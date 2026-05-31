<?php

namespace App\Services;

use App\Models\AssociatSepaRemittance;
use App\Settings\SettingStore;

class SepaXmlGenerator
{
    public function generate(AssociatSepaRemittance $remittance): string
    {
        $store = app(SettingStore::class);

        $creditorId   = $store->get('sepa_creditor_id', '');
        $creditorName = $store->get('sepa_org_name', $store->get('associats_org_name', 'Entitat'));
        $creditorIban = $store->get('sepa_iban', '');
        $creditorBic  = $store->get('sepa_bic', '');
        $concept      = $store->get('associat_quota_concept', 'Quota anual soci {YEAR}');

        $remittance->load('quotes.member');

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS(
            'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02',
            'Document'
        );
        $dom->appendChild($root);

        $init = $dom->createElement('CstmrDrctDbtInitn');
        $root->appendChild($init);

        // ── Group Header ─────────────────────────────────────────────────────
        $grpHdr = $dom->createElement('GrpHdr');
        $init->appendChild($grpHdr);

        $grpHdr->appendChild($dom->createElement('MsgId', $remittance->reference));
        $grpHdr->appendChild($dom->createElement('CreDtTm', now()->format('Y-m-d\TH:i:s')));
        $grpHdr->appendChild($dom->createElement('NbOfTxs', $remittance->total_transactions));
        $grpHdr->appendChild($dom->createElement('CtrlSum', number_format($remittance->total_amount, 2, '.', '')));

        $initgPty = $dom->createElement('InitgPty');
        $grpHdr->appendChild($initgPty);
        $initgPty->appendChild($dom->createElement('Nm', $this->sanitize($creditorName)));

        // ── Payment Information ───────────────────────────────────────────────
        $pmtInf = $dom->createElement('PmtInf');
        $init->appendChild($pmtInf);

        $pmtInf->appendChild($dom->createElement('PmtInfId', $remittance->reference . '-1'));
        $pmtInf->appendChild($dom->createElement('PmtMtd', 'DD'));
        $pmtInf->appendChild($dom->createElement('NbOfTxs', $remittance->total_transactions));
        $pmtInf->appendChild($dom->createElement('CtrlSum', number_format($remittance->total_amount, 2, '.', '')));

        $pmtTpInf = $dom->createElement('PmtTpInf');
        $pmtInf->appendChild($pmtTpInf);
        $svcLvl = $dom->createElement('SvcLvl');
        $pmtTpInf->appendChild($svcLvl);
        $svcLvl->appendChild($dom->createElement('Cd', 'SEPA'));
        $lclInstrm = $dom->createElement('LclInstrm');
        $pmtTpInf->appendChild($lclInstrm);
        $lclInstrm->appendChild($dom->createElement('Cd', 'CORE'));
        $pmtTpInf->appendChild($dom->createElement('SeqTp', 'RCUR'));

        $pmtInf->appendChild($dom->createElement('ReqdColltnDt', $remittance->execution_date->format('Y-m-d')));

        // Creditor
        $cdtr = $dom->createElement('Cdtr');
        $pmtInf->appendChild($cdtr);
        $cdtr->appendChild($dom->createElement('Nm', $this->sanitize($creditorName)));

        $cdtrAcct = $dom->createElement('CdtrAcct');
        $pmtInf->appendChild($cdtrAcct);
        $cdtrId = $dom->createElement('Id');
        $cdtrAcct->appendChild($cdtrId);
        $cdtrId->appendChild($dom->createElement('IBAN', preg_replace('/\s+/', '', $creditorIban)));

        if ($creditorBic) {
            $cdtrAgt = $dom->createElement('CdtrAgt');
            $pmtInf->appendChild($cdtrAgt);
            $finInstnId = $dom->createElement('FinInstnId');
            $cdtrAgt->appendChild($finInstnId);
            $finInstnId->appendChild($dom->createElement('BIC', $creditorBic));
        }

        // Creditor Scheme Id
        $cdtrSchmeId = $dom->createElement('CdtrSchmeId');
        $pmtInf->appendChild($cdtrSchmeId);
        $cdtrSchmeIdId = $dom->createElement('Id');
        $cdtrSchmeId->appendChild($cdtrSchmeIdId);
        $prvtId = $dom->createElement('PrvtId');
        $cdtrSchmeIdId->appendChild($prvtId);
        $othr = $dom->createElement('Othr');
        $prvtId->appendChild($othr);
        $othr->appendChild($dom->createElement('Id', $creditorId));
        $schmeNm = $dom->createElement('SchmeNm');
        $othr->appendChild($schmeNm);
        $schmeNm->appendChild($dom->createElement('Prtry', 'SEPA'));

        // ── Transactions ──────────────────────────────────────────────────────
        foreach ($remittance->quotes as $quote) {
            $member = $quote->member;
            $iban   = preg_replace('/\s+/', '', $member->bank_iban ?? '');
            if (empty($iban) || empty($member->mandate_reference)) {
                continue;
            }

            $conceptText = str_replace('{YEAR}', $quote->year, $concept);
            $sequence    = $member->mandate_sequence ?? 'RCUR';

            $drctDbtTxInf = $dom->createElement('DrctDbtTxInf');
            $pmtInf->appendChild($drctDbtTxInf);

            $pmtId = $dom->createElement('PmtId');
            $drctDbtTxInf->appendChild($pmtId);
            $pmtId->appendChild($dom->createElement('EndToEndId', "SOCI-{$member->member_number}-{$quote->year}"));

            $instdAmt = $dom->createElement('InstdAmt', number_format($quote->amount, 2, '.', ''));
            $instdAmt->setAttribute('Ccy', 'EUR');
            $drctDbtTxInf->appendChild($instdAmt);

            $drctDbtTx = $dom->createElement('DrctDbtTx');
            $drctDbtTxInf->appendChild($drctDbtTx);
            $mndtRltdInf = $dom->createElement('MndtRltdInf');
            $drctDbtTx->appendChild($mndtRltdInf);
            $mndtRltdInf->appendChild($dom->createElement('MndtId', $member->mandate_reference));
            $mndtRltdInf->appendChild($dom->createElement('DtOfSgntr',
                $member->mandate_signed_at?->format('Y-m-d') ?? now()->format('Y-m-d')
            ));
            $mndtRltdInf->appendChild($dom->createElement('AmdmntInd', 'false'));

            // Debtor
            $dbtr = $dom->createElement('Dbtr');
            $drctDbtTxInf->appendChild($dbtr);
            $dbtr->appendChild($dom->createElement('Nm', $this->sanitize($member->full_name)));

            $dbtrAcct = $dom->createElement('DbtrAcct');
            $drctDbtTxInf->appendChild($dbtrAcct);
            $dbtrId = $dom->createElement('Id');
            $dbtrAcct->appendChild($dbtrId);
            $dbtrId->appendChild($dom->createElement('IBAN', $iban));

            $rmtInf = $dom->createElement('RmtInf');
            $drctDbtTxInf->appendChild($rmtInf);
            $rmtInf->appendChild($dom->createElement('Ustrd', $this->sanitize($conceptText)));
        }

        return $dom->saveXML();
    }

    private function sanitize(string $text): string
    {
        return substr(preg_replace('/[^A-Za-z0-9\s\-\/\(\)\?\:\@\,\'\+\.]/', '', $text), 0, 70);
    }
}

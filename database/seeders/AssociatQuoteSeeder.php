<?php

namespace Database\Seeders;

use App\Models\AssociatMember;
use App\Models\AssociatQuote;
use App\Models\AssociatSepaRemittance;
use Illuminate\Database\Seeder;

class AssociatQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $activeMembers = AssociatMember::where('status', 'active')->get();

        if ($activeMembers->isEmpty()) {
            $this->command->warn('Cap soci actiu trobat. Executa AssociatMemberSeeder primer.');
            return;
        }

        // ── Quotes anuals 2025 (pagades) ──────────────────────────────────
        foreach ($activeMembers as $member) {
            AssociatQuote::firstOrCreate(
                ['member_id' => $member->id, 'year' => 2025, 'period' => 'annual', 'period_number' => 1],
                [
                    'amount'   => 60.00,
                    'status'   => 'paid',
                    'due_date' => '2025-03-31',
                    'paid_at'  => '2025-03-15 10:00:00',
                ]
            );
        }

        // ── Remesa SEPA 2025 (processada) ────────────────────────────────
        $quotesSepa2025 = AssociatQuote::where('year', 2025)
            ->where('period', 'annual')
            ->where('period_number', 1)
            ->whereHas('member', fn ($q) =>
                $q->whereNotNull('bank_iban')->whereNotNull('mandate_reference')
            )
            ->get();

        if ($quotesSepa2025->isNotEmpty() && !AssociatSepaRemittance::where('reference', 'REMESA-2025-001')->exists()) {
            $remittance = AssociatSepaRemittance::create([
                'reference'          => 'REMESA-2025-001',
                'year'               => 2025,
                'execution_date'     => '2025-03-20',
                'total_transactions' => $quotesSepa2025->count(),
                'total_amount'       => $quotesSepa2025->sum('amount'),
                'status'             => 'processed',
                'generated_at'       => '2025-03-15 09:00:00',
                'notes'              => 'Anual 2025',
            ]);
            $quotesSepa2025->each(fn ($q) => $q->update(['remittance_id' => $remittance->id]));
        }

        // ── Quotes anuals 2026 (pendents) ─────────────────────────────────
        foreach ($activeMembers as $member) {
            AssociatQuote::firstOrCreate(
                ['member_id' => $member->id, 'year' => 2026, 'period' => 'annual', 'period_number' => 1],
                [
                    'amount'   => 65.00,
                    'status'   => 'pending',
                    'due_date' => '2026-03-31',
                ]
            );
        }

        // ── Quotes semestral 2026 (exemple de periodicitat) ───────────────
        $membresAmbSepa = $activeMembers->filter(
            fn ($m) => !empty($m->bank_iban) && !empty($m->mandate_reference)
        );

        foreach ($membresAmbSepa->take(2) as $i => $member) {
            foreach ([1, 2] as $num) {
                AssociatQuote::firstOrCreate(
                    ['member_id' => $member->id, 'year' => 2026, 'period' => 'semi-annual', 'period_number' => $num],
                    [
                        'amount'   => 32.50,
                        'status'   => $num === 1 && $i === 0 ? 'paid' : 'pending',
                        'due_date' => $num === 1 ? '2026-03-31' : '2026-09-30',
                        'paid_at'  => $num === 1 && $i === 0 ? '2026-03-20 11:00:00' : null,
                    ]
                );
            }
        }

        $total = AssociatQuote::count();
        $this->command->info("✅ Quotes creades/verificades: {$total} registres al total.");
    }
}

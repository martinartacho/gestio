<?php

namespace App\Filament\Pages;

use App\Models\CampusEnrollment;
use App\Models\CampusPayment;
use App\Models\CampusTeacherPayment;
use App\Models\TresoreriaQuote;
use App\Models\TresoreriaRemittance;
use App\Settings\SettingStore;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TresoreriaDashboard extends Page
{
    protected static ?int $navigationSort = 0;
    protected string $view = 'filament.pages.tresoreria-dashboard';

    public static function getNavigationIcon(): string  { return 'heroicon-o-banknotes'; }
    public static function getNavigationLabel(): string { return 'Resum financer'; }
    public static function getNavigationGroup(): string { return __('site.treasury_group'); }
    public function getTitle(): string                  { return 'Resum financer de Tresoreria'; }

    public static function canAccess(): bool
    {
        $s = app(SettingStore::class);
        return (bool) $s->get('tresoreria_enabled', true)
            && (Auth::user()?->hasAnyRole(['admin', 'tresoreria']) ?? false);
    }

    // ── Flags de visibilitat ──────────────────────────────────────────────
    public bool $showInscripcions  = false;
    public bool $showPagaments     = false;
    public bool $showLiquidacions  = false;
    public bool $showQuotesSocis   = false;
    public bool $showSepaSocis     = false;

    // ── Dades Inscripcions ────────────────────────────────────────────────
    public array $inscripcions = [];
    public float $inscripcionsTotal = 0;

    // ── Dades Pagaments ───────────────────────────────────────────────────
    public array $pagaments = [];
    public float $pagamentsTotal = 0;

    // ── Dades Liquidacions ────────────────────────────────────────────────
    public array $liquidacions = [];
    public float $liquidacionsBrut  = 0;
    public float $liquidacionsNet   = 0;
    public float $liquidacionsRetencio = 0;

    // ── Dades Quotes Socis ────────────────────────────────────────────────
    public array $quotesSocis = [];
    public float $quotesTotal = 0;

    // ── Dades Remeses SEPA socis ──────────────────────────────────────────
    public array $remesesSepa     = [];
    public float $remesesImport   = 0;
    public int   $remesesOperacions = 0;

    public function mount(): void
    {
        $s = app(SettingStore::class);

        $this->showInscripcions = (bool) $s->get('tresoreria_inscripcions_enabled', true);
        $this->showPagaments    = (bool) $s->get('tresoreria_pagaments_enabled', true);
        $this->showLiquidacions = (bool) $s->get('tresoreria_liquidacions_enabled', true);
        $associatsActiu = (bool) $s->get('associats_enabled', false);
        $this->showQuotesSocis  = $associatsActiu && (bool) $s->get('tresoreria_quotes_socis_enabled', true);
        $this->showSepaSocis    = $associatsActiu && (bool) $s->get('tresoreria_sepa_socis_enabled', true);

        if ($this->showInscripcions) {
            $rows = CampusEnrollment::selectRaw('status, count(*) as total, coalesce(sum(amount),0) as import')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            foreach (['pending', 'paid', 'confirmed', 'cancelled', 'refunded'] as $estat) {
                $this->inscripcions[$estat] = [
                    'total'  => $rows[$estat]->total ?? 0,
                    'import' => (float) ($rows[$estat]->import ?? 0),
                ];
            }
            $this->inscripcionsTotal = collect($this->inscripcions)
                ->whereIn('total', array_keys(['paid' => 1, 'confirmed' => 1]))
                ->sum('import');
            $this->inscripcionsTotal = ($this->inscripcions['paid']['import'] ?? 0)
                + ($this->inscripcions['confirmed']['import'] ?? 0);
        }

        if ($this->showPagaments) {
            $rows = CampusPayment::selectRaw('method, status, count(*) as total, coalesce(sum(amount),0) as import')
                ->groupBy('method', 'status')
                ->get();

            foreach ($rows as $row) {
                $this->pagaments[$row->method][$row->status] = [
                    'total'  => $row->total,
                    'import' => (float) $row->import,
                ];
            }
            $this->pagamentsTotal = CampusPayment::where('status', 'completed')->sum('amount');
        }

        if ($this->showLiquidacions) {
            $rows = CampusTeacherPayment::selectRaw(
                'status,
                 coalesce(sum(gross_amount),0) as brut,
                 coalesce(sum(net_amount),0) as net,
                 coalesce(sum(retention_amount),0) as retencio'
            )->groupBy('status')->get()->keyBy('status');

            foreach (['draft', 'sent', 'paid', 'cancelled'] as $estat) {
                $this->liquidacions[$estat] = [
                    'brut'     => (float) ($rows[$estat]->brut ?? 0),
                    'net'      => (float) ($rows[$estat]->net ?? 0),
                    'retencio' => (float) ($rows[$estat]->retencio ?? 0),
                ];
            }
            $this->liquidacionsBrut     = collect($this->liquidacions)->sum('brut');
            $this->liquidacionsNet      = collect($this->liquidacions)->sum('net');
            $this->liquidacionsRetencio = collect($this->liquidacions)->sum('retencio');
        }

        if ($this->showQuotesSocis) {
            $rows = TresoreriaQuote::selectRaw('status, count(*) as total, coalesce(sum(amount),0) as import')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            foreach (['pending', 'paid', 'failed', 'cancelled'] as $estat) {
                $this->quotesSocis[$estat] = [
                    'total'  => $rows[$estat]->total ?? 0,
                    'import' => (float) ($rows[$estat]->import ?? 0),
                ];
            }
            $this->quotesTotal = $this->quotesSocis['paid']['import'] ?? 0;
        }

        if ($this->showSepaSocis) {
            $rows = TresoreriaRemittance::selectRaw(
                'status, count(*) as total,
                 coalesce(sum(total_amount),0) as import,
                 coalesce(sum(total_transactions),0) as operacions'
            )->groupBy('status')->get()->keyBy('status');

            foreach (['draft', 'generated', 'submitted', 'processed'] as $estat) {
                $this->remesesSepa[$estat] = [
                    'total'      => $rows[$estat]->total ?? 0,
                    'import'     => (float) ($rows[$estat]->import ?? 0),
                    'operacions' => (int) ($rows[$estat]->operacions ?? 0),
                ];
            }
            $this->remesesImport    = collect($this->remesesSepa)->sum('import');
            $this->remesesOperacions = collect($this->remesesSepa)->sum('operacions');
        }
    }
}

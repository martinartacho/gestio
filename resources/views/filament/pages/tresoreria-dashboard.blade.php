<x-filament-panels::page>

<div style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- ── Bloc Inscripcions ── --}}
    @if ($showInscripcions)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0;">Inscripcions</h2>
            <span style="font-size:0.8125rem;color:#6b7280;">Total recaptat: <strong style="color:#111827;">{{ number_format($inscripcionsTotal, 2, ',', '.') }} €</strong></span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0;">
            @foreach ([
                'pending'   => ['Pendent',    '#f59e0b', '#fffbeb'],
                'paid'      => ['Pagada',     '#10b981', '#f0fdf4'],
                'confirmed' => ['Confirmada', '#6366f1', '#eef2ff'],
                'cancelled' => ['Cancel·lada','#6b7280', '#f9fafb'],
                'refunded'  => ['Retornada',  '#ef4444', '#fef2f2'],
            ] as $estat => [$label, $color, $bg])
            <div style="padding:1rem 1.25rem;border-right:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6;border-left:3px solid {{ $color }};background:{{ $bg }};">
                <p style="font-size:0.7rem;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;margin:0 0 0.25rem;font-weight:600;">{{ $label }}</p>
                <p style="font-size:1.375rem;font-weight:700;color:#111827;margin:0 0 0.125rem;">{{ $inscripcions[$estat]['total'] }}</p>
                <p style="font-size:0.8125rem;color:#6b7280;margin:0;">{{ number_format($inscripcions[$estat]['import'], 2, ',', '.') }} €</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Bloc Pagaments alumnes ── --}}
    @if ($showPagaments)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0;">Pagaments alumnes</h2>
            <span style="font-size:0.8125rem;color:#6b7280;">Total completat: <strong style="color:#111827;">{{ number_format($pagamentsTotal, 2, ',', '.') }} €</strong></span>
        </div>
        @if (empty($pagaments))
            <p style="padding:1rem 1.25rem;color:#9ca3af;font-size:0.875rem;margin:0;">Sense pagaments registrats.</p>
        @else
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:0.625rem 1.25rem;text-align:left;color:#6b7280;font-weight:600;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Mètode</th>
                    <th style="padding:0.625rem 1.25rem;text-align:left;color:#6b7280;font-weight:600;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Estat</th>
                    <th style="padding:0.625rem 1.25rem;text-align:right;color:#6b7280;font-weight:600;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Registres</th>
                    <th style="padding:0.625rem 1.25rem;text-align:right;color:#6b7280;font-weight:600;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Import</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pagaments as $metode => $estats)
                    @foreach ($estats as $estat => $dades)
                    <tr style="border-top:1px solid #f3f4f6;">
                        <td style="padding:0.625rem 1.25rem;color:#111827;font-weight:500;">{{ ucfirst($metode) }}</td>
                        <td style="padding:0.625rem 1.25rem;">
                            <span style="font-size:0.75rem;padding:0.125rem 0.5rem;border-radius:9999px;background:{{ $estat === 'completed' ? '#d1fae5' : ($estat === 'refunded' ? '#fee2e2' : '#fef3c7') }};color:{{ $estat === 'completed' ? '#065f46' : ($estat === 'refunded' ? '#991b1b' : '#92400e') }};">
                                {{ match($estat) { 'completed' => 'Completat', 'pending' => 'Pendent', 'refunded' => 'Retornat', default => $estat } }}
                            </span>
                        </td>
                        <td style="padding:0.625rem 1.25rem;text-align:right;color:#374151;">{{ $dades['total'] }}</td>
                        <td style="padding:0.625rem 1.25rem;text-align:right;color:#374151;font-weight:500;">{{ number_format($dades['import'], 2, ',', '.') }} €</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- ── Bloc Liquidacions professors ── --}}
    @if ($showLiquidacions)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;">
            <h2 style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.5rem;">Liquidacions professors</h2>
            <div style="display:flex;gap:2rem;flex-wrap:wrap;">
                <span style="font-size:0.8125rem;color:#6b7280;">Brut total: <strong style="color:#111827;">{{ number_format($liquidacionsBrut, 2, ',', '.') }} €</strong></span>
                <span style="font-size:0.8125rem;color:#6b7280;">Retencions: <strong style="color:#ef4444;">{{ number_format($liquidacionsRetencio, 2, ',', '.') }} €</strong></span>
                <span style="font-size:0.8125rem;color:#6b7280;">Net a pagar: <strong style="color:#10b981;">{{ number_format($liquidacionsNet, 2, ',', '.') }} €</strong></span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0;">
            @foreach ([
                'draft'     => ['Esborrany', '#9ca3af', '#f9fafb'],
                'sent'      => ['Enviada',   '#f59e0b', '#fffbeb'],
                'paid'      => ['Pagada',    '#10b981', '#f0fdf4'],
                'cancelled' => ['Cancel·lada','#ef4444','#fef2f2'],
            ] as $estat => [$label, $color, $bg])
            <div style="padding:1rem 1.25rem;border-right:1px solid #f3f4f6;border-left:3px solid {{ $color }};background:{{ $bg }};">
                <p style="font-size:0.7rem;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;margin:0 0 0.25rem;font-weight:600;">{{ $label }}</p>
                <p style="font-size:0.875rem;color:#111827;margin:0 0 0.125rem;">Brut: <strong>{{ number_format($liquidacions[$estat]['brut'], 2, ',', '.') }} €</strong></p>
                <p style="font-size:0.875rem;color:#10b981;margin:0;">Net: <strong>{{ number_format($liquidacions[$estat]['net'], 2, ',', '.') }} €</strong></p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Bloc Quotes socis ── --}}
    @if ($showQuotesSocis)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0;">Quotes socis</h2>
            <span style="font-size:0.8125rem;color:#6b7280;">Total cobrat: <strong style="color:#111827;">{{ number_format($quotesTotal, 2, ',', '.') }} €</strong></span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0;">
            @foreach ([
                'pending'   => ['Pendent',    '#f59e0b', '#fffbeb'],
                'paid'      => ['Pagada',     '#10b981', '#f0fdf4'],
                'failed'    => ['Fallida',    '#ef4444', '#fef2f2'],
                'cancelled' => ['Cancel·lada','#6b7280', '#f9fafb'],
            ] as $estat => [$label, $color, $bg])
            <div style="padding:1rem 1.25rem;border-right:1px solid #f3f4f6;border-left:3px solid {{ $color }};background:{{ $bg }};">
                <p style="font-size:0.7rem;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;margin:0 0 0.25rem;font-weight:600;">{{ $label }}</p>
                <p style="font-size:1.375rem;font-weight:700;color:#111827;margin:0 0 0.125rem;">{{ $quotesSocis[$estat]['total'] }}</p>
                <p style="font-size:0.8125rem;color:#6b7280;margin:0;">{{ number_format($quotesSocis[$estat]['import'], 2, ',', '.') }} €</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Bloc Remeses SEPA socis ── --}}
    @if ($showSepaSocis)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;">
            <h2 style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.25rem;">Remeses SEPA socis</h2>
            <div style="display:flex;gap:2rem;flex-wrap:wrap;">
                <span style="font-size:0.8125rem;color:#6b7280;">Import total: <strong style="color:#111827;">{{ number_format($remesesImport, 2, ',', '.') }} €</strong></span>
                <span style="font-size:0.8125rem;color:#6b7280;">Operacions totals: <strong style="color:#111827;">{{ $remesesOperacions }}</strong></span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0;">
            @foreach ([
                'draft'     => ['Esborrany',    '#9ca3af', '#f9fafb'],
                'generated' => ['XML generat',  '#6366f1', '#eef2ff'],
                'submitted' => ['Enviat banc',  '#f59e0b', '#fffbeb'],
                'processed' => ['Processat',    '#10b981', '#f0fdf4'],
            ] as $estat => [$label, $color, $bg])
            <div style="padding:1rem 1.25rem;border-right:1px solid #f3f4f6;border-left:3px solid {{ $color }};background:{{ $bg }};">
                <p style="font-size:0.7rem;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;margin:0 0 0.25rem;font-weight:600;">{{ $label }}</p>
                <p style="font-size:1.375rem;font-weight:700;color:#111827;margin:0 0 0.125rem;">{{ $remesesSepa[$estat]['total'] }}</p>
                <p style="font-size:0.8125rem;color:#6b7280;margin:0;">{{ number_format($remesesSepa[$estat]['import'], 2, ',', '.') }} €</p>
                <p style="font-size:0.75rem;color:#9ca3af;margin:0.125rem 0 0;">{{ $remesesSepa[$estat]['operacions'] }} operacions</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if (!$showInscripcions && !$showPagaments && !$showLiquidacions && !$showQuotesSocis && !$showSepaSocis)
    <div style="padding:3rem;text-align:center;color:#9ca3af;">
        <p style="font-size:0.9375rem;">Cap sub-mòdul de Tresoreria actiu. Activa'ls des de <a href="{{ route('filament.admin.pages.settings-page') }}" style="color:#6366f1;">Configuració</a>.</p>
    </div>
    @endif

</div>

</x-filament-panels::page>

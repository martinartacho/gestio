<x-filament-panels::page>

<div style="max-width:52rem;">

    {{-- ── Tabs ─────────────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:0;border-bottom:1px solid #e5e7eb;margin-bottom:1.5rem;">
        @foreach([
            'campus'    => '🏫 Campus',
            'aparenca'  => '🎨 Aparença',
            'email'     => '✉️ Correu',
            'moduls'    => '🔧 Mòduls',
            'pagament'  => '💳 Pagament',
            'avançat'   => '⚙️ Avançat',
        ] as $tab => $label)
        <button wire:click="$set('activeTab','{{ $tab }}')"
                type="button"
                style="padding:0.5rem 1.25rem;font-size:0.875rem;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid {{ $activeTab === $tab ? '#4f46e5' : 'transparent' }};color:{{ $activeTab === $tab ? '#4f46e5' : '#6b7280' }};">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <form wire:submit.prevent="save">

        {{-- ══════════════════════════════════════════════════════════════
             TAB: DADES DEL CAMPUS
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'campus' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:1.25rem;">Dades del Campus</h2>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Nom del campus *</label>
                        <input type="text" wire:model="campus_name" required
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Eslògan</label>
                        <input type="text" wire:model="campus_tagline"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">URL del logotip</label>
                        <input type="url" wire:model="campus_logo_url" placeholder="https://..."
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">URL del favicon</label>
                        <input type="url" wire:model="campus_favicon_url" placeholder="https://..."
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Correu de contacte</label>
                        <input type="email" wire:model="campus_contact_email"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Telèfon de contacte</label>
                        <input type="tel" wire:model="campus_contact_phone"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Adreça</label>
                        <textarea wire:model="campus_address" rows="2"
                                  style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: APARENÇA
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'aparenca' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:1.25rem;">Aparença de la pàgina d'inici</h2>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Títol del hero</label>
                        <input type="text" wire:model="hero_title"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Subtítol del hero</label>
                        <textarea wire:model="hero_subtitle" rows="2"
                                  style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Color de fons del hero</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="color" wire:model="hero_color"
                                   style="width:3rem;height:2.25rem;border:1px solid #d1d5db;border-radius:0.375rem;cursor:pointer;padding:0.125rem;">
                            <input type="text" wire:model="hero_color"
                                   style="flex:1;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:monospace;">
                        </div>
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Color del text del hero</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="color" wire:model="hero_text_color"
                                   style="width:3rem;height:2.25rem;border:1px solid #d1d5db;border-radius:0.375rem;cursor:pointer;padding:0.125rem;">
                            <input type="text" wire:model="hero_text_color"
                                   style="flex:1;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:monospace;">
                        </div>
                    </div>
                </div>

                {{-- Preview --}}
                <div style="margin-top:1.25rem;border-radius:0.5rem;overflow:hidden;border:1px solid #e5e7eb;">
                    <div style="padding:2rem 1.5rem;background:{{ $hero_color }};color:{{ $hero_text_color }};">
                        <h3 style="font-size:1.5rem;font-weight:700;margin:0 0 0.5rem;">{{ $hero_title ?: 'Títol del hero' }}</h3>
                        <p style="margin:0;font-size:1rem;opacity:0.9;">{{ $hero_subtitle ?: 'Subtítol del hero' }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: CORREU ELECTRÒNIC
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'email' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Correu electrònic</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.25rem;">
                    Configuració del remitent dels correus automàtics del campus.<br>
                    <strong>Nota:</strong> el servidor SMTP es configura a <code>.env</code> (MAIL_HOST, MAIL_PORT, etc.)
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Nom del remitent</label>
                        <input type="text" wire:model="mail_from_name"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Adreça remitent</label>
                        <input type="email" wire:model="mail_from_address"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                    </div>

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Text del peu de correu</label>
                        <textarea wire:model="mail_footer_text" rows="3"
                                  style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;resize:vertical;"></textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: MÒDULS
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'moduls' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Mòduls actius</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.25rem;">
                    Activa o desactiva funcionalitats del campus. Alguns canvis requereixen recarregar la pàgina per fer efecte.
                </p>

                @foreach([
                    ['key' => 'documents_enabled',        'label' => 'Mòdul de documents',           'desc' => 'Permet pujar i compartir documents amb alumnes i professors.'],
                    ['key' => 'lms_enabled',              'label' => 'LMS (plataforma d\'aprenentatge)', 'desc' => 'Activa les funcions de LMS: lliçons, qüestionaris i seguiment.'],
                    ['key' => 'courses_learning_enabled', 'label' => 'Aprenentatge en línia de cursos', 'desc' => 'Permet als alumnes visualitzar continguts de vídeo i materials en línia.'],
                ] as $module)
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 0;border-bottom:1px solid #f3f4f6;cursor:pointer;">
                    <input type="checkbox" wire:model="{{ $module['key'] }}"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.125rem;">{{ $module['label'] }}</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">{{ $module['desc'] }}</p>
                    </div>
                </label>
                @endforeach

            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: PAGAMENT
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'pagament' ? 'display:none;' : '' }}">

            {{-- Stripe (llegit del .env, no editable aquí) --}}
            @php $stripeOk = ! empty(config('services.stripe.secret')); @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.875rem 1.25rem;margin-bottom:1rem;border-radius:0.75rem;border:1px solid {{ $stripeOk ? '#bbf7d0' : '#fde68a' }};background:{{ $stripeOk ? '#f0fdf4' : '#fffbeb' }};">
                <span style="font-size:1.25rem;">💳</span>
                <div>
                    <span style="font-weight:600;font-size:0.9375rem;color:#111827;">Stripe (targeta bancària)</span>
                    @if ($stripeOk)
                        <span style="margin-left:0.5rem;font-size:0.8125rem;color:#16a34a;font-weight:500;">✓ Configurat via .env</span>
                    @else
                        <span style="margin-left:0.5rem;font-size:0.8125rem;color:#b45309;font-weight:500;">⚠ No configurat — afegeix STRIPE_KEY i STRIPE_SECRET al .env</span>
                    @endif
                    <p style="margin:0;font-size:0.75rem;color:#9ca3af;">El pagament amb targeta s'activa automàticament quan Stripe és configurat.</p>
                </div>
            </div>

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Mètodes de pagament manual</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.5rem;">
                    Habilita els mètodes que vols oferir als alumnes. Un cop el pagament es rebi, l'admin el confirma manualment a Filament.
                </p>

                {{-- Transferència bancària --}}
                <div style="padding:1rem 0;border-bottom:1px solid #f3f4f6;">
                    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;margin-bottom:0.75rem;">
                        <input type="checkbox" wire:model.live="payment_transfer_enabled"
                               style="width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                        <span style="font-weight:600;font-size:0.9375rem;color:#111827;">🏦 Transferència bancària</span>
                    </label>
                    @if ($payment_transfer_enabled)
                    <div style="margin-left:1.875rem;display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div>
                            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">IBAN</label>
                            <input type="text" wire:model="payment_iban"
                                   placeholder="ES76 2100 0418 40 0200051332"
                                   style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.4rem 0.75rem;font-size:0.875rem;font-family:monospace;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Titular del compte</label>
                            <input type="text" wire:model="payment_bank_holder"
                                   placeholder="Associació Campus"
                                   style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.4rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Bizum --}}
                <div style="padding:1rem 0;border-bottom:1px solid #f3f4f6;">
                    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;margin-bottom:0.75rem;">
                        <input type="checkbox" wire:model.live="payment_bizum_enabled"
                               style="width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                        <span style="font-weight:600;font-size:0.9375rem;color:#111827;">📱 Bizum</span>
                    </label>
                    @if ($payment_bizum_enabled)
                    <div style="margin-left:1.875rem;">
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Número de telèfon Bizum</label>
                        <input type="text" wire:model="payment_bizum_number"
                               placeholder="612 345 678"
                               style="width:12rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.4rem 0.75rem;font-size:0.875rem;font-family:monospace;">
                    </div>
                    @endif
                </div>

                {{-- Efectiu --}}
                <div style="padding:1rem 0;border-bottom:1px solid #f3f4f6;">
                    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
                        <input type="checkbox" wire:model="payment_cash_enabled"
                               style="width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                        <span style="font-weight:600;font-size:0.9375rem;color:#111827;">🏢 Pagament en efectiu</span>
                    </label>
                    <p style="margin-left:1.875rem;font-size:0.8125rem;color:#9ca3af;margin-top:0.25rem;">
                        L'alumne paga a la secretaria. L'admin confirma el pagament manualment.
                    </p>
                </div>

                {{-- PayPal --}}
                <div style="padding:1rem 0;">
                    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;margin-bottom:0.75rem;">
                        <input type="checkbox" wire:model.live="payment_paypal_enabled"
                               style="width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                        <span style="font-weight:600;font-size:0.9375rem;color:#111827;">🔗 PayPal</span>
                    </label>
                    @if ($payment_paypal_enabled)
                    <div style="margin-left:1.875rem;">
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Adreça de correu PayPal</label>
                        <input type="email" wire:model="payment_paypal_email"
                               placeholder="pagaments@campus.cat"
                               style="width:20rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.4rem 0.75rem;font-size:0.875rem;">
                    </div>
                    @endif
                </div>
            </div>

            {{-- Concepte i referència --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Concepte del pagament</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1rem;">
                    Text que es mostrarà a l'alumne com a concepte de la transferència/Bizum/PayPal.
                    Variables: <code style="background:#f3f4f6;padding:0.1rem 0.4rem;border-radius:0.25rem;">{NOM}</code>,
                    <code style="background:#f3f4f6;padding:0.1rem 0.4rem;border-radius:0.25rem;">{CURS}</code>,
                    <code style="background:#eef2ff;color:#4f46e5;padding:0.1rem 0.4rem;border-radius:0.25rem;">{REFERENCIA}</code>
                    <span style="font-size:0.75rem;color:#9ca3af;">(codi únic per matriculació)</span>.
                </p>
                <input type="text" wire:model="payment_concept_template"
                       placeholder="{NOM} - {CURS} - {REFERENCIA}"
                       style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
            </div>

            {{-- Caducitat --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Termini per completar el pagament</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1rem;">
                    Temps que té l'alumne per realitzar el pagament manual des del moment de la inscripció.
                    Un cop superat, la plaça pot ser alliberada. Poseu <strong>0</strong> per no establir cap termini.
                </p>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <input type="number" wire:model="payment_expiry_value"
                           min="0" max="720"
                           style="width:5.5rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;text-align:center;">
                    <div style="display:flex;gap:0.25rem;">
                        <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;padding:0.4rem 0.75rem;border:1px solid #d1d5db;border-radius:0.5rem 0 0 0.5rem;font-size:0.875rem;background:{{ $payment_expiry_unit === 'hours' ? '#eef2ff' : '#fff' }};color:{{ $payment_expiry_unit === 'hours' ? '#4f46e5' : '#374151' }};">
                            <input type="radio" wire:model.live="payment_expiry_unit" value="hours" style="accent-color:#4f46e5;">
                            hores
                        </label>
                        <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;padding:0.4rem 0.75rem;border:1px solid #d1d5db;border-left:none;border-radius:0 0.5rem 0.5rem 0;font-size:0.875rem;background:{{ $payment_expiry_unit === 'days' ? '#eef2ff' : '#fff' }};color:{{ $payment_expiry_unit === 'days' ? '#4f46e5' : '#374151' }};">
                            <input type="radio" wire:model.live="payment_expiry_unit" value="days" style="accent-color:#4f46e5;">
                            dies
                        </label>
                    </div>
                    @if ($payment_expiry_value > 0)
                        <span style="font-size:0.8125rem;color:#6b7280;">
                            @if ($payment_expiry_unit === 'hours')
                                ≈ {{ round($payment_expiry_value / 24, 1) }} dies
                            @else
                                = {{ $payment_expiry_value * 24 }} hores
                            @endif
                        </span>
                    @endif
                </div>
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.625rem;">
                    @if ($payment_expiry_value > 0)
                        Les matrícules pendents caduquen al cap de
                        <strong>{{ $payment_expiry_value }} {{ $payment_expiry_unit === 'hours' ? 'h' : 'dies' }}</strong>
                        · <code style="background:#f3f4f6;padding:0.1rem 0.3rem;border-radius:0.2rem;">php artisan enrollments:expire</code>
                    @else
                        Sense límit de temps.
                    @endif
                </p>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: AVANÇAT
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'avançat' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:1.25rem;">Avançat</h2>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Zona horària</label>
                        <select wire:model="timezone"
                                style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;background:#fff;">
                            @foreach($this->getTimezoneOptions() as $tz => $label)
                                <option value="{{ $tz }}" {{ $timezone === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">S'aplica als càlculs de dates i hores de l'aplicació.</p>
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Idioma</label>
                        <select wire:model="locale"
                                style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;background:#fff;">
                            @foreach($this->getLocaleOptions() as $code => $name)
                                <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top:1.5rem;padding:1rem;background:#fefce8;border:1px solid #fef08a;border-radius:0.5rem;">
                    <p style="font-size:0.8125rem;color:#713f12;margin:0;">
                        <strong>⚠️ Zona de perill:</strong> canviar la zona horària pot afectar l'ordenació de sessions, disponibilitat de documents i tots els càlculs de dates. Torna a carregar l'aplicació després de desar.
                    </p>
                </div>
            </div>

        </div>

        {{-- ── Botó Desar ───────────────────────────────────────────────── --}}
        <div style="display:flex;justify-content:flex-end;padding-top:0.5rem;">
            <button type="submit"
                    style="background:#4f46e5;color:#fff;border:none;border-radius:0.5rem;padding:0.625rem 1.75rem;font-size:0.875rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem;">
                <span wire:loading.remove wire:target="save">💾 Desar configuració</span>
                <span wire:loading wire:target="save">Desant…</span>
            </button>
        </div>

    </form>

</div>

</x-filament-panels::page>

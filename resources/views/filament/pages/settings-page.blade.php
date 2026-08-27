<x-filament-panels::page>

<div style="max-width:52rem;">

    {{-- ── Tabs ─────────────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:0;border-bottom:1px solid #e5e7eb;margin-bottom:1.5rem;">
        @foreach([
            'campus'     => '🏫 Site',
            'aparenca'   => '🎨 Aparença',
            'moduls'     => '🔧 Mòduls',
            'cataleg'    => '📚 Catàleg',
            'pagament'   => '💳 Pagament',
            'cua'        => '🎟 Cua',
            'avançat'    => '⚙️ Avançat',
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
             TAB: DADES DEL SITE
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'campus' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:1.25rem;">Dades del Site</h2>

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
             TAB: MÒDULS
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'moduls' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Mòduls actius</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.5rem;">
                    Activa o desactiva funcionalitats. Alguns canvis requereixen recarregar la pàgina per fer efecte.
                </p>

                {{-- ── Mòdul Campus ── --}}
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 0;border-bottom:1px solid #e5e7eb;cursor:pointer;">
                    <input type="checkbox" wire:model.live="campus_enabled"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.125rem;">Mòdul Campus</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">Catàleg de cursos, portals d'alumnes i professors, inscripcions i pagaments. Quan és desactivat, els admins segueixen tenint accés al panell.</p>
                    </div>
                </label>

                {{-- Sub-mòduls de Campus (indentats) --}}
                <div style="margin-left:1.875rem;border-left:2px solid #e5e7eb;padding-left:1rem;">

                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid #f3f4f6;cursor:pointer;{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <input type="checkbox" wire:model.live="documents_enabled" {{ $campus_enabled ? '' : 'disabled' }}
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">Mòdul de documents</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">Permet pujar i compartir documents amb alumnes i professors.</p>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid #f3f4f6;cursor:pointer;{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <input type="checkbox" wire:model.live="lms_enabled" {{ $campus_enabled ? '' : 'disabled' }}
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">LMS (plataforma d'aprenentatge)</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">Activa les funcions de LMS: lliçons, qüestionaris i seguiment del progrés.</p>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid #f3f4f6;cursor:pointer;{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <input type="checkbox" wire:model.live="courses_learning_enabled" {{ $campus_enabled ? '' : 'disabled' }}
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">Aprenentatge en línia de cursos</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">Permet als alumnes visualitzar continguts de vídeo i materials en línia.</p>
                        </div>
                    </label>

                    {{-- ── Cursos i Professorat ── --}}
                    @foreach ([
                        ['key' => 'campus_cursos_enabled',      'label' => 'Cursos',      'desc' => 'Gestió del catàleg de cursos, edicions i continguts.'],
                        ['key' => 'campus_professorat_enabled', 'label' => 'Professorat', 'desc' => 'Fitxes de professors, liquidacions i assignació a cursos.'],
                    ] as $sub)
                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid #f3f4f6;cursor:pointer;{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <input type="checkbox" wire:model.live="{{ $sub['key'] }}" {{ $campus_enabled ? '' : 'disabled' }}
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">{{ $sub['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach

                    {{-- ── Catàleg ── --}}
                    <div style="{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;cursor:pointer;">
                            <input type="checkbox" wire:model.live="cataleg_enabled" {{ $campus_enabled ? '' : 'disabled' }}
                                   style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                            <div>
                                <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">Catàleg</p>
                                <p style="font-size:0.8rem;color:#6b7280;margin:0;">Períodes acadèmics, categories, espais i franges horàries.</p>
                            </div>
                        </label>

                        @if ($campus_enabled && $cataleg_enabled)
                        <div style="margin-left:1.5rem;border-left:2px solid #f3f4f6;padding-left:0.75rem;">
                            @foreach ([
                                ['key' => 'cataleg_periodes_enabled',   'label' => 'Períodes',          'desc' => 'Temporades acadèmiques (tardor, primavera...).'],
                                ['key' => 'cataleg_categories_enabled', 'label' => 'Categories',        'desc' => 'Classificació temàtica dels cursos.'],
                                ['key' => 'cataleg_espais_enabled',     'label' => 'Espais',            'desc' => 'Aules i sales on s\'imparteixen els cursos.'],
                                ['key' => 'cataleg_franges_enabled',    'label' => 'Franges horàries',  'desc' => 'Torns horaris disponibles (matí, tarda...).'],
                            ] as $sub)
                            <label style="display:flex;align-items:flex-start;gap:0.625rem;padding:0.5rem 0;border-top:1px solid #f9fafb;cursor:pointer;">
                                <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                                       style="margin-top:0.15rem;width:0.875rem;height:0.875rem;border-radius:0.2rem;cursor:pointer;">
                                <div>
                                    <p style="font-size:0.8125rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                                    <p style="font-size:0.75rem;color:#9ca3af;margin:0;">{{ $sub['desc'] }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- ── Tresoreria ── --}}
                    <div style="{{ $campus_enabled ? '' : 'opacity:0.45;pointer-events:none;' }}">
                        <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid #f3f4f6;cursor:pointer;">
                            <input type="checkbox" wire:model.live="tresoreria_enabled" {{ $campus_enabled ? '' : 'disabled' }}
                                   style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                            <div>
                                <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">Tresoreria</p>
                                <p style="font-size:0.8rem;color:#6b7280;margin:0;">Inscripcions, pagaments, liquidacions de professors i gestió d'alumnes.</p>
                            </div>
                        </label>

                        @if ($campus_enabled && $tresoreria_enabled)
                        <div style="margin-left:1.5rem;border-left:2px solid #f3f4f6;padding-left:0.75rem;">
                            @foreach ([
                                ['key' => 'tresoreria_inscripcions_enabled',  'label' => 'Inscripcions',           'desc' => 'Gestió de matrícules i el seu estat de pagament.'],
                                ['key' => 'tresoreria_pagaments_enabled',     'label' => 'Pagaments',              'desc' => 'Registre de pagaments rebuts (Stripe, transferència, Bizum...).'],
                                ['key' => 'tresoreria_liquidacions_enabled',  'label' => 'Liquidacions professors','desc' => 'Càlcul i gestió de liquidacions econòmiques dels professors.'],
                                ['key' => 'tresoreria_alumnes_enabled',       'label' => 'Alumnes',                'desc' => 'Fitxes dels alumnes, accés al portal i historial de cursos.'],
                                ['key' => 'tresoreria_ips_enabled',           'label' => 'IPs bloquejades',        'desc' => 'Control d\'adreces IP amb accés restringit al portal.'],
                            ] as $sub)
                            <label style="display:flex;align-items:flex-start;gap:0.625rem;padding:0.5rem 0;border-top:1px solid #f9fafb;cursor:pointer;">
                                <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                                       style="margin-top:0.15rem;width:0.875rem;height:0.875rem;border-radius:0.2rem;cursor:pointer;">
                                <div>
                                    <p style="font-size:0.8125rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                                    <p style="font-size:0.75rem;color:#9ca3af;margin:0;">{{ $sub['desc'] }}</p>
                                </div>
                            </label>
                            @endforeach

                            {{-- Quotes i Remeses SEPA socis: depenen de Associats --}}
                            @foreach ([
                                ['key' => 'tresoreria_quotes_socis_enabled', 'label' => 'Quotes socis',       'desc' => 'Gestió de quotes de socis des de Tresoreria. Requereix el mòdul Associats actiu.'],
                                ['key' => 'tresoreria_sepa_socis_enabled',   'label' => 'Remeses SEPA socis', 'desc' => 'Gestió de remeses SEPA de socis des de Tresoreria. Requereix el mòdul Associats actiu.'],
                            ] as $sub)
                            <label style="display:flex;align-items:flex-start;gap:0.625rem;padding:0.5rem 0;border-top:1px solid #f9fafb;{{ $associats_enabled ? 'cursor:pointer;' : 'opacity:0.45;cursor:not-allowed;' }}">
                                <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                                       {{ $associats_enabled ? '' : 'disabled' }}
                                       style="margin-top:0.15rem;width:0.875rem;height:0.875rem;border-radius:0.2rem;">
                                <div>
                                    <p style="font-size:0.8125rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                                    <p style="font-size:0.75rem;color:#9ca3af;margin:0;">
                                        {{ $sub['desc'] }}
                                        @if (!$associats_enabled)
                                            <em style="color:#ef4444;">(Activa el mòdul Associats primer)</em>
                                        @endif
                                    </p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>

                </div>

                {{-- ── Mòdul Gestió ── --}}
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 0;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;margin-top:0.5rem;cursor:pointer;">
                    <input type="checkbox" wire:model.live="gestio_enabled"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.125rem;">Mòdul Gestió</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">Eines d'administració del panell: usuaris, rols i calendari de cursos.</p>
                    </div>
                </label>

                @if ($gestio_enabled)
                <div style="margin-left:1.875rem;border-left:2px solid #e5e7eb;padding-left:1rem;padding-top:0.5rem;">
                    @foreach ([
                        ['key' => 'gestio_administracio_enabled', 'label' => 'Administració', 'desc' => 'Gestió d\'usuaris del panell i assignació de rols.'],
                        ['key' => 'gestio_calendari_enabled',     'label' => 'Calendari',     'desc' => 'Vista de calendari amb tots els cursos i sessions programades.'],
                    ] as $sub)
                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid #f3f4f6;cursor:pointer;">
                        <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">{{ $sub['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @endif

                {{-- ── Mòdul Associats ── --}}
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 0;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;margin-top:0.5rem;cursor:pointer;">
                    <input type="checkbox" wire:model.live="associats_enabled"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.125rem;">Mòdul Associats</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">Gestió de socis de l'entitat: portal <code>/socis</code>, carnet digital amb QR i Passaport Cultural Digital.</p>
                    </div>
                </label>

                {{-- Config Associats quan actiu --}}
                @if ($associats_enabled)
                <div style="margin-left:1.875rem;border-left:2px solid #e5e7eb;padding-left:1rem;padding-top:0.5rem;">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem;">
                        <div style="grid-column:span 2;">
                            <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Nom de l'entitat</label>
                            <input type="text" wire:model="associats_org_name" placeholder="Nom de l'entitat"
                                   style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                            <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.2rem;">Apareix al carnet digital i als correus als socis.</p>
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Prefix del número de soci</label>
                            <input type="text" wire:model="associats_member_prefix" placeholder="Deixa buit si no en vols"
                                   style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                            <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.2rem;">Ex: "ACG-" mostrarà "ACG-1947".</p>
                        </div>
                    </div>

                    {{-- Creditor SEPA de l'entitat --}}
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f3f4f6;">
                        <p style="font-size:0.75rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#9ca3af;margin-bottom:0.75rem;">Creditor SEPA de l'entitat</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <div style="grid-column:span 2;">
                                <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Identificador de creditor SEPA</label>
                                <input type="text" wire:model="sepa_creditor_id" placeholder="ES12ZZZ09999994"
                                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;font-family:monospace;box-sizing:border-box;">
                                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.2rem;">Assignat per el banc. Necessari per al fitxer pain.008.</p>
                            </div>
                            <div style="grid-column:span 2;">
                                <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Nom legal de l'entitat</label>
                                <input type="text" wire:model="sepa_org_name" placeholder="Nom legal de l'entitat"
                                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">IBAN de l'entitat (receptor)</label>
                                <input type="text" wire:model="sepa_iban" placeholder="ES91 2100 0418 4502 0005 1332"
                                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;font-family:monospace;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.8rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">BIC / SWIFT</label>
                                <input type="text" wire:model="sepa_bic" placeholder="CAIXESBBXXX"
                                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;font-family:monospace;box-sizing:border-box;">
                                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.2rem;">Alguns bancs encara el requereixen.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sub-mòduls actius --}}
                    @foreach ([
                        ['key' => 'associats_socis_enabled',  'label' => 'Socis',        'desc' => 'Gestió de persones sòcies: crear, editar, fitxa i carnet digital amb QR.'],
                        ['key' => 'associats_quotes_enabled', 'label' => 'Quotes',       'desc' => 'Gestió de quotes periòdiques (anual, semestral, trimestral, mensual).'],
                        ['key' => 'associats_sepa_enabled',   'label' => 'Remeses SEPA', 'desc' => 'Generació de fitxers pain.008 per a domiciliació bancària SEPA.'],
                    ] as $sub)
                    <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid #f3f4f6;cursor:pointer;">
                        <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                               style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;cursor:pointer;">
                        <div>
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">{{ $sub['label'] }}</p>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0;">{{ $sub['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach

                    {{-- Sub-mòduls pròximament --}}
                    <div style="opacity:0.45;pointer-events:none;">
                        <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-top:1px solid #f3f4f6;">
                            <input type="checkbox" disabled style="margin-top:0.125rem;width:1rem;height:1rem;border-radius:0.25rem;">
                            <div>
                                <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.1rem;">Notificacions i comunicats <span style="font-size:0.7rem;background:#f3f4f6;color:#6b7280;padding:0.1rem 0.4rem;border-radius:999px;margin-left:0.25rem;">Pròximament</span></p>
                                <p style="font-size:0.8rem;color:#6b7280;margin:0;">Enviament de comunicats personalitzats als socis per correu electrònic.</p>
                            </div>
                        </label>
                    </div>

                </div>
                @endif

                {{-- ── Mòdul Notícies ── --}}
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 0;border-top:1px solid #e5e7eb;margin-top:0.5rem;cursor:pointer;">
                    <input type="checkbox" wire:model.live="noticies_enabled"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0 0 0.125rem;">Mòdul Notícies</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">Notícies i comunicats del site. Si es desactiva, desapareix del sidebar de l'administrador i de la pàgina pública <code>/noticies</code>.</p>
                    </div>
                </label>

            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: CATÀLEG (camps visibles a la fitxa pública de curs)
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'cataleg' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Camps del catàleg públic</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.5rem;">
                    Tria quines dades es mostren a les targetes i fitxes de curs de <code>/cursos</code>. El títol sempre es mostra.
                </p>

                @foreach ([
                    ['key' => 'catalog_show_category',    'label' => 'Categoria',            'desc' => 'Etiqueta de categoria del curs.'],
                    ['key' => 'catalog_show_code',         'label' => 'Codi',                 'desc' => 'Codi intern del curs (ex: AULOBE).'],
                    ['key' => 'catalog_show_dates',        'label' => 'Dates',                'desc' => 'Data d\'inici i final del curs.'],
                    ['key' => 'catalog_show_space',        'label' => 'Espai',                'desc' => 'Aula o sala on s\'imparteix.'],
                    ['key' => 'catalog_show_format',       'label' => 'Format',               'desc' => 'Presencial, Online o Híbrid.'],
                    ['key' => 'catalog_show_sessions',     'label' => 'Sessions',             'desc' => 'Nombre de sessions del curs.'],
                    ['key' => 'catalog_show_hours',        'label' => 'Hores',                'desc' => 'Durada total en hores.'],
                    ['key' => 'catalog_show_places',       'label' => 'Places',               'desc' => 'Aforament i disponibilitat.'],
                    ['key' => 'catalog_show_price',        'label' => 'Preu',                 'desc' => 'Import del curs.'],
                    ['key' => 'catalog_show_description',  'label' => 'Descripció',           'desc' => 'Text descriptiu del curs.'],
                    ['key' => 'catalog_show_objectives',   'label' => 'Objectius',            'desc' => 'Objectius d\'aprenentatge (fitxa de detall).'],
                    ['key' => 'catalog_show_requirements', 'label' => 'Requisits',            'desc' => 'Requisits previs (fitxa de detall).'],
                ] as $sub)
                <label style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid #f3f4f6;cursor:pointer;">
                    <input type="checkbox" wire:model.live="{{ $sub['key'] }}"
                           style="margin-top:0.125rem;width:1.125rem;height:1.125rem;border-radius:0.25rem;cursor:pointer;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:500;color:#111827;margin:0 0 0.125rem;">{{ $sub['label'] }}</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">{{ $sub['desc'] }}</p>
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
                    Variables disponibles:
                    <code style="background:#f3f4f6;padding:0.1rem 0.4rem;border-radius:0.25rem;">{NOM}</code> nom de l'alumne,
                    <code style="background:#f3f4f6;padding:0.1rem 0.4rem;border-radius:0.25rem;">{CURS}</code> títol del curs,
                    <code style="background:#eef2ff;color:#4f46e5;padding:0.1rem 0.4rem;border-radius:0.25rem;">{REFERENCIA}</code>
                    <span style="font-size:0.75rem;color:#92400e;font-weight:500;">codi únic de ticket/carret</span>
                    <span style="font-size:0.75rem;color:#9ca3af;">(un sol codi per tots els cursos d'un mateix carret — obligatori per identificar el pagament)</span>.
                </p>
                <input type="text" wire:model="payment_concept_template"
                       placeholder="{NOM} - {REFERENCIA}"
                       style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.375rem;">
                    Recomanat: <code style="background:#f3f4f6;padding:0.1rem 0.3rem;border-radius:0.2rem;">{NOM} - {REFERENCIA}</code>.
                    Si el carret conté múltiples cursos, <code style="background:#f3f4f6;padding:0.1rem 0.3rem;border-radius:0.2rem;">{CURS}</code> mostrarà el títol del primer curs.
                </p>
            </div>

            {{-- Límit de cursos al carret --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Límit de cursos al carret</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1rem;">
                    Nombre màxim de cursos que un alumne pot tenir en un mateix carret (per transacció).
                    Útil per evitar reserves massives en períodes d'alta demanda.
                </p>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <input type="number" wire:model="payment_max_cart_items"
                           min="1" max="20"
                           style="width:5.5rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;text-align:center;">
                    <span style="font-size:0.875rem;color:#6b7280;">cursos per carret</span>
                </div>
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.375rem;">
                    Valor recomanat: <strong>5</strong>. Mínim: 1.
                </p>
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
             TAB: CUA D'INSCRIPCIONS
        ══════════════════════════════════════════════════════════════ --}}
        <div style="{{ $activeTab !== 'cua' ? 'display:none;' : '' }}">

            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.375rem;">Cua d'inscripcions</h2>
                <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.25rem;">
                    Quan hi ha molta demanda, la cua garanteix un accés ordenat al catàleg.
                    Cada alumne rep un número de torn i una hora estimada d'accés.
                </p>

                {{-- Toggle principal --}}
                <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:1rem;background:#f9fafb;border-radius:0.5rem;margin-bottom:1.25rem;">
                    <input type="checkbox" wire:model.live="queue_enabled"
                           style="width:1.25rem;height:1.25rem;border-radius:0.25rem;cursor:pointer;accent-color:#4f46e5;">
                    <div>
                        <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin:0;">Activar la cua d'inscripcions</p>
                        <p style="font-size:0.8125rem;color:#6b7280;margin:0;">
                            Quan és activa, els alumnes han d'entrar a la cua per accedir al catàleg de cursos.
                        </p>
                    </div>
                </label>

                @if ($queue_enabled)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

                    {{-- Data i hora d'obertura --}}
                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            Data i hora d'inici de la cua
                        </label>
                        <input type="datetime-local" wire:model="queue_start_at"
                               style="width:100%;max-width:20rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;box-sizing:border-box;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                            Fins a aquesta hora, el catàleg és accessible sense restriccions.
                            Deixeu en blanc per activar la cua immediatament.
                        </p>
                    </div>

                    {{-- Mida del lot --}}
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            Alumnes per torn (lot)
                        </label>
                        <input type="number" wire:model="queue_batch_size"
                               min="1" max="500"
                               style="width:7rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;text-align:center;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                            Quants alumnes accedeixen al mateix temps (en el mateix slot horari).
                        </p>
                    </div>

                    {{-- Durada del slot --}}
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            Durada de cada slot (minuts)
                        </label>
                        <input type="number" wire:model="queue_slot_minutes"
                               min="1" max="240"
                               style="width:7rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;text-align:center;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                            Cada quants minuts s'obre el torn al grup següent.
                        </p>
                    </div>

                    {{-- Finestra d'accés --}}
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            Temps per usar el codi (minuts)
                        </label>
                        <input type="number" wire:model="queue_access_window_minutes"
                               min="5" max="120"
                               style="width:7rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;text-align:center;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                            Minuts que té l'alumne per entrar el codi un cop notificat.
                        </p>
                    </div>

                    {{-- Resum --}}
                    @if ($queue_batch_size > 0 && $queue_slot_minutes > 0)
                    <div style="grid-column:span 2;padding:0.875rem;background:#eef2ff;border:1px solid #c7d2fe;border-radius:0.5rem;">
                        <p style="font-size:0.8125rem;color:#3730a3;margin:0;">
                            📊 Cada <strong>{{ $queue_slot_minutes }} min</strong> accediran
                            <strong>{{ $queue_batch_size }}</strong> alumnes.
                            Exemple: 100 apuntats → l'últim accedeix al cap de
                            <strong>{{ round(ceil(100 / $queue_batch_size) * $queue_slot_minutes) }} min</strong>.
                        </p>
                    </div>
                    @endif

                </div>

                {{-- Instruccions scheduler --}}
                <div style="margin-top:1.25rem;padding:0.875rem 1rem;background:#fefce8;border:1px solid #fef08a;border-radius:0.5rem;">
                    <p style="font-size:0.8125rem;color:#713f12;margin:0 0 0.25rem;"><strong>⚙️ Scheduler</strong></p>
                    <p style="font-size:0.8125rem;color:#92400e;margin:0;">
                        Cal que el scheduler de Laravel estigui en marxa:
                        <code style="background:#fef9c3;padding:0.1rem 0.4rem;border-radius:0.25rem;">php artisan schedule:run</code>
                        cada minut via cron, o bé
                        <code style="background:#fef9c3;padding:0.1rem 0.4rem;border-radius:0.25rem;">php artisan schedule:work</code>
                        en local. La comanda <code style="background:#fef9c3;padding:0.1rem 0.4rem;border-radius:0.25rem;">queue:notify</code>
                        s'executa cada 5 minuts automàticament.
                    </p>
                </div>
                @endif

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

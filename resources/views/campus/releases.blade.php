@extends('campus.layouts.app')

@section('title', 'Novetats · ' . setting('campus_name', 'Campus'))

@section('content')
<div style="max-width:700px; margin:0 auto;">

    {{-- Capçalera --}}
    <div style="margin-bottom:2.5rem; border-bottom:1px solid #e5e7eb; padding-bottom:1.75rem;">
        <p style="font-size:0.65rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; font-weight:600; margin-bottom:0.5rem;">
            Historial de versions
        </p>
        <h1 style="font-size:2rem; font-weight:700; color:#111827; margin-bottom:0.5rem;">Novetats</h1>
        <p style="color:#6b7280; font-size:0.9rem; line-height:1.75;">
            Aquí trobaràs un registre de tot el que va millorant al
            <strong>{{ setting('campus_name', 'Campus') }}</strong>, explicat sense tecnicismes.
        </p>
    </div>

    {{-- ── v1.3.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.3.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#fff; background:#6366f1; padding:0.2rem 0.6rem; border-radius:3px;">
                Nou
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Maig 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                El LMS incorpora avaluació interactiva i emissió automàtica de certificats
                al final de cada curs.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Preguntes i respostes a les sessions
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada sessió pot incloure preguntes de resposta única. L'alumne les respon
                    al seu ritme i pot revisar les respostes en qualsevol moment. El professor
                    les gestiona directament des del panell d'administració.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Certificat de finalització
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    En completar totes les sessions d'un curs, l'alumne obté un certificat
                    de finalització personalitzat amb el seu nom i la data de superació.
                    El certificat és accessible des del portal en qualsevol moment posterior.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.2.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.2.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Millores
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Maig 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Seguretat reforçada i cua d'inscripcions per gestionar l'accés a cursos
                amb alta demanda sense col·lapsar el sistema.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Verificació per OTP
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El registre i la recuperació de contrasenya s'han migrat a codis OTP per correu.
                    Sense contrasenyes temporals que caduquen: l'alumne introdueix un codi de 6 dígits
                    i accedeix immediatament.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Protecció anti-frau
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Rate limiting per evitar enviaments massius, honeypot invisible per bots,
                    bloqueig manual d'IPs des del panell d'administració i límit de matrícules
                    pendents per compte.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Cua d'inscripcions amb torns
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Quan un curs obre inscripcions, l'alumne pot apuntar-se a la cua i tria
                    una franja horària d'atenció. El sistema envia un avís quan arriba el seu torn
                    i permet canviar l'hora si no convé. El pagament es finalitza dins de la finestra
                    assignada.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.1.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.1.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Millores
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Maig 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Checkout complet amb múltiples modalitats de pagament, control de places,
                cancel·lacions i devolucions.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Pagament manual i Stripe
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    A més de Stripe, l'alumne pot pagar per transferència bancària, Bizum, efectiu
                    o PayPal. Cada mètode genera una referència única i una data de caducitat
                    configurable. L'administrador confirma el pagament des del panell.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Control de places
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Les places pendents de pagament compten al límit de capacitat igual que
                    les pagades. Quan s'omple el curs, el botó d'inscripció desapareix del catàleg.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Cancel·lacions i devolucions
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    L'alumne pot cancel·lar una inscripció pendent i tornar a inscriure's
                    amb un altre mètode. Les devolucions es gestionen des de l'administrador
                    i envien una confirmació per correu a l'alumne.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.0.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.0.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#fff; background:#111827; padding:0.2rem 0.6rem; border-radius:3px;">
                Versió inicial
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Maig 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Primera versió pública del <strong>{{ setting('campus_name', 'Campus') }}</strong>.
                Catàleg de cursos, portal d'alumnes, portal de professors i LMS integrat.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Catàleg i inscripcions
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Catàleg públic de cursos amb categories, fitxa detallada, indicador de places
                    disponibles i control d'inscripcions obertes o tancades per temporada.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Portal d'alumnes
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Registre, accés i consulta de matrícules. L'alumne veu l'estat de cada
                    inscripció (pendent, pagada, cancel·lada) i accedeix als continguts LMS
                    dels cursos en que s'ha matriculat.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Portal de professors
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada professor accedeix als seus cursos, puja material, gestiona sessions
                    i consulta les liquidacions. Pot crear nous cursos LMS amb un wizard de
                    tres passos que envia la proposta a l'administrador per a revisió.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;LMS integrat
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada curs pot tenir sessions en format LMS amb contingut estructurat.
                    L'alumne avança al seu ritme i el professor veu el progrés des del seu portal.
                    L'administrador aprova els cursos i supervisa el conjunt des de Filament.
                </p>
            </div>
        </div>
    </div>

    {{-- Tornar --}}
    <div style="padding-top:1.5rem; border-top:1px solid #e5e7eb;">
        <a href="{{ route('campus.catalog.index') }}"
           style="font-size:0.875rem; color:#6366f1; text-decoration:none; font-weight:500;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            ← Tornar al catàleg
        </a>
    </div>

</div>
@endsection

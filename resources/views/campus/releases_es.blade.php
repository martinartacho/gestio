@extends('campus.layouts.app')

@section('title', 'Novedades · ' . setting('campus_name', 'Campus'))

@section('content')
<div style="max-width:700px; margin:0 auto;">

    {{-- Capçalera --}}
    <div style="margin-bottom:2.5rem; border-bottom:1px solid #e5e7eb; padding-bottom:1.75rem;">
        <p style="font-size:0.65rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; font-weight:600; margin-bottom:0.5rem;">
            Historial de versiones
        </p>
        <h1 style="font-size:2rem; font-weight:700; color:#111827; margin-bottom:0.5rem;">Novedades</h1>
        <p style="color:#6b7280; font-size:0.9rem; line-height:1.75;">
            Aquí encontrarás un registro de todo lo que va mejorando en el
            <strong>{{ setting('campus_name', 'Campus') }}</strong>, explicado sin tecnicismos.
        </p>
    </div>

    {{-- ── v1.7.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.7.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#fff; background:#6366f1; padding:0.2rem 0.6rem; border-radius:3px;">
                Nuevo
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Junio 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                El LMS incorpora un <strong>curso de introducción a GestorApp</strong> con 6 sesiones publicadas,
                soporte para <strong>imágenes múltiples por sesión</strong> con posicionamiento flexible,
                y recuperación de contraseña para profesores mediante <strong>código OTP por correo</strong>.
            </p>

            <div style="margin-bottom:1rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Curso de introducción GestorApp (LMS)
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    6 sesiones publicadas que guían al administrador por la plataforma: configuración inicial,
                    catálogo de cursos, tesorería, módulo de asociados y gestión de roles y usuarios.
                    Accesible desde el portal de alumnos.
                </p>
            </div>

            <div style="margin-bottom:1rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Imágenes múltiples por sesión LMS
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El profesor puede añadir varias imágenes a cada sesión, eligiendo en qué apartado
                    aparecerán (después de la cita, la introducción, el tema…) y añadiendo un pie de foto opcional.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Recuperación de contraseña de profesores (OTP)
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El registro y la recuperación de contraseña se han migrado a códigos OTP por correo.
                    Sin contraseñas temporales que caducan: el profesor introduce un código de 6 dígitos
                    y accede inmediatamente.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.6.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.6.0</span>
            <span style="font-size:0.65rem; color:#9ca3af;">Junio 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                El responsable de Tesorería dispone de un <strong>resumen financiero consolidado</strong>
                en una sola página: inscripciones, pagos, liquidaciones de profesores y cuotas de socios,
                todo visible a la vez y adaptado a los módulos activos.
            </p>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Dashboard Resumen Financiero
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Nueva página accesible desde el menú Tesorería. Muestra KPIs por estado
                    (pendiente, pagado, cancelado…) con el número de registros y el importe total de cada uno.
                    Si un submódulo se desactiva desde la Configuración, su sección desaparece
                    automáticamente del resumen.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.5.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.5.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Mejoras
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Junio 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                La Tesorería se convierte en el <strong>centro de todos los flujos económicos</strong>
                de la plataforma, independientemente del módulo de origen: Campus, Asociados y, en el futuro, Tickets y Tienda.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Cuotas de socios en Tesorería
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El rol de Tesorería puede consultar y gestionar todas las cuotas de los socios
                    directamente desde su grupo de navegación, sin necesidad de acceder al módulo
                    de Asociados. La opción de activación es independiente.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Remesas SEPA en Tesorería
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Las remesas de domiciliación bancaria de los socios también son accesibles y gestionables
                    desde Tesorería, con visión transversal de todos los socios y estado de cada remesa
                    (borrador, XML generado, enviada al banco, procesada).
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.4.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.4.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Mejoras
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Mayo 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                La plataforma se ha preparado para acoger nuevos módulos independientes.
                Estreno del <strong>Pasaporte Cultural Digital</strong> para socios de entidades culturales.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Módulo Campus activable/desactivable
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El administrador puede activar o desactivar el campus de formación desde el panel
                    de configuración. Cuando está desactivado, el catálogo y los portales de alumnos y profesores
                    dejan de ser accesibles a los visitantes, sin afectar a la administración.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Pasaporte Cultural Digital (módulo Asociados)
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Primer paso de la digitalización de los carnés físicos de la entidad.
                    Cada socio puede acceder al portal con correo y contraseña y obtener su
                    <strong>carné digital</strong> con código QR único que conserva el número
                    de socio histórico. El módulo se activa desde la configuración y es completamente
                    independiente del campus de formación.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.3.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.3.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Mejoras
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Mayo 2026</span>
        </div>

        <div style="border-left:2px solid #6366f1; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                El LMS incorpora evaluación interactiva y emisión automática de certificados
                al final de cada curso.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Preguntas y respuestas en las sesiones
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada sesión puede incluir preguntas de respuesta única. El alumno las responde
                    a su ritmo y puede revisar las respuestas en cualquier momento. El profesor
                    las gestiona directamente desde el panel de administración.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#6366f1; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Certificado de finalización
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Al completar todas las sesiones de un curso, el alumno obtiene un certificado
                    de finalización personalizado con su nombre y la fecha de superación.
                    El certificado es accesible desde el portal en cualquier momento posterior.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.2.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.2.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Mejoras
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Mayo 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Seguridad reforzada y cola de inscripciones para gestionar el acceso a cursos
                con alta demanda sin colapsar el sistema.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Verificación por OTP
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El registro y la recuperación de contraseña se han migrado a códigos OTP por correo.
                    Sin contraseñas temporales que caducan: el alumno introduce un código de 6 dígitos
                    y accede inmediatamente.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Protección antifraude
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Límite de peticiones para evitar envíos masivos, honeypot invisible para bots,
                    bloqueo manual de IPs desde el panel de administración y límite de matrículas
                    pendientes por cuenta.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Cola de inscripciones con turnos
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cuando un curso abre inscripciones, el alumno puede apuntarse a la cola y elegir
                    una franja horaria de atención. El sistema envía un aviso cuando llega su turno
                    y permite cambiar la hora si no le conviene. El pago se finaliza dentro de la ventana
                    asignada.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.1.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.1.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#374151; background:#e5e7eb; padding:0.2rem 0.6rem; border-radius:3px;">
                Mejoras
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Mayo 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Checkout completo con múltiples modalidades de pago, control de plazas,
                cancelaciones y devoluciones.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Pago manual y Stripe
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Además de Stripe, el alumno puede pagar por transferencia bancaria, Bizum, efectivo
                    o PayPal. Cada método genera una referencia única y una fecha de caducidad
                    configurable. El administrador confirma el pago desde el panel.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Control de plazas
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Las plazas pendientes de pago cuentan en el límite de capacidad igual que
                    las pagadas. Cuando el curso se llena, el botón de inscripción desaparece del catálogo.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Cancelaciones y devoluciones
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    El alumno puede cancelar una inscripción pendiente y volver a inscribirse
                    con otro método. Las devoluciones se gestionan desde el administrador
                    y envían una confirmación por correo al alumno.
                </p>
            </div>
        </div>
    </div>

    {{-- ── v1.0.0 ── --}}
    <div style="margin-bottom:3rem;">
        <div style="display:flex; align-items:baseline; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <span style="font-size:1.35rem; font-weight:700; color:#111827;">v1.0.0</span>
            <span style="font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:#fff; background:#111827; padding:0.2rem 0.6rem; border-radius:3px;">
                Versión inicial
            </span>
            <span style="font-size:0.65rem; color:#9ca3af;">Mayo 2026</span>
        </div>

        <div style="border-left:2px solid #e5e7eb; padding-left:1.5rem;">
            <p style="color:#6b7280; font-size:0.9rem; line-height:1.8; margin-bottom:1.5rem;">
                Primera versión pública del <strong>{{ setting('campus_name', 'Campus') }}</strong>.
                Catálogo de cursos, portal de alumnos, portal de profesores y LMS integrado.
            </p>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Catálogo e inscripciones
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Catálogo público de cursos con categorías, ficha detallada, indicador de plazas
                    disponibles y control de inscripciones abiertas o cerradas por temporada.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Portal de alumnos
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Registro, acceso y consulta de matrículas. El alumno ve el estado de cada
                    inscripción (pendiente, pagada, cancelada) y accede a los contenidos LMS
                    de los cursos en los que se ha matriculado.
                </p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;Portal de profesores
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada profesor accede a sus cursos, sube material, gestiona sesiones
                    y consulta las liquidaciones. Puede crear nuevos cursos LMS con un asistente de
                    tres pasos que envía la propuesta al administrador para su revisión.
                </p>
            </div>

            <div style="margin-bottom:0;">
                <div style="font-size:0.62rem; letter-spacing:0.2em; text-transform:uppercase; color:#9ca3af; margin-bottom:0.4rem; font-weight:600;">
                    ✦ &nbsp;LMS integrado
                </div>
                <p style="color:#6b7280; font-size:0.875rem; line-height:1.75;">
                    Cada curso puede tener sesiones en formato LMS con contenido estructurado.
                    El alumno avanza a su ritmo y el profesor ve el progreso desde su portal.
                    El administrador aprueba los cursos y supervisa el conjunto desde Filament.
                </p>
            </div>
        </div>
    </div>

    {{-- Volver --}}
    <div style="padding-top:1.5rem; border-top:1px solid #e5e7eb;">
        <a href="{{ route('campus.catalog.index') }}"
           style="font-size:0.875rem; color:#6366f1; text-decoration:none; font-weight:500;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            ← Volver al catálogo
        </a>
    </div>

</div>
@endsection

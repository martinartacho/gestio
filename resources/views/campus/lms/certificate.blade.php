<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat · {{ $course->title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #f9fafb;
            color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 2rem 1rem;
        }

        .no-print {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-print {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .btn-back {
            background: transparent;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .certificate {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            max-width: 780px;
            width: 100%;
            padding: 0;
            overflow: hidden;
        }

        .cert-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            text-align: center;
            padding: 2.5rem 2rem 2rem;
        }

        .cert-header .org-name {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        .cert-header h1 {
            font-size: 2rem;
            font-weight: 400;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .cert-header .subtitle {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.8125rem;
            opacity: 0.75;
        }

        .cert-body {
            padding: 2.5rem 3rem;
            text-align: center;
        }

        .cert-body .label {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .cert-body .student-name {
            font-size: 2.25rem;
            font-style: italic;
            color: #111827;
            border-bottom: 2px solid #e0e7ff;
            display: inline-block;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .cert-body .completion-text {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.9375rem;
            color: #4b5563;
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }

        .cert-body .course-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e1b4b;
            margin-bottom: 2rem;
        }

        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 1.5rem 3rem 2.5rem;
            border-top: 1px solid #f3f4f6;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .cert-footer .info-block {
            text-align: left;
        }

        .cert-footer .info-block .info-label {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 0.25rem;
        }

        .cert-footer .info-block .info-value {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .cert-seal {
            width: 5rem;
            height: 5rem;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.75rem;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .certificate {
                border: none;
                border-radius: 0;
                max-width: 100%;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    {{-- Botons (no imprimibles) --}}
    <div class="no-print">
        <a href="{{ route('campus.lms.course', $course->slug) }}" class="btn-back">&larr; Tornar al curs</a>
        <button onclick="window.print()" class="btn-print">🖨 Imprimir / Descarregar PDF</button>
    </div>

    {{-- Certificat --}}
    <div class="certificate">

        <div class="cert-header">
            <p class="org-name">{{ config('app.name', 'GestioAPP') }}</p>
            <h1>Certificat d'Aprofitament</h1>
            <p class="subtitle">Learning Management System</p>
        </div>

        <div class="cert-body">
            <p class="label">Certifiquem que</p>
            <div class="student-name">{{ $student->full_name }}</div>

            <p class="completion-text">ha completat satisfactòriament el curs</p>

            <p class="course-title">{{ $course->title }}</p>
        </div>

        <div class="cert-footer">
            <div class="info-block">
                <p class="info-label">Data d'emissió</p>
                <p class="info-value">{{ $certificate->issued_at->format('d / m / Y') }}</p>
            </div>
            <div class="cert-seal">🎓</div>
            <div class="info-block" style="text-align:right;">
                <p class="info-label">Número de certificat</p>
                <p class="info-value" style="font-family:monospace;font-size:0.8125rem;">{{ $certificate->certificate_number }}</p>
            </div>
        </div>

    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de privacitat — Gestio App</title>
    <style>
        body { font-family: sans-serif; max-width: 720px; margin: 40px auto; padding: 0 24px; color: #1f2937; line-height: 1.7; }
        h1 { font-size: 28px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
        h2 { font-size: 18px; color: #374151; margin-top: 32px; }
        p, ul { color: #4b5563; }
        ul { padding-left: 20px; }
        footer { margin-top: 48px; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>
    <h1>Política de privacitat de Gestio App</h1>
    <p>Última actualització: {{ now()->format('d/m/Y') }}</p>

    <h2>1. Responsable del tractament</h2>
    <p>Pepe Martín Artacho<br>
    Correu de contacte: <a href="mailto:hola@artacho.org">hola@artacho.org</a></p>

    <h2>2. Dades que recollim</h2>
    <ul>
        <li>Nom, cognom i adreça electrònica (compte d'alumne o professorat)</li>
        <li>Telèfon (si consta a la fitxa de l'entitat)</li>
        <li>Dades de matrícula i cursos (estat, sessions, horaris)</li>
        <li>Dades tècniques del dispositiu per a l'enviament de notificacions push, quan aquesta funció estigui activa (token Firebase)</li>
    </ul>

    <h2>3. Finalitat del tractament</h2>
    <p>Les dades es fan servir exclusivament per gestionar la relació de l'usuari amb l'entitat que ofereix els cursos: consultar les matrícules i cursos propis (alumnat) o els cursos impartits i l'alumnat matriculat (professorat).</p>

    <h2>4. Base legal</h2>
    <p>El tractament es basa en l'execució de la relació contractual/formativa entre l'usuari i l'entitat (matrícula al curs) i, si escau, en el consentiment de l'usuari.</p>

    <h2>5. Tercers</h2>
    <p>L'aplicació pot utilitzar <strong>Firebase</strong> (Google LLC) per a l'enviament de notificacions push, quan aquesta funció estigui activa per a la teva entitat. Google pot emmagatzemar dades tècniques als seus servidors d'acord amb la seva pròpia política de privacitat: <a href="https://policies.google.com/privacy" target="_blank">policies.google.com/privacy</a>.</p>

    <h2>6. Conservació de les dades</h2>
    <p>Les dades es conserven mentre l'usuari tingui un compte actiu a l'entitat. En cas de baixa, les dades s'eliminen o anonimitzen a petició de l'usuari o de l'entitat responsable.</p>

    <h2>7. Drets de l'usuari</h2>
    <p>L'usuari pot exercir els drets d'accés, rectificació, supressió i portabilitat de les dades contactant per correu electrònic a <a href="mailto:hola@artacho.org">hola@artacho.org</a>.</p>

    <h2>8. Seguretat</h2>
    <p>Les dades es transmeten xifrades (HTTPS) i s'emmagatzemen en servidors protegits. Les contrasenyes es guarden amb hash irreversible.</p>

    <footer>
        Gestio App · Aplicació mòbil per a alumnat i professorat &nbsp;·&nbsp;
        <a href="mailto:hola@artacho.org">hola@artacho.org</a>
    </footer>
</body>
</html>

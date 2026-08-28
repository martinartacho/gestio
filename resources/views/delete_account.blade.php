<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sol·licitud d'eliminació de compte — Gestio App</title>
    <style>
        body { font-family: sans-serif; max-width: 720px; margin: 40px auto; padding: 0 24px; color: #1f2937; line-height: 1.7; }
        h1 { font-size: 28px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
        h2 { font-size: 18px; color: #374151; margin-top: 32px; }
        p, ul, ol { color: #4b5563; }
        ul, ol { padding-left: 20px; }
        .steps { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 24px; margin: 16px 0; }
        .steps ol { margin: 0; }
        footer { margin-top: 48px; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>
    <h1>Sol·licitud d'eliminació de compte a Gestio App</h1>
    <p>Última actualització: {{ now()->format('d/m/Y') }}</p>

    <h2>Com sol·licitar l'eliminació del teu compte</h2>
    <p>Pots sol·licitar l'eliminació del teu compte i les dades associades de dues maneres:</p>

    <div class="steps">
        <strong>Opció 1 — A través de l'entitat</strong>
        <ol>
            <li>Contacta amb la secretaria o administració de l'entitat on estàs matriculat/da.</li>
            <li>Sol·licita que eliminin el teu perfil d'alumne o professorat de l'aplicació.</li>
            <li>L'administrador eliminarà el teu compte des del panell de gestió.</li>
        </ol>
    </div>

    <div class="steps">
        <strong>Opció 2 — Per correu electrònic directament</strong>
        <ol>
            <li>Envia un correu a <a href="mailto:hola@artacho.org">hola@artacho.org</a> amb l'assumpte <em>"Eliminació de compte Gestio App"</em>.</li>
            <li>Indica el teu nom, cognoms i l'entitat a la qual pertanys.</li>
            <li>Rebràs confirmació en un termini màxim de 30 dies.</li>
        </ol>
    </div>

    <h2>Quines dades s'anonimitzen</h2>
    <p>Per preservar l'historial acadèmic i les estadístiques de l'entitat, el compte no s'elimina completament sinó que s'<strong>anonimitza</strong>: les dades personals identificables se substitueixen per valors neutres que no permeten associar-les amb cap persona.</p>
    <ul>
        <li>Nom i cognoms → substituïts per un identificador anònim</li>
        <li>Correu electrònic → substituït per <code>usuari{id}@baixa.gestio.test</code></li>
        <li>Telèfon i adreça → eliminats</li>
        <li>Tokens de notificació push → eliminats</li>
        <li>Tokens d'autenticació (sessions actives) → eliminats</li>
    </ul>

    <h2>Quines dades es conserven</h2>
    <p>L'historial de matrícules i cursos es conserva de forma anonimitzada per mantenir la integritat dels registres de l'entitat (p. ex. a efectes comptables o estadístics). Cap d'aquestes dades permet identificar la persona un cop anonimitzades.</p>

    <h2>Responsable del tractament</h2>
    <p>
        Pepe Martín Artacho<br>
        Aplicació: Gestio App<br>
        Correu: <a href="mailto:hola@artacho.org">hola@artacho.org</a>
    </p>

    <footer>
        Gestio App — Aplicació mòbil per a alumnat i professorat &nbsp;·&nbsp;
        <a href="/privacy">Política de privacitat</a>
    </footer>
</body>
</html>

# Gestió Campus

Panel d'administració per a la gestió de cursos, professors, espais i calendari d'un campus de formació continuada.

## Tecnologies

- **Laravel 13** + **PHP 8.3**
- **Filament 4** — panel d'administració
- **Spatie Laravel Permission** — rols i permisos
- **FullCalendar 6** — calendari visual de cursos
- **Stripe** — pagaments en línia per a inscripcions d'alumnes
- **Vite + Tailwind CSS 4**

## Funcionalitats

### Gestió del campus
| Mòdul | Descripció |
|---|---|
| Temporades | Anys acadèmics i quadrimestres amb dates d'inscripció; estat `draft` / `active` / `closed` |
| Categories | Àrees temàtiques amb color identificador |
| Espais | Aules i ubicacions amb capacitat |
| Franges horàries | Horaris setmanals reutilitzables |
| Professors | Fitxa completa: dades personals, fiscals, bancàries (encriptades), RGPD |
| Cursos | Gestió completa: codi, format, dates, sessions, preu, professors assignats |

### Regles de visibilitat i inscripció

| Condició | Resultat |
|---|---|
| `campus_seasons.status = active` | Temporada visible al catàleg |
| `campus_courses.status = active` AND `is_public = true` | Curs visible al catàleg |
| anterior + `season.enrollmentIsOpen()` | Inscripció disponible |
| `season.status = closed` | Temporada visible però no inscrivible |
| `season.status = draft` | Temporada oculta al públic |

**Mode previsualització** (`?preview=1`): usuaris del panel amb permís `courses.edit` poden veure tots els cursos i temporades (inclosos `draft` i `is_public=false`) des de `/cursos?preview=1` o des del botó **Vista prèvia** al CalendarPage.

### Calendari visual
- Vista mensual i setmanal de sessions per temporada
- Clic sobre un event per veure el detall del curs
- Drag & drop per reubicar dates (només admins)
- Títol de l'event: `codi_curs / codi_professor`
- **Llegenda ⓘ**: popover de colors per categoria, reactiu a la temporada seleccionada

### Pàgina llançadora

`/` — portal d'entrada amb accés directe per perfil: **Alumnat**, **Professorat** i **Gestió**. Catàleg de cursos i registre accessibles des de la mateixa pàgina.

### Mòdul Alumnat (portal públic)
| Recurs | Descripció |
|---|---|
| Catàleg públic | `/cursos` — llista de cursos actius i públics, filtrable per temporada |
| Detall de curs | `/cursos/{slug}` — fitxa completa amb botó d'inscripció o estat (finalitzat/properament) |
| Portal alumne | `/portal/meus-cursos` — llista d'inscripcions amb estat |
| Registre i login | `/portal/registre` i `/portal/login` — auth independent (guard `student`) |
| Pagament Stripe | Checkout Session amb confirmació via webhook |
| Llista alumnes | Panel Filament → Cursos → tab Alumnes (admin/manager) |

**Flux de pagament:**
1. Alumne selecciona curs → Stripe Checkout
2. Webhook `checkout.session.completed` → `campus_enrollments.status = paid`
3. Pivot `campus_course_student` s'actualitza amb l'alumne confirmat

### Mòdul Professorat (portal autenticat)

| Recurs | Descripció |
|---|---|
| Login | `/professorat/login` — auth independent (guard `teacher`) |
| Els meus cursos | `/professorat/portal` — cursos assignats amb badge d'alumnes i progress de sessions |
| Detall de curs | `/professorat/portal/curs/{slug}` — llista d'alumnes matriculats |
| Perfil | `/professorat/portal/perfil` — editar telèfon, bio i contrasenya |
| Liquidacions | `/professorat/portal/liquidacions` — resum d'ordres de pagament per curs |

**Progress de sessions**: si el curs té `calendar_notes` (dates reals), compta les sessions passades/futures a partir de les dates; si no, estimació setmanal. Exemple: `● 8 fetes · 2 per fer [████████░░ 80%]`.

### Mòdul Documents

| Recurs | Descripció |
|---|---|
| Tipus | Fitxer (PDF, Office, imatges, ZIP, àudio, vídeo) o Enllaç extern (URL) |
| Emmagatzematge | Disk `local` de Laravel — no exposat directament |
| Descàrrega segura | `GET /documents/{document}/download` — comprova accés per guard |
| Visibilitat | `public` (tothom) · `enrolled` (alumnes matriculats) · `private` (sol professor/a propietari/a) |
| Herència | `inherit_to_editions` — documents d'un curs template visibles als cursos fills |
| Admin (Filament) | `/admin/documents` — CRUD complet + tab **Documents** a cada curs |
| Portal professor | `/professorat/portal/curs/{slug}` — puja i elimina documents per curs |
| Portal alumne | `/portal/meus-cursos` — veu els documents `public` i `enrolled` dels cursos inscrits |

**Accés a la descàrrega:**
- `public` → qualsevol visitant (si és fitxer)
- `enrolled` → alumne amb matrícula `paid`/`pending` al curs (o curs pare / fills)
- `private` → únicament el professor/a propietari/a o admin
- Admin Filament → sempre pot descarregar

**Gestor global de documents (professorat):**
- `/professorat/portal/documents` — llista tots els documents propis i els d'altres professors (visibilitat `public` o `enrolled`), amb formulari d'edició complet (condicions d'accés, ordre, estat)
- Condicions d'accés (lògica OR): `available_from` (data) **o** `session_number` (sessió) — en complir-se qualsevol condició el document es desbloqueja per als alumnes
- Indicador ⏰ visible al portal del professor i al panel Filament

### Configuració del lloc (`/admin/settings-page`)

Pàgina d'administració (rol `admin`) per personalitzar el campus sense tocar el codi:

| Pestanya | Ajustos |
|---|---|
| 🏫 Campus | Nom, eslògan, logotip, favicon, contacte, adreça |
| 🎨 Aparença | Títol i subtítol del hero, colors de fons i text (amb previsualització en viu) |
| ✉️ Correu | Remitent dels correus automàtics (nom + adreça + peu) |
| 🔧 Mòduls | Feature flags: `documents_enabled`, `lms_enabled`, `courses_learning_enabled` |
| ⚙️ Avançat | Zona horària i idioma |

**Implementació tècnica:**
- Taula `site_settings` (clau primària: `key` VARCHAR(100), valor JSON)
- `SettingStore` singleton amb caché (`Cache::remember` 1 hora, invalidada en desar)
- Facade `Setting` i helper global `setting('clau', 'defecte')`
- Middleware `FeatureEnabled` (`feature:nom_del_flag`) retorna 404 si el mòdul és inactiu
- Timezone aplicada dinàmicament a `AppServiceProvider::boot()`

### Mòdul Tresoreria
Hub central de tots els fluxos econòmics (Campus + Associats). Dissenyat per créixer cap a Tickets, Botiga i altres mòduls futurs.

| Recurs | Descripció |
|---|---|
| Inscripcions | Alta d'alumnes a cursos, estat, domiciliació bancària, RGPD |
| Pagaments alumnes | Registre de pagaments per inscripció (transferència, rebut, efectiu…) |
| Liquidacions professors | Ordre de pagament per professor/curs/temporada amb sessions, import brut, retenció i net |
| Quotes socis | Gestió de quotes de socis des de Tresoreria (independent del mòdul Associats) |
| Remeses SEPA socis | Gestió de remeses SEPA de domiciliació de socis des de Tresoreria |
| **Resum financer** | Dashboard de KPIs consolidats: inscripcions, pagaments, liquidacions i quotes. S'adapta als flags actius de Settings |

### Control d'accés
| Rol | Permisos |
|---|---|
| `admin` | Accés total + drag & drop al calendari |
| `manager` | Gestió de cursos, professors i categories |
| `secretaria` | Visualització de tot el catàleg |
| `tresoreria` | Inscripcions, pagaments, liquidacions professors, quotes socis i remeses SEPA |
| `editor` | Gestió d'usuaris i cursos |
| `viewer` | Només lectura |

## Multi-entitat (multi-tenant)

Una mateixa instal·lació pot allotjar diverses institucions ("entitats"),
cadascuna amb el seu propi catàleg, alumnat, professorat, socis i
configuració (marca, colors, mòduls actius) — tot en una sola base de
dades compartida, escopada per `tenant_id`.

| Concepte | Detall |
|---|---|
| URL pública | `/{entitat}/cursos`, `/{entitat}/portal/login`, etc. — el primer segment identifica l'entitat |
| Admin | `/admin/{entitat}/...` — tenancy nativa de Filament, un sol codi/panell |
| Gestió d'entitats | `/admin/{entitat}/tenants` — només super-admin |
| Configuració per entitat | `/admin/{entitat}/settings-page` — marca, colors i mòduls independents per entitat |

**Crear una entitat nova** (`/admin/{entitat}/tenants/create`, només
super-admin): nom + slug, opcionalment un usuari admin (nom, email,
contrasenya) i dades d'exemple (notícies, professorat, cursos, alumnes,
socis) perquè no comenci buida.

**Una persona pot pertànyer a més d'una entitat.** Alumnat, professorat
i socis fan servir un únic compte (email+contrasenya globals); si
intenten accedir a una entitat on encara no tenen accés però en tenen
d'altres, se'ls mostra un selector per triar-ne una. Un super-admin pot
assignar o corregir a quina entitat pertany un usuari de l'admin des
del seu propi formulari.

`users.email` es queda únic globalment a propòsit: el login de l'admin
és previ a saber a quina entitat s'accedeix.

## Instal·lació

```bash
git clone https://github.com/martinartacho/gestio.git
cd gestio

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configura la base de dades i les variables al `.env`:

```env
DB_DATABASE=gestio
DB_USERNAME=root
DB_PASSWORD=

SEEDER_ADMIN_PASSWORD=el_teu_password_admin
SEEDER_USER_PASSWORD=el_teu_password_usuaris
SEEDER_STUDENT_PASSWORD=el_teu_password_alumnes
SEEDER_TEACHER_PASSWORD=el_teu_password_professors

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...   # opcional en dev
```

```bash
php artisan migrate --seed
npm run build
```

Accedeix al panel a `/admin` i al catàleg públic a `/cursos`.

## Usuaris de prova (seeder)

| Email | Rol |
|---|---|
| admin@app.com | admin |
| manager@app.com | manager |
| secretaria@app.com | secretaria |
| tresoreria@app.com | tresoreria |
| editor@app.com | editor |
| viewer@app.com | viewer |

## Dades de prova (CampusSeeder)

El seeder genera dades realistes per a cinc temporades, 8 professors i 35 cursos:

| Temporada | Estat | Descripció |
|---|---|---|
| Tardor 2024 | `closed` | Tancada |
| Primavera 2025 | `closed` | Tancada |
| Tardor 2025 | `closed` | Tancada |
| Primavera 2026 | `active` | Temporada en curs |
| Tardor 2026 | `draft` | En preparació |

## Professors de prova (CampusSeeder)

8 professors amb fitxa completa (dades fiscals, bancàries encriptades, RGPD). Contrasenya definida per `SEEDER_TEACHER_PASSWORD` al `.env`. Accés a `/professorat/login`.

## Alumnes de prova (CampusStudentSeeder)

12 alumnes amb 14 inscripcions variades (paid, pending, cancelled, refunded) en fins a 4 cursos.

Contrasenya definida per `SEEDER_STUDENT_PASSWORD` al `.env`.

## Desenvolupament

```bash
php artisan serve
npm run dev
```

Per testejar webhooks Stripe en local:
```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Per reiniciar la base de dades amb dades de prova:
```bash
php artisan migrate:fresh --seed
```

Per executar els tests:
```bash
php artisan test
```

## Llicència

[MIT License](LICENSE) — projecte obert a contribucions i millores.

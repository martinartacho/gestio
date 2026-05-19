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
| Temporades | Anys acadèmics i quadrimestres |
| Categories | Àrees temàtiques amb color identificador |
| Espais | Aules i ubicacions amb capacitat |
| Franges horàries | Horaris setmanals reutilitzables |
| Professors | Fitxa amb codi, especialitat i estat |
| Cursos | Gestió completa: codi, format, dates, sessions, preu |

### Calendari visual
- Vista mensual de sessions de cursos per temporada
- Clic sobre un event per veure el detall del curs
- Drag & drop per reubicar dates (només admins)
- Títol de l'event: `codi_curs / codi_professor`

### Mòdul Alumnat (portal públic)
| Recurs | Descripció |
|---|---|
| Catàleg públic | `/cursos` — llista de cursos actius i públics de la temporada activa |
| Detall de curs | `/cursos/{slug}` — fitxa completa amb botó d'inscripció |
| Portal alumne | `/portal/meus-cursos` — llista d'inscripcions amb estat |
| Registre i login | `/portal/registre` i `/portal/login` — auth independent (guard `student`) |
| Pagament Stripe | Checkout Session amb confirmació via webhook |
| Llista alumnes | Panel Filament → Cursos → tab Alumnes (admin/manager) |

**Flux de pagament:**
1. Alumne selecciona curs → Stripe Checkout
2. Webhook `checkout.session.completed` → `campus_enrollments.status = paid`
3. Pivot `campus_course_student` s'actualitza amb l'alumne confirmat

### Control d'accés
| Rol | Permisos |
|---|---|
| `admin` | Accés total + drag & drop al calendari |
| `manager` | Gestió de cursos, professors i categories |
| `secretaria` | Visualització de tot el catàleg |
| `editor` | Gestió d'usuaris i cursos |
| `viewer` | Només lectura |

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
| editor@app.com | editor |
| viewer@app.com | viewer |

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

Projecte privat — tots els drets reservats.

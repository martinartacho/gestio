# Gestió Campus

Panel d'administració per a la gestió de cursos, professors, espais i calendari d'un campus de formació continuada.

## Tecnologies

- **Laravel 13** + **PHP 8.3**
- **Filament 4** — panel d'administració
- **Spatie Laravel Permission** — rols i permisos
- **FullCalendar 6** — calendari visual de cursos
- **Vite + Tailwind CSS 4**

## Funcionalitats

### Gestió del campus
| Mòdul | Descripció |
|---|---|
| Temporades | Anys acadèmics i quadrimestres |
| Categories | Àrees temàtiques amb color identificador |
| Espais | Aules i ubicacions amb capacitat |
| Franges horàries | Horaris setmanals reutilitzables |
| Professors | Fitxa completa: dades personals, fiscals, bancàries (encriptades), RGPD |
| Cursos | Gestió completa: codi, format, dates, sessions, preu, professors assignats |

### Calendari visual
- Vista mensual i setmanal de sessions per temporada
- Clic sobre un event per veure el detall del curs
- Drag & drop per reubicar dates (només admins)
- Títol de l'event: `codi_curs / codi_professor`

### Mòdul Tresoreria
| Recurs | Descripció |
|---|---|
| Inscripcions | Alta d'alumnes a cursos, estat, domiciliació bancària, RGPD |
| Pagaments alumnes | Registre de pagaments per inscripció (transferència, rebut, efectiu…) |
| Liquidacions professors | Ordre de pagament per professor/curs/temporada amb sessions, import brut, retenció i net |

### Control d'accés
| Rol | Permisos |
|---|---|
| `admin` | Accés total + drag & drop al calendari |
| `manager` | Gestió de cursos, professors i categories |
| `secretaria` | Visualització de tot el catàleg |
| `tresoreria` | Inscripcions, pagaments, liquidacions i fitxa fiscal de professors |
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

Configura la base de dades i les variables de seeder al `.env`:

```env
DB_DATABASE=gestio
DB_USERNAME=root
DB_PASSWORD=

SEEDER_ADMIN_PASSWORD=el_teu_password_admin
SEEDER_USER_PASSWORD=el_teu_password_usuaris
```

```bash
php artisan migrate --seed
npm run build
```

Accedeix al panel a `/admin`.

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

El seeder genera dades realistes per a tres anys acadèmics relatius a la data d'avui:

- **Any anterior** — cursos tancats, professors amb pagament confirmat
- **Any actual** — quadrimestre de tardor tancat + quadrimestre de primavera actiu amb cursos en curs
- **En preparació** — quadrimestre de tardor de l'any vinent en estat `planning`

Cada quadrimestre inclou 7–9 cursos de categories diverses (salut, educació, tecnologia, arts…) amb professors reals amb dades fiscals i bancàries de prova.

## Desenvolupament

```bash
php artisan serve
npm run dev
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

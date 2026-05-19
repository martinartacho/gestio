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
| Professors | Fitxa amb codi, especialitat i estat |
| Cursos | Gestió completa: codi, format, dates, sessions, preu |

### Calendari visual
- Vista mensual de sessions de cursos per temporada
- Clic sobre un event per veure el detall del curs
- Drag & drop per reubicar dates (només admins)
- Títol de l'event: `codi_curs / codi_professor`

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
| editor@app.com | editor |
| viewer@app.com | viewer |

## Desenvolupament

```bash
php artisan serve
npm run dev
```

Per reiniciar la base de dades amb dades de prova:

```bash
php artisan migrate:fresh --seed
```

## Llicència

Projecte privat — tots els drets reservats.

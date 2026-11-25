# Plataforma Fútbol

Prueba técnica Senior Full Stack.

Consulta de ligas, equipos y jugadores con datos de football-data.org. Hecha con Laravel 13, Vue 3, Vuetify, Passport, Spatie y CASL.

## Requisitos

- PHP 8.5 (`pdo_mysql`, `mbstring`, `bcmath`), Composer 2
- Node.js 20+ y npm
- MySQL 8 o MariaDB 10.6
- Token gratuito de [football-data.org](https://www.football-data.org/client/register)

## Instalación

```bash
git clone <url-del-repositorio>
cd incarail
cp .env.example .env
```

Crea la base `incarail` y completa en `.env` los valores `DB_USERNAME`, `DB_PASSWORD` y `FOOTBALL_DATA_TOKEN`. Luego:

```bash
composer setup
php artisan serve
```

`composer setup` instala dependencias, genera la `APP_KEY` y las llaves de Passport, migra con los seeders y compila la SPA. La aplicación queda en http://localhost:8000.

### Con Docker

```bash
cp .env.example .env
docker compose up -d --build
```

En `.env` basta con completar `FOOTBALL_DATA_TOKEN`. La aplicación queda en http://localhost:8080, servida por nginx, PHP 8.5-fpm y MySQL 8.4. Las llaves, las migraciones y los seeders se ejecutan al arrancar el contenedor.

### Usuarios de prueba

La contraseña de ambos es `password`.

- `admin@demo.test`: todo, incluida la administración de usuarios y roles.
- `consultor@demo.test`: solo competiciones, equipos y jugadores.

### Tests

Los tests usan una base aparte, `incarail_test`, que hay que crear primero:

```bash
php artisan test
npm run type-check
```

football-data se simula con `Http::fake`. El CI de GitHub Actions corre Pint, los tests, el type-check y el build.

## Diagramas

Están en [`docs/`](docs), en Mermaid (`.mmd`) y SVG.

**Arquitectura**

![Arquitectura](docs/arquitectura.svg)

La SPA consume la API con un token de Passport, y cada ruta valida el permiso con Spatie. Solo `FootballDataApi` sale a internet, y antes de cada llamada revisa el contador del `RateLimiter`, que se guarda en MySQL.

**Flujo al abrir una liga**

![Flujo al abrir una liga](docs/flujo-detalle-liga.svg)

Si la liga ya se descargó, se responde desde MySQL. La primera vez, un candado evita que varios usuarios la descarguen a la vez. Sin cupo, se responde 429 y la SPA reintenta sola.

**Modelo de datos**

![Modelo de datos](docs/modelo-datos.svg)

Ligas, equipos y jugadores tienen `external_id` único, y un equipo puede estar en varias ligas.

## Cómo lo resolví

**Un solo proyecto.** La SPA va dentro de Laravel y se compila con Vite. Es más simple de instalar y revisar, y la SPA igual habla con la API solo por HTTP con token.

**Sin hexagonal ni DDD.** Usé las capas normales de Laravel: controladores delgados, Form Requests, Resources y servicios. Para este tamaño, más capas no aportaban. La única interfaz es la de la API externa (`FootballApiInterface`), para que la parte que sale a internet quede aislada.

**Permisos.** Spatie protege la API. `/me` entrega los permisos convertidos a reglas de CASL, y la SPA las usa para mostrar u ocultar menús, botones y rutas.

**Límite de 10 peticiones por minuto.** Lo dejé sin cola ni cron para que el proyecto funcione solo con PHP y MySQL:

- Cuento las llamadas con el `RateLimiter` de Laravel, con un máximo de 9 por minuto para dejar margen. Si no queda cupo, la API no llama a football-data y responde 429 con `Retry-After`.
- Todo lo que ya está guardado se responde desde MySQL, aunque football-data no responda.
- La lista de ligas se descarga en la primera consulta y se refresca cada 24 horas.
- Cada liga se descarga una sola vez, con 1 petición que trae equipos y plantillas.
- Si varios usuarios abren la misma liga a la vez, `Cache::lock` hace que se gaste una sola petición. Lo probé con 5 peticiones simultáneas.
- Ante un 429, la SPA muestra una cuenta regresiva y reintenta sola.

**Sin duplicados.** Guardo con `upsert` y `updateOrCreate` por `external_id`, además del índice único en la base.

## Supuestos

- Las rutas pedidas van con prefijo `/api`, por ejemplo `/api/team/{id}`.
- La tarea no dice cuándo cargar las ligas. Las cargo en la primera consulta para no agregar un paso de instalación.
- La tarea tampoco pide actualizar los datos de una liga. Cada liga se descarga una vez; si se necesitara refrescarla, basta con poner `teams_synced_at` en `null`.
- football-data no envía el número de camiseta en las plantillas; lo verifiqué con datos reales. La columna existe y queda en `null`. Pedirlo jugador por jugador costaría unas 550 peticiones por liga.
- Es una herramienta interna, así que esperar unos segundos ante el límite me parece aceptable.

## Pendiente

- Una cola con un job por liga, para que nadie reciba un 429 y la descarga espere su turno. No la agregué para no exigir un worker extra.
- Refrescar ligas y plantillas con una tarea programada.

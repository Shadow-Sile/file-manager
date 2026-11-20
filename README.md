# File Manager - Laravel Application

Aplicación de gestión de archivos desarrollada en Laravel 13 con pipeline CI/CD completo usando GitHub Actions y despliegue automático en Render.com.

## Características

- **Autenticación completa**: Login, registro, verificación de email, recuperación de contraseña (Laravel Breeze)
- **Gestión de archivos**: Subida, listado, visualización, descarga y eliminación de archivos
- **Control de acceso**: Cada usuario solo ve y gestiona sus propios archivos (Políticas de Laravel)
- **Interfaz responsive**: Diseño con Tailwind CSS, modo oscuro incluido
- **Tipos de archivo soportados**: Imágenes, videos, audio, PDFs, documentos, archivos comprimidos (hasta 50 MB)
- **Tests**: 36 tests Feature que cubren el CRUD, la autorización y el acceso sin sesión

## Stack Tecnológico

- **Backend**: Laravel 13 (PHP 8.3+)
- **Frontend**: Blade + Tailwind CSS + Vite
- **Base de datos**: PostgreSQL (producción, Render) / MySQL (CI) / SQLite (desarrollo)
- **Testing**: PHPUnit
- **Code Style**: Laravel Pint
- **Static Analysis**: PHPStan
- **Containerización**: Docker + Nginx + PHP-FPM + Supervisor
- **CI/CD**: GitHub Actions → GHCR → Render.com

## Requisitos

- PHP 8.3+
- Composer 2+
- Node.js 20+ y npm
- SQLite (desarrollo), MySQL 8.0 (CI) o PostgreSQL (producción)
- Docker y Docker Compose (opcional, para correr la pila completa en local)

## Instalación Local

```bash
# Clonar repositorio
git clone <repository-url>
cd file-manager

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos (SQLite para desarrollo)
touch database/database.sqlite
# Editar .env: DB_CONNECTION=sqlite

# Ejecutar migraciones
php artisan migrate

# Crear enlace de storage
php artisan storage:link

# Compilar assets
npm run build

# Iniciar servidor de desarrollo
php artisan serve
```

## Desarrollo Local (SQLite)

```bash
# 1. Clonar e instalar dependencias
git clone <repository-url>
cd file-manager
composer install
npm install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Base de datos SQLite
touch database/database.sqlite
# Verificar que .env tenga: DB_CONNECTION=sqlite

# 4. Migraciones y storage
php artisan migrate
php artisan storage:link

# 5. Compilar assets y servir
npm run build
php artisan serve
# Abrir http://localhost:8000
```

## Desarrollo con Docker Compose (PostgreSQL)

```bash
# Variables necesarias en .env (además de las de arriba)
# DB_CONNECTION=pgsql, DB_HOST=db, DB_PORT=5432, DB_DATABASE=file_manager,
# DB_USERNAME=file_manager, DB_PASSWORD=...

docker compose up -d

# Verificar
docker compose ps
docker compose logs -f app
curl -f http://localhost/health || echo "FAIL"
```

El entrypoint del contenedor ejecuta automáticamente `storage:link`, `config:cache`, `view:cache` y `migrate --force` (con reintentos) antes de arrancar nginx + PHP-FPM vía supervisord.

## Producción en Render.com

El despliegue está definido en `render.yaml` (Blueprint):

- **Servicio web** `file-manager`: imagen preconstruida desde `ghcr.io/shadow-sile/file-manager:latest` (`runtime: image`), plan gratuito, health check en `/health`.
- **Base de datos** `file-manager-db`: PostgreSQL gratuito. El servicio recibe `DB_URL` automáticamente vía `fromDatabase`.
- Las migraciones se ejecutan en el entrypoint del contenedor (`migrate --force`), porque el plan gratuito de Render no permite `preDeployCommand`.

### Pasos

1. **Crear el servicio en Render**:
   - [New > Blueprint](https://dashboard.render.com/blueprints) y apuntar al repositorio GitHub, o
   - crear manualmente un **Web Service** con *Source Code → Existing Image* apuntando a `ghcr.io/shadow-sile/file-manager:latest` y añadir una **PostgreSQL** con los mismos env vars que `render.yaml`.
2. **Variables de entorno obligatorias** (en el dashboard de Render):
   - `APP_KEY`: generar con `php artisan key:generate --show` y pegarla (`sync: false` en el blueprint).
   - `APP_URL`: dominio público asignado por Render (ej. `https://file-manager-xxxx.onrender.com`).
3. **Secret de GitHub** `RENDER_DEPLOY_HOOK_URL`: URL del deploy hook del servicio (Settings → Deploy → Deploy Hook). Sin él, el job de deploy se salta con un aviso.
4. **Push a `main`** → GitHub Actions construye la imagen, la sube a GHCR y avisa a Render mediante el deploy hook.

> **Nota**: el plan gratuito de Render se suspende tras 15 min de inactividad y no tiene discos persistentes; los archivos subidos viven en el contenedor y se pierden al redeploy. Para uso real, considera un disco persistente o S3.

## Pipeline CI/CD (GitHub Actions)

El workflow `.github/workflows/ci-cd.yml` ejecuta en cada push a `main` y `develop`:

### 1. **Test** (MySQL 8.0 service)
- Instala dependencias con cache
- Migra contra MySQL (`file_manager_test`)
- Corre la suite (PHPUnit usa SQLite en memoria vía `phpunit.xml`)

### 2. **Lint** (Laravel Pint)
- Verifica estilo de código PSR-12 + Laravel

### 3. **Static Analysis** (PHPStan)
- Analiza modelos, controladores y políticas

### 4. **Build Docker** (solo en push a `main`)
- Construye imagen multi-stage (assets → composer → runtime)
- Push a GitHub Container Registry (`ghcr.io/shadow-sile/file-manager:latest`)
- Cache de capas Docker (GitHub Actions cache)

### 5. **Deploy to Render** (solo en push a `main`)
- Envía un POST al deploy hook de Render para redeploy con la imagen nueva

## Secrets Requeridos para Deploy

Configurar en GitHub Settings > Secrets and variables > Actions:

| Secret | Descripción |
|--------|-------------|
| `RENDER_DEPLOY_HOOK_URL` | URL del deploy hook del servicio web en Render |

El resto de credenciales (BD, `APP_KEY`) se gestionan en Render y se inyectan al contenedor; `GITHUB_TOKEN` para GHCR lo aporta GitHub Actions.

## Estructura del Proyecto

```
app/
├── Http/Controllers/
│   ├── Controller.php           # Base con AuthorizesRequests/ValidatesRequests
│   ├── FileController.php       # CRUD de archivos
│   └── Auth/                    # Controladores de autenticación (Breeze)
├── Models/
│   ├── File.php                 # Modelo de archivo con accessors
│   └── User.php                 # Usuario con relación files()
├── Policies/
│   └── FilePolicy.php           # Autorización: usuarios solo ven sus archivos
└── Providers/
    └── AppServiceProvider.php   # Registro de políticas

resources/views/
├── layouts/app.blade.php        # Layout principal (componente x-app-layout)
└── files/
    ├── index.blade.php          # Listado con filtros y paginación
    ├── create.blade.php         # Formulario de subida (drag & drop)
    └── show.blade.php           # Vista previa y detalles

docker/
├── entrypoint.sh                # storage:link, cache, migraciones, supervisord
├── nginx.conf                   # Nginx (fastcgi 127.0.0.1:9000, subidas 64M)
├── supervisord.conf             # Supervisor: PHP-FPM + Nginx
└── php/uploads.ini              # Límites de subida de PHP

.github/workflows/
└── ci-cd.yml                    # Pipeline CI/CD completo

render.yaml                      # Blueprint de Render (web + PostgreSQL)
```

## Comandos Útiles

```bash
# Tests
php artisan test
php artisan test --compact
php artisan test --filter=FileManagementTest

# Code style
./vendor/bin/pint --format agent   # Auto-fix (recomendado)
./vendor/bin/pint --test           # Solo verificar

# Static analysis
./vendor/bin/phpstan analyse --memory-limit=256M

# Base de datos
php artisan migrate:fresh --seed
php artisan migrate:status

# Storage
php artisan storage:link

# Cache (NO usar route:cache: las rutas usan closures)
php artisan config:cache
php artisan view:cache

# Limpiar cache
php artisan optimize:clear
```

## Licencia

MIT License - Ver archivo LICENSE para detalles.

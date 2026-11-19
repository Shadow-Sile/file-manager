# File Manager - Laravel Application

Aplicación de gestión de archivos desarrollada en Laravel 11 con pipeline CI/CD completo usando GitHub Actions.

## Características

- **Autenticación completa**: Login, registro, verificación de email, recuperación de contraseña (Laravel Breeze)
- **Gestión de archivos**: Subida, listado, visualización, descarga y eliminación de archivos
- **Control de acceso**: Cada usuario solo ve y gestiona sus propios archivos (Políticas de Laravel)
- **Interfaz responsive**: Diseño con Tailwind CSS, modo oscuro incluido
- **Tipos de archivo soportados**: Imágenes, videos, audio, PDFs, documentos, archivos comprimidos

## Stack Tecnológico

- **Backend**: Laravel 11 (PHP 8.3)
- **Frontend**: Blade + Tailwind CSS + Vite
- **Base de datos**: MySQL 8.0 / SQLite (desarrollo)
- **Testing**: PHPUnit + Pest
- **Code Style**: Laravel Pint
- **Static Analysis**: PHPStan
- **Containerización**: Docker + Nginx + PHP-FPM + Supervisor
- **CI/CD**: GitHub Actions

## Requisitos

- PHP 8.3+
- Composer 2+
- Node.js 20+ y npm
- MySQL 8.0 (producción) / SQLite (desarrollo)
- Docker y Docker Compose (opcional, para producción)

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

## Variables de Entorno Principales

```env
APP_NAME="File Manager"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=file_manager
# DB_USERNAME=file_manager
# DB_PASSWORD=secret

FILESYSTEM_DISK=public
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Estructura del Proyecto

```
app/
├── Http/Controllers/
│   ├── FileController.php      # CRUD de archivos
│   └── Auth/                   # Controladores de autenticación (Breeze)
├── Models/
│   ├── File.php                # Modelo de archivo con accessors
│   └── User.php                # Usuario con relación files()
├── Policies/
│   └── FilePolicy.php          # Autorización: usuarios solo ven sus archivos
└── Providers/
    └── AppServiceProvider.php  # Registro de políticas

resources/views/
├── layouts/app.blade.php       # Layout principal
└── files/
    ├── index.blade.php         # Listado con filtros y paginación
    ├── create.blade.php        # Formulario de subida (drag & drop)
    └── show.blade.php          # Vista previa y detalles

docker/
├── nginx.conf                  # Configuración Nginx
└── supervisord.conf            # Supervisor para PHP-FPM + Nginx

.github/workflows/
└── ci-cd.yml                   # Pipeline CI/CD completo
```

## Pipeline CI/CD (GitHub Actions)

El workflow `.github/workflows/ci-cd.yml` ejecuta en cada push a `main` y `develop`:

### 1. **Test** (MySQL 8.0 service)
- Instala dependencias con cache
- Configura base de datos de prueba
- Ejecuta migraciones
- Corre suite de tests con coverage

### 2. **Lint** (Laravel Pint)
- Verifica estilo de código PSR-12 + Laravel

### 3. **Static Analysis** (PHPStan nivel 5)
- Analiza modelos, políticas y providers

### 4. **Build Docker** (solo en push a main)
- Construye imagen multi-stage
- Push a GitHub Container Registry (ghcr.io)
- Cache de capas Docker

### 5. **Deploy** (solo en push a main, requiere secrets)
- SSH al servidor de producción
- Pull de imagen latest
- `docker compose down && up -d`
- Limpieza de imágenes antiguas

## Secrets Requeridos para Deploy

Configurar en GitHub Settings > Secrets > Actions:

| Secret | Descripción |
|--------|-------------|
| `SSH_HOST` | IP o hostname del servidor |
| `SSH_USER` | Usuario SSH (ej. root, ubuntu) |
| `SSH_PRIVATE_KEY` | Clave privada SSH (sin passphrase) |
| `SSH_PORT` | Puerto SSH (default: 22) |

Variables de entorno en el servidor (`/opt/file-manager/.env`):
```env
DB_ROOT_PASSWORD=secure_root_password
DB_DATABASE=file_manager
DB_USERNAME=file_manager
DB_PASSWORD=secure_password
APP_URL=https://tu-dominio.com
```

## Despliegue con Docker Compose

```bash
# En el servidor
mkdir -p /opt/file-manager
cd /opt/file-manager

# Copiar docker-compose.yml y .env
# Editar .env con valores de producción

# Levantar servicios
docker compose up -d

# Ver logs
docker compose logs -f

# Ver estado
docker compose ps
```

## Comandos Útiles

```bash
# Tests
php artisan test
php artisan test --coverage

# Code style
./vendor/bin/pint          # Auto-fix
./vendor/bin/pint --test   # Solo verificar

# Static analysis
./vendor/bin/phpstan analyse --memory-limit=256M

# Base de datos
php artisan migrate:fresh --seed
php artisan migrate:status

# Storage
php artisan storage:link

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar cache
php artisan optimize:clear
```

## Licencia

MIT License - Ver archivo LICENSE para detalles.
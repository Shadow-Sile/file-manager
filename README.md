# File Manager · AEA2 · Laravel y Wasmer

Gestor de archivos propio con Laravel 13, Blade, Tailwind, registro e inicio de sesión. Cada usuario puede subir archivos de hasta 50 MB, buscar, filtrar por tipo, consultar detalles, descargar y eliminar sus archivos. Las descargas requieren sesión y autorización. No se ejecutan ni se publican los archivos subidos.

## Arranque local

Requisitos: PHP 8.5, Composer y Node 24 (el lock actual de Composer requiere al menos PHP 8.4.1).

```bash
composer install
```

Si todavía no existe `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Conserva la clave y el archivo `.env` si ya existen. Para una instalación nueva con SQLite:

```bash
touch database/database.sqlite
php artisan migrate --no-interaction
npm ci
npm run build
php artisan serve
```

Regístrate en la web. No hay contraseñas de demostración ni verificación de correo obligatoria. La recuperación de contraseña usa logs hasta configurar un servidor SMTP.

## Producción en Wasmer

El despliegue usa un paquete WebAssembly/WASIX, sin Docker como intermediario. `wasmer.toml` fija `shira/php` 8.5.10 porque el runtime oficial `php/php` sigue en la serie 8.3 y no es compatible con el lock de dependencias actual. Es un runtime comunitario; CI comprueba que Laravel arranca dentro de él antes de permitir el despliegue.

`app.yaml` configura PostgreSQL gestionado, sesiones y caché en base de datos y un volumen persistente `uploaded-files` montado en `/data`. Los archivos se guardan en `/data/files`, fuera de `public`. No cambies el nombre del volumen ni lo elimines: Wasmer asocia sus datos a ese nombre. La base de datos recibe credenciales inyectadas por Wasmer; `config/database.php` admite su variable `DB_NAME`. Las migraciones se ejecutan mediante el job de predespliegue `migrate-database`.

### Primera configuración

1. Instala Wasmer desde <https://docs.wasmer.io/install/> e inicia sesión con `wasmer login`.
2. Crea una app en tu cuenta de Wasmer. Anota el propietario, el nombre real y la URL HTTPS. Usa esos mismos valores en todos los despliegues para conservar la base de datos y el volumen.
3. En los secretos de la app configura `APP_KEY` con una clave estable generada mediante `php artisan key:generate --show`. Configura `APP_URL` con su URL HTTPS real. No compartas la clave ni la añadas al repositorio.
4. En GitHub → Settings → Secrets and variables → Actions añade:

| Tipo | Nombre | Valor |
| --- | --- | --- |
| Secret | `WASMER_TOKEN` | Token de tu cuenta con acceso a la app |
| Variable | `WASMER_OWNER` | Tu usuario o namespace de Wasmer |
| Variable | `WASMER_APP_NAME` | Nombre exacto de la app |
| Variable | `WASMER_APP_URL` | URL HTTPS real de la app |

5. Envía los cambios a `main` o ejecuta manualmente el workflow en `main`. Si usas un environment `production` con aprobación en GitHub, aprueba ese job.
6. Comprueba la web pública: registro, subida, listado, detalles, descarga y borrado. Repite una descarga después de un nuevo despliegue para demostrar persistencia.

La base de datos y los volúmenes dependen de las condiciones y recursos disponibles de tu cuenta de Wasmer. No se ha contratado ningún recurso ni se ha verificado todavía el despliegue público.

Para preparar el paquete manualmente usa un checkout limpio y dependencias de producción:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
sh docker/prepare-wasmer.sh
wasmer run . --net
```

Para desplegar ese paquete:

```bash
wasmer deploy --owner TU_USUARIO --name TU_APP
```

El script de empaquetado incluye exclusivamente código, migraciones, assets y dependencias. No incluye `.env`, base de datos local, archivos subidos ni cachés locales. No publiques el directorio completo mediante un mapeo `"/app" = "."`.

## GitHub Actions: CI/CD

1. **Checks:** instala PHP 8.5 y Node 24, compila los assets, comprueba migraciones en PostgreSQL y ejecuta PHPUnit, Pint y PHPStan.
2. **Package:** instala dependencias sin desarrollo, crea el paquete y arranca Laravel en Wasmer. Comprueba `/up`, `/login`, `/register` y que `/.env` devuelve 404. Guarda el paquete validado como artefacto.
3. **Deploy:** solo en `main`, descarga ese mismo artefacto y ejecuta `wasmer deploy`. Si faltan secretos o variables, falla explícitamente. Comprueba las rutas públicas después del despliegue.

Las pull requests verifican el código y el paquete sin desplegar. `develop` también ejecuta los controles. Los despliegues se serializan para evitar cambios simultáneos. No hay hooks ni configuración de Render.

## Docker opcional para desarrollo

Se conservan Dockerfile y Compose como alternativa local. Define `POSTGRES_PASSWORD` en `.env` y ejecuta `docker compose up -d --build`. Usa el puerto 8080. Los volúmenes `app_storage` y `db_data` conservan los datos; no uses `down -v` si quieres conservarlos. Esta alternativa no se usa para desplegar en Wasmer.

## Archivos de instalaciones anteriores

Si tenías subidas en `storage/app/public/uploads`, copia `uploads` a `storage/app/private/uploads` conservando la estructura y verifica las descargas antes de quitar la copia pública. Una nueva app en Wasmer no importa automáticamente tu SQLite ni tus archivos locales.

## Verificación

```bash
php artisan test --compact
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=256M
npm run build
```

En un entorno que bloquee sockets locales, PHPStan se puede ejecutar con `--debug` para desactivar sus procesos paralelos.

## Demostración para el vídeo

1. Explica qué resuelve la aplicación y demuestra registro, subida, búsqueda, filtro, detalles, descarga y eliminación.
2. Accede con una segunda cuenta y demuestra el aislamiento entre usuarios.
3. Enseña el controlador, la migración, el modelo y la política de autorización.
4. Explica CI con las pruebas, el estilo, el análisis y la compilación del frontend.
5. Enseña `wasmer.toml`, `app.yaml`, el volumen y el job de migraciones, sin mostrar secretos.
6. Haz un cambio visible, súbelo a `main`, muestra los jobs de Actions y el resultado en la URL pública de Wasmer.
7. Descarga un archivo subido antes del despliegue para demostrar que se conserva.

La entrega requiere demostrar el despliegue real funcionando. Tener estos archivos preparados todavía no acredita esa parte del ejercicio.

Referencias: [Wasmer y Laravel](https://docs.wasmer.io/edge/guides/laravel/), [configuración de apps](https://docs.wasmer.io/edge/configuration/), [jobs de despliegue](https://docs.wasmer.io/edge/configuration/jobs/) y [runtime PHP 8.5](https://wasmer.io/shira/php).

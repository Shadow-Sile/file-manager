# File Manager · AEA2

Proyecto propio de gestión de archivos en Laravel 13, Blade, Tailwind, PostgreSQL y Docker. Incluye registro, inicio de sesión, subida de hasta 50 MB, búsqueda, filtro por tipo, paginación, detalles, descarga y eliminación. Cada usuario gestiona únicamente sus archivos, almacenados fuera de la carpeta pública.

## Estado comprobado

40 tests pasan, assets compilados, migraciones SQLite ejecutadas y PHPStan sin errores. Proyecto publicado en GitHub mediante GitHub Desktop. La construcción de la imagen se verifica en GitHub Actions. El despliegue en Render está bloqueado porque la cuenta está suspendida por una factura pendiente. Docker Desktop local tampoco puede arrancar: su instalación no contiene el ejecutable principal.

## Arranque local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm ci
npm run build
php artisan serve
```

Si ya existe `.env`, consérvalo. Regístrate desde la aplicación; no se incluye una contraseña de demostración. La recuperación de contraseña usa el mailer de logs por defecto: para enviar correo real hay que configurar SMTP.

## Docker con almacenamiento persistente

1. Arranca Docker Desktop.
2. Instala dependencias y genera `APP_KEY` como arriba.
3. Define una contraseña aleatoria en `POSTGRES_PASSWORD` dentro de `.env`.
4. Ejecuta:

```bash
docker compose up -d --build
docker compose ps
docker compose logs --tail=100 app
```

Abre el puerto local 8080. Los volúmenes `app_storage` y `db_data` conservan archivos y base de datos al recrear los contenedores. No ejecutes `docker compose down -v` si quieres conservarlos. Haz copias de ambos volúmenes y de `APP_KEY` para un uso real.

El entrypoint prepara permisos y cachés, ejecuta migraciones con reintentos y arranca Nginx y PHP-FPM mediante Supervisor.

### Archivos de una instalación anterior

Las subidas nuevas usan `storage/app/private`. Si tenías archivos en `storage/app/public/uploads`, copia el directorio `uploads` a `storage/app/private/uploads`, conservando su estructura, antes de usar esta versión. Después de verificar las descargas, elimina la copia pública para que no siga accesible. No se ha modificado tu almacenamiento original.

## CI/CD con GitHub Actions y Render

El workflow `.github/workflows/ci-cd.yml` ejecuta:

1. Tests PHPUnit en SQLite y comprobación de migraciones en MySQL.
2. Estilo con Laravel Pint y análisis con PHPStan.
3. Tras superar los controles en `main`, construye una imagen Linux amd64 y la publica en GHCR con las etiquetas `latest` y SHA.
4. Llama al deploy hook de Render. Si falta el secreto, falla explícitamente. Un hook aceptado indica que se ha solicitado el despliegue; hay que comprobar en Render que termina y probar la aplicación.

No publica imágenes ni despliega desde pull requests. También permite ejecución manual desde Actions en `main`.

### Configuración pendiente en las cuentas

- Comprobar la ejecución de Actions en `Shadow-Sile/file-manager`.
- Resolver la suspensión por facturación de la cuenta de Render antes de crear el servicio.
- Dar visibilidad pública al paquete GHCR si Render lo consume sin credenciales.
- Crear el Blueprint de `render.yaml`, o conectar un servicio de imagen a `ghcr.io/shadow-sile/file-manager:latest`.
- En Render definir una `APP_KEY` estable y `APP_URL` con la URL HTTPS real. La conexión PostgreSQL se obtiene del Blueprint.
- En GitHub, Settings → Secrets and variables → Actions, añadir `RENDER_DEPLOY_HOOK_URL` con el deploy hook del servicio.
- Probar registro, subida y descarga desde la URL pública; repetir después de un cambio enviado a `main`.

**Persistencia en Render:** el Blueprint incluido guarda los archivos en el disco del contenedor. No debes tratarlo como almacenamiento duradero: para conservar subidas entre despliegues necesitas un disco persistente montado en `/var/www/html/storage/app` o almacenamiento externo. Comprueba la disponibilidad y el coste en tu cuenta antes de contratar recursos. Compose sí incluye volúmenes persistentes. La alternativa VPS requiere configurar acceso SSH y adaptar el paso de despliegue: ese despliegue no está implementado aquí.

## Comprobaciones

```bash
php artisan test --compact
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=256M
npm run build
```

## Guion para el vídeo

1. Explica el problema: guardar y gestionar archivos por usuario.
2. Demuestra registro, subida, búsqueda, detalles, descarga y borrado.
3. Con una segunda cuenta demuestra que no aparecen los archivos de la primera.
4. Enseña controlador, modelo, migración y política; explica el almacenamiento privado.
5. Enseña las etapas del Dockerfile y los volúmenes de Compose.
6. Explica CI (tests, estilo y análisis) y CD (imagen GHCR y hook).
7. Haz un cambio visible, súbelo a `main`, muestra los jobs en Actions, el despliegue en Render y el cambio en la web pública.
8. Explica los secretos sin mostrar sus valores y la limitación de persistencia del hosting elegido.

La entrega requiere una web pública funcionando y demostrar el despliegue real. Tener el workflow preparado por sí solo no completa ese requisito.

#!/bin/sh
set -e

cd /var/www/html
: "${APP_KEY:?APP_KEY debe estar configurada y conservarse entre despliegues}"
mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Cache de configuración y vista
php artisan config:cache
php artisan view:cache

# Migraciones con reintentos
if [ -n "$DB_CONNECTION" ]; then
    echo "Ejecutando migraciones..."
    max_attempts=30
    attempt=1
    while [ "$attempt" -le "$max_attempts" ]; do
        if php artisan migrate --force; then
            echo "Migraciones completadas exitosamente"
            break
        fi
        echo "Intento $attempt de migración fallido. Esperando 2 segundos..."
        sleep 2
        attempt=$((attempt + 1))
    done

    if [ "$attempt" -gt "$max_attempts" ]; then
        echo "Error: no se pudieron completar las migraciones después de $max_attempts intentos"
        exit 1
    fi
else
    echo "DB_CONNECTION no definida, omitiendo migraciones"
fi

# Arrancar supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

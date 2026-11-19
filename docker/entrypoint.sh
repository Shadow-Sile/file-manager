#!/bin/bash
set -e

# Crear enlace de almacenamiento
php artisan storage:link

# Cache de configuración y vista
php artisan config:cache
php artisan view:cache

# Migraciones con reintentos
if [[ -n "$DB_CONNECTION" ]]; then
    echo "Ejecutando migraciones..."
    max_attempts=30
    attempt=1
    while [ $attempt -le $max_attempts ]; do
        if php artisan migrate --force; then
            echo "Migraciones completadas exitosamente"
            break
        else
            echo "Intento $attempt de migración fallido. Esperando 2 segundos..."
            sleep 2
            ((attempt++))
        fi
    done
    
    if [ $attempt -gt $max_attempts ]; then
        echo "Error: No se pudieron completar las migraciones después de $max_attempts intentos"
        exit 1
    fi
else
    echo "DB_CONNECTION no definida, omitiendo migraciones"
fi

# Arrancar supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
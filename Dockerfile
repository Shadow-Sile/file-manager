# Etapa de construcción de assets (Node.js)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Etapa de construcción de Composer
FROM php:8.5-cli-alpine AS composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip libxml2 \
    && apk add --no-cache --virtual .build-deps libxml2-dev \
    && docker-php-ext-install dom \
    && apk del .build-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
COPY . .
# Descartar manifest locales de paquetes (pueden referenciar dependencias dev)
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev

# Etapa de runtime
FROM php:8.5-fpm-alpine

# Libs de runtime (explícitas: se conservan tras purgar los -dev) + headers de build
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    libpq \
    libxml2 \
    zlib \
    && apk add --no-cache --virtual .build-deps \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) dom gd pdo_mysql pdo_pgsql zip bcmath \
    && apk del .build-deps

WORKDIR /var/www/html

# Crear directorios necesarios
RUN mkdir -p /var/log/supervisor \
    /var/log/nginx \
    /var/run/nginx \
    /var/www/html

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copiar archivos de la aplicación desde las etapas anteriores
COPY --from=assets /app/public/build public/build
COPY --from=composer /app/vendor vendor
COPY . .

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponer puerto
EXPOSE 80

# Establecer punto de entrada
ENTRYPOINT ["/entrypoint.sh"]
# ---- Etapa 1: compilar assets de Vue/Inertia con Node ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run build

# ---- Etapa 2: imagen final con PHP ----
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && php artisan config:clear

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["/entrypoint.sh"]
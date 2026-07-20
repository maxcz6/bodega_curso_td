FROM php:8.4-fpm

# Instalamos dependencias básicas + herramientas de compilación para PECL (autoconf, g++, make)
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libzip-dev default-mysql-client \
    autoconf g++ make \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    # --- INSTALACIÓN DE XDEBUG ---
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    # Limpieza para reducir el tamaño de la imagen
    && apt-get remove -y autoconf g++ make \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-interaction --prefer-dist --no-progress

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
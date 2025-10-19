FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www/html

# Copy composer dan install
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev

# Copy .env jika perlu (atau set env di Railway dashboard)
# COPY .env.example .env

# Expose port (sesuaikan dengan port yang digunakan)
EXPOSE 8000

# Command untuk start Laravel (misal pakai built-in server)
CMD php artisan serve --host=0.0.0.0 --port=8000
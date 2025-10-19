FROM php:8.3-fpm

# Install system dependencies
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
    nodejs \
    npm

# Install PHP extensions (semua dalam satu perintah)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Buat file SQLite kosong (jika pakai sqlite)
RUN mkdir -p database && touch database/database.sqlite

# Set permission
RUN mkdir -p storage/framework/sessions \
    && chmod -R 775 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Jalankan migrasi & seeder (jika memang ingin fresh setiap build)
RUN php artisan migrate:fresh --seed --force

# Install Node.js dependencies and build frontend assets
RUN npm install && npm run build

# Expose port
EXPOSE 8000

# Start Laravel with built-in server
CMD php artisan serve --host=0.0.0.0 --port=8000
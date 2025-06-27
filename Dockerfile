# Gunakan versi PHP yang spesifik
FROM dunglas/frankenphp

# Pindah ke user root untuk semua setup sistem
USER root

# Install dependensi sistem, ekstensi PHP, DAN COMPOSER
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    # Install Composer secara global
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    # Bersihkan cache
    && rm -rf /var/lib/apt/lists/*

# --- SEKARANG, SEMUA SETUP SISTEM SELESAI ---
# --- KITA BERALIH KE USER APLIKASI ---

# Kembali ke user non-root untuk keamanan
USER frankenphp

WORKDIR /app

# Salin hanya file dependensi untuk caching
COPY --chown=frankenphp:frankenphp composer.json composer.lock ./

# Install dependensi Laravel sebagai user 'frankenphp'
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Salin sisa kode aplikasi
COPY --chown=frankenphp:frankenphp . .

# Atur izin yang benar untuk direktori Laravel
# chown tidak lagi diperlukan karena kita menggunakan COPY --chown
RUN chmod -R 775 storage bootstrap/cache

# Konfigurasi FrankenPHP untuk menjalankan worker Laravel
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

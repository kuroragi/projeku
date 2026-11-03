FROM php:8.5-apache

# Ambil ekstensi installer resmi mlocati
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Pasang dependency dasar dan tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Pasang seluruh ekstensi yang dibutuhkan (otomatis compile, configure, & pasang header)
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache \
    redis

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Ambil Composer versi resmi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Pasang Node.js LTS (v20/v22) dan NPM
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

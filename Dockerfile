FROM dunglas/frankenphp:1.1.1

# Installe les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /app

# Copier uniquement les fichiers nécessaires à l'installation des dépendances
COPY composer.json composer.lock ./

# Installer les dépendances (vendor sera dans l'image ou écrasé par le volume ? → on va gérer ça)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copier la config de FrankenPHP
COPY docker/frankenphp/conf.frankenphp.yaml /etc/frankenphp.yaml

# Par défaut, on part en prod, mais APP_ENV sera écrasé par docker-compose si besoin
ENV APP_ENV=prod
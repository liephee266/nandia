# Dockerfile
FROM dunglas/frankenphp:1.1.1

# Installe les dépendances système nécessaires à Symfony
RUN apt-get update && apt-get install -y git unzip libicu-dev libpq-dev libzip-dev zlib1g-dev

# Active les extensions PHP requises
# Note : FrankenPHP est basé sur PHP, donc on peut utiliser docker-php-ext-*
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip
    
# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie les fichiers de l'application
WORKDIR /app
COPY . .

# Installe les dépendances PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Autorise Symfony à fonctionner en production
ENV APP_ENV=prod

# Configure FrankenPHP pour servir Symfony depuis /public
COPY docker/frankenphp/conf.frankenphp.yaml /etc/frankenphp.yaml

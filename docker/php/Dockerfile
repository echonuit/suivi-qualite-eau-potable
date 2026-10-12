# Image de développement : PHP 8.4 avec Apache, PDO PostgreSQL et Composer.
FROM php:8.4-apache

# pdo_pgsql n'est pas dans l'image officielle. curl et mbstring, eux, y sont déjà.
# unzip sert à Composer pour décompresser les paquets, et à vous pour ouvrir
# les archives de data.gouv.fr et du catalogue E-Phy.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Réglages PHP de développement : les erreurs s'affichent dans la page.
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# Apache ne sert que public/ : le reste du dépôt n'est pas accessible par le web.
COPY docker/php/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

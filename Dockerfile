FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev pkg-config unzip git \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Le projet est a la racine du conteneur web
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data /var/www/html/uploads || true

EXPOSE 80

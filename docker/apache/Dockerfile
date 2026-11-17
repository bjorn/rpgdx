FROM php:8.3-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libonig-dev libxml2-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd mysqli pdo_mysql zip mbstring dom \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' '<Directory /var/www/html>' '  AllowOverride All' '</Directory>' '<Directory /var/www/html/legacy-forums>' '  AllowOverride None' '  Require all denied' '</Directory>' > /etc/apache2/conf-available/rpgdx.conf \
  && a2enconf rpgdx

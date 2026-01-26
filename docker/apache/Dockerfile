FROM php:8.4-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd mysqli pdo_mysql zip \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

# Allow .htaccess overrides for phpBB and the main site.
RUN printf "%s\n" \
    "<Directory /var/www/html>" \
    "  AllowOverride All" \
    "</Directory>" \
  > /etc/apache2/conf-available/rpgdx.conf \
  && a2enconf rpgdx

# PHP 8.x shims for old mysql_* and ereg_* calls used by phpBB 2.x.
RUN mkdir -p /opt/git \
  && git clone https://github.com/Hierosoft/php-ereg-shim-procedural.git /opt/git/php-ereg-shim-procedural \
  && git clone https://gitlab.com/poikilos/mysql-shim.git /opt/git/mysql-shim \
  && (cd /opt/git/mysql-shim && git switch php8 || true) \
  && git clone https://github.com/ChristiaanBye/polyfill-each.git /opt/git/polyfill-each \
  && printf "%s\n" \
    "<?php" \
    "require '/opt/git/mysql-shim/mysql-shim.php';" \
    "require '/opt/git/php-ereg-shim-procedural/php-ereg-shim-procedural.php';" \
    "require '/opt/git/polyfill-each/polyfill-each.php';" \
    "?>" \
    > /opt/git/php8-shims.php \
  && echo "auto_prepend_file=/opt/git/php8-shims.php" > /usr/local/etc/php/conf.d/99-php8-shims.ini

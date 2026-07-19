FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli \
    && a2enmod rewrite headers expires \
    && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY composer.json composer.lock /tmp/build/
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cd /tmp/build && composer install --no-dev --no-interaction --optimize-autoloader \
    && mkdir -p /var/www/vendor && cp -r /tmp/build/vendor/* /var/www/vendor/ \
    && rm -rf /tmp/build

COPY jaxpay/ /var/www/html/
RUN chmod -R 755 /var/www/html/assets \
    && chown -R www-data:www-data /var/www/html/assets/uploads

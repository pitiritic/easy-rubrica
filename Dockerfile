FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html/

# Redirigir la raíz de Apache a la subcarpeta easyrubrica
RUN sed -ri -e 's!/var/www/html!/var/www/html/easyrubrica!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!/var/www/html/easyrubrica!g' /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

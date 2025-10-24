FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_sqlite intl

RUN a2enmod rewrite

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/logos && \
    chown -R www-data:www-data /var/www/html/database /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
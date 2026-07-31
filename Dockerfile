FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update \
    && apt-get install -y unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html

COPY . .
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
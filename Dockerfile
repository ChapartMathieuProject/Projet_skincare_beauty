FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update \
    && apt-get install -y unzip cron \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY docker/cron/loyalty-cron /etc/cron.d/loyalty-cron
COPY docker/cron/entrypoint-cron.sh /usr/local/bin/entrypoint-cron.sh
RUN chmod 0644 /etc/cron.d/loyalty-cron \
    && crontab /etc/cron.d/loyalty-cron \
    && chmod +x /usr/local/bin/entrypoint-cron.sh \
    && touch /var/log/cron_loyalty.log

WORKDIR /var/www/html

COPY . .
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
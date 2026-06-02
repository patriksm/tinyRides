FROM php:8.2-apache

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Apache rewrite
RUN a2enmod rewrite

# App files
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
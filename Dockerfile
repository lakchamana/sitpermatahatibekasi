FROM php:8.2-apache

# Install extension PHP yang dibutuhkan project
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

# Copy semua source code ke Apache
COPY . /var/www/html/

# Permission folder
RUN chown -R www-data:www-data /var/www/html

# Port Apache
EXPOSE 80
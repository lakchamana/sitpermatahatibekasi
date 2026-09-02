FROM php:8.2-apache


# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli


# Pastikan hanya satu MPM Apache aktif
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork


# Aktifkan rewrite untuk .htaccess
RUN a2enmod rewrite


# Copy project
COPY . /var/www/html/


# Permission
RUN chown -R www-data:www-data /var/www/html


EXPOSE 80
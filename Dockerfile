FROM php:8.2-apache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your code into the default Apache web root
COPY . /var/www/html/

# Enable mod_rewrite for .htaccess
RUN a2enmod rewrite

# Install PHP extensions (e.g., pdo_mysql if needed)
RUN docker-php-ext-install pdo_mysql

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install Composer dependencies (if composer.json exists)
RUN if [ -f /var/www/html/composer.json ]; then \
        cd /var/www/html && composer install --no-interaction --optimize-autoloader; \
    fi

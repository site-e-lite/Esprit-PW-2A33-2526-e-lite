FROM php:8.2-cli
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . /app
RUN if [ -f /app/composer.json ]; then composer install --no-interaction --optimize-autoloader; fi
EXPOSE 10000
CMD php -S 0.0.0.0:$PORT -t /app

FROM php:8.2-cli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application code
COPY . /app

# Install dependencies if composer.json exists
RUN if [ -f /app/composer.json ]; then composer install --no-interaction --optimize-autoloader; fi

# Expose the port Render expects
EXPOSE 10000

# Start the PHP built-in server on Render's port
CMD php -S 0.0.0.0:$PORT -t /app

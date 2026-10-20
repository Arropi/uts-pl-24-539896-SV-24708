FROM dunglas/frankenphp:1.3-php8.3-alpine

# Install persistent dependencies and PHP extensions required by Laravel
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    pcntl \
    bcmath

# Copy Composer binary from official composer image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Layer Caching: copy composer files first
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts --no-progress --prefer-dist

# Copy the rest of the application code
COPY . .

# Run dump-autoload
RUN composer dump-autoload --optimize

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD ["frankenphp", "php-server", "--listen", ":8000", "--root", "public/"]

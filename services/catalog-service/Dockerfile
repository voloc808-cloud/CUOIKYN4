FROM php:8.2-cli
RUN apt-get update && apt-get install -y curl gnupg2 libzip-dev unixodbc-dev \
    && curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && pecl install sqlsrv pdo_sqlsrv && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install bcmath zip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/app
COPY packages/pims-common /var/packages/pims-common
COPY services/catalog-service/composer.json services/catalog-service/composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
COPY services/catalog-service .
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

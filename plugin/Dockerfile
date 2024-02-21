FROM php:8.2-cli as php-builder
WORKDIR /usr/src/app

# Copy Composer files first to take advantage of Docker layer caching
COPY plugin/composer.* ./

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install git, zip extension, and other necessary PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd zip && \
    apt-get update && \
    apt-get install -y git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    # Allow Composer to run as super user
    export COMPOSER_ALLOW_SUPERUSER=1 && \
    composer install --no-dev --optimize-autoloader

FROM node:18 as node-builder
WORKDIR /usr/src/app

# Install dependencies
COPY react-bracket-builder/package*.json .
RUN npm install
COPY react-bracket-builder .
# Build the app for standalone and wordpress environments
RUN npm run build:local

FROM alpine:latest as final
WORKDIR /usr/src/app

COPY plugin .
COPY --from=php-builder /usr/src/app/vendor ./vendor
COPY --from=node-builder /usr/src/app/build/wordpress ./Includes/react-bracket-builder/build/wordpress
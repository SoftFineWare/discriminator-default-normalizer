FROM php:8.1-cli-alpine

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN  --mount=type=bind,from=mlocati/php-extension-installer:1.5,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
      install-php-extensions zip xdebug-stable
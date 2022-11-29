FROM php:8.1-cli-alpine

RUN  --mount=type=bind,from=mlocati/php-extension-installer:1.5,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
      install-php-extensions zip xdebug-stable

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# TODO finish Pull Request here https://github.com/vimeo/psalm/pull/7701#pullrequestreview-1026024601 and remove add and chmod
ADD https://github.com/psalm/phar/raw/33723713902e1345904a5c9064ef7848bee0d490/psalm.phar /usr/local/bin/psalm
RUN chmod 555 /usr/local/bin/psalm
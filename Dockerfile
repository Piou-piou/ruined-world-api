ARG PHP_EXTENSIONS="pdo_mysql intl zip"
FROM thecodingmachine/php:7.3-v2-slim-apache
ENV TEMPLATE_PHP_INI=production
ENV APP_ENV=prod
ENV APACHE_DOCUMENT_ROOT=public/
COPY --chown=docker:docker ./ /var/www/html
WORKDIR /var/www/html
RUN composer install
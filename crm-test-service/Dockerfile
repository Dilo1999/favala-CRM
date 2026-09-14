FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

EXPOSE 8090

CMD ["php", "-S", "0.0.0.0:8090", "-t", "public"]

FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update && apt-get install -y libcurl4-openssl-dev && docker-php-ext-install curl

WORKDIR /app
COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "."]

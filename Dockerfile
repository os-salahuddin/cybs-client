# Use the official PHP image from the Docker Hub
FROM php:8.1-apache

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

EXPOSE 80
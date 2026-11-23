# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilitar mod_rewrite y extensiones necesarias (cURL para la API de pagos)
RUN a2enmod rewrite \
    && docker-php-ext-install opcache

# Copiar todos los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Ajustar permisos para Apache (escritura de transacciones_pse.json)
RUN chown -R www-data:www-data /var/www/html \
    && chmod 666 /var/www/html/transacciones_pse.json 2>/dev/null || true

# Puerto del contenedor
EXPOSE 80

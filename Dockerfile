FROM php:8.2-apache

# Instalar extensiones de PHP necesarias para MySQL (mysqli y pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del proyecto (esto se sobreescribirá en desarrollo por los volúmenes, pero es útil para producción)
COPY ./lachina2 /var/www/html/

# Dar permisos adecuados al usuario de Apache (www-data)
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80 para el tráfico HTTP
EXPOSE 80

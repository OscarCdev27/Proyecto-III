#!/bin/bash
# Script de inicialización de Base de Datos para el contenedor MySQL

set -e

echo "Iniciando la configuración de base de datos..."

# Crear las bases de datos si no existen
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS tucasa_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS sainvc30_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS lachina2_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Bases de datos creadas exitosamente."

# Crear el usuario específico y asignarle permisos en las 3 bases de datos
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS 'root_mundo'@'%' IDENTIFIED BY 'D2wZP8WhX_PyNAKc*';"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON tucasa_bd.* TO 'root_mundo'@'%';"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON sainvc30_bd.* TO 'root_mundo'@'%';"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON lachina2_bd.* TO 'root_mundo'@'%';"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"

echo "Usuario 'root_mundo' configurado correctamente."

# Importar los archivos SQL de respaldo
echo "Importando deportes_db.sql en tucasa_bd..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" tucasa_bd < /sql-dumps/deportes_db.sql

echo "Importando sainvc_bd.sql en sainvc30_bd..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" sainvc30_bd < /sql-dumps/sainvc_bd.sql

echo "Importando lachina2_bd.sql en lachina2_bd..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" lachina2_bd < /sql-dumps/lachina2_bd.sql

echo "Importación de bases de datos finalizada exitosamente."

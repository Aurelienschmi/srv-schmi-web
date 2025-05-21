#!/bin/bash

# Charger les variables depuis le .env si présent
if [ -f ../.env ]; then
    source ../.env
elif [ -f ../../.env ]; then
    source ../../.env
fi

DATABASE_NAME="${DB_NAME:-stage}"
USERNAME="${DB_USER:-root}"
PASSWORD="${DB_PASS:-welcome1}"
HOST="${DB_HOST:-127.0.0.1}"

echo "Suppression de la base $DATABASE_NAME si elle existe..."
mysql --host=$HOST --user=$USERNAME --password=$PASSWORD --default-character-set=utf8mb4 -e "DROP DATABASE IF EXISTS \`$DATABASE_NAME\`;"

echo "Création de la base $DATABASE_NAME..."
mysql --host=$HOST --user=$USERNAME --password=$PASSWORD --default-character-set=utf8mb4 -e "CREATE DATABASE \`$DATABASE_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Import des migrations..."
for file in ../migration/*.sql
do
    echo "Import de $file"
    mysql --host=$HOST --user=$USERNAME --password=$PASSWORD --default-character-set=utf8mb4 $DATABASE_NAME < "$file"
done

echo "Base de données prête."
#!/bin/bash

# Charger les variables depuis le .env si présent
echo "PWD: $(pwd)"
ls -l ../.env
if [ -f ../.env ]; then
    set -a
    . ../.env
    set +a
    echo "Chargement des variables d'environnement depuis ../.env"
elif [ -f ../../.env ]; then
    set -a
    . ../../.env
    set +a
    echo "Chargement des variables d'environnement depuis ../../.env"
fi

echo "DEBUG: DB_USER=$DB_USER DB_PASS=$DB_PASS DB_NAME=$DB_NAME DB_HOST=$DB_HOST"

DATABASE_NAME="${DB_NAME}"
USERNAME="${DB_USER}"
PASSWORD="${DB_PASS}"
HOST="${DB_HOST}"

echo "Utilisation de l'utilisateur : $USERNAME"
echo "Suppression de la base $DATABASE_NAME si elle existe..."
mysql --host="$HOST" --user="$USERNAME" --password="$PASSWORD" --default-character-set=utf8mb4 -e "DROP DATABASE IF EXISTS \`$DATABASE_NAME\`;"

echo "Création de la base $DATABASE_NAME..."
mysql --host="$HOST" --user="$USERNAME" --password="$PASSWORD" --default-character-set=utf8mb4 -e "CREATE DATABASE \`$DATABASE_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Import des migrations..."
for file in ../migration/*.sql
do
    if [ -f "$file" ]; then
        echo "Import de $file"
        mysql --host="$HOST" --user="$USERNAME" --password="$PASSWORD" --default-character-set=utf8mb4 "$DATABASE_NAME" < "$file"
    fi
done

echo "Base de données prête."
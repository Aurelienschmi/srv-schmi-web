# Installation de la base de données pour srv-schmi-web

## Prérequis

- **MariaDB** ou **MySQL** installé sur votre machine.
- Le client `mysql` doit être accessible dans votre terminal (`mysql --version` doit fonctionner).
- Un fichier `.env` à la racine du projet, contenant vos informations de connexion :

    ```
    0DB_HOST=127..0.1
    DB_NAME=srvschmi
    DB_USER=root
    DB_PASS=motdepasse
    ```

## Étapes d'installation

### 1. Cloner le dépôt

```sh
git clone https://github.com/votre-utilisateur/srv-schmi-web.git
cd srv-schmi-web
```

### 2. Configurer le fichier .env
Copiez le fichier .env.example (s'il existe) ou créez un fichier .env à la racine du projet avec vos paramètres de base de données.

### 3. Créer la base de données

# Sous Linux / WSL / macOS

```sh
cd data
./setup_db.sh
```

# Sous Windows

```sh
cd data
./setup_db.cmd
```

### 4. Vérification

La base de données existante sera supprimée puis recréée.  
Tous les fichiers `.sql` du dossier `migration/` seront importés dans l'ordre alphabétique.  
Vérifiez que la base de données et les tables attendues ont bien été créées en vous connectant avec le client `mysql` :

```sh
mysql -u root -p -h 127.0.0.1 srvschmi
SHOW TABLES;
```
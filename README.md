# Installation de la base de données pour srv-schmi-web

## Prérequis

- **MariaDB** ou **MySQL** installé sur votre machine.
- Le client `mysql` doit être accessible dans votre terminal (`mysql --version` doit fonctionner).
- **Important :** Le dossier `bin` de MariaDB/MySQL (ex : `C:\Program Files\MariaDB 11.7\bin`) doit être ajouté à la variable d'environnement système `Path` sous Windows.  
  [Voir comment faire](https://mariadb.com/kb/en/setting-the-path-for-mariadb-tools/)
- Un fichier `.env` à la racine du projet, contenant vos informations de connexion :

    ```
    DB_HOST=127.0.0.1
    DB_NAME=srvschmi
    DB_USER=root
    DB_PASS=motdepasse
    ```

- **Laragon** installé pour lancer l'application web facilement sous Windows.  
  [Télécharger Laragon](https://laragon.org/download/)

## Étapes d'installation

### 1. Cloner le dépôt

```sh
git clone https://github.com/votre-utilisateur/srv-schmi-web.git
cd srv-schmi-web
```

### 2. Configurer le fichier .env
Copiez le fichier .env.example (s'il existe) ou créez un fichier .env à la racine du projet avec vos paramètres de base de données.

### 3. Créer la base de données

#### Sous Linux / WSL / macOS

```sh
cd data
./setup_db.sh
```

#### Sous Windows

```sh
cd data
setup_db.cmd
```

> **Remarque :**  
> Si la commande `mysql` n'est pas reconnue, ajoute le chemin du dossier `bin` de MariaDB/MySQL à la variable d'environnement `Path` puis redémarre ton terminal.

### 4. Vérification

La base de données existante sera supprimée puis recréée.  
Tous les fichiers `.sql` du dossier `migration/` seront importés dans l'ordre alphabétique.  
Vérifiez que la base de données et les tables attendues ont bien été créées en vous connectant avec le client `mysql` :

```sh
mysql -u root -p -h 127.0.0.1 srvschmi
SHOW TABLES;
```

### 5. Lancer l'application web avec Laragon

1. **Déplacez le dossier du projet dans le dossier `www` de Laragon**  
   Exemple : `C:\laragon\www\srv-schmi-web`

2. **Démarrez Laragon** et assurez-vous que Apache/Nginx et MySQL/MariaDB sont lancés.

3. **Accédez à l'application**  
   Ouvrez votre navigateur et allez à l'adresse :  
   ```
   http://srv-schmi-web.test
   ```
   ou  
   ```
   http://localhost/srv-schmi-web
   ```
   selon la configuration de Laragon.

> **Astuce :**  
> Vous pouvez configurer un VirtualHost personnalisé dans Laragon pour accéder à votre projet via une URL conviviale.

---

## Statut du serveur Palworld (`palworld_status.json`)

Le fichier `palworld_status.json` sert à stocker et à afficher le statut en temps réel du serveur Palworld sur l’interface web.

### Emplacement

Le fichier doit être placé dans le dossier :

```
/var/palworld_status.json
```
(par exemple : `c:\laragon\www\var\palworld_status.json`)

### Format attendu

Le fichier doit contenir un objet JSON avec la structure suivante :

```json
{
  "status": "online",           // ou "offline"
  "players": ["Joueur1", "Joueur2"], // Liste des joueurs connectés (optionnel)
  "last_update": "2024-06-01 12:34:56" // Date/heure de la dernière mise à jour (optionnel)
}
```

### Exemple

```json
{
  "status": "online",
  "players": ["Alice", "Bob"],
  "last_update": "2025-05-22 18:30:00"
}
```

### Utilisation dans l’application

- Le fichier est lu par la page `src/palworld.php` pour afficher le statut du serveur, la liste des joueurs connectés et la date de dernière mise à jour.
- Si le fichier n’existe pas ou est mal formé, un message d’erreur s’affiche sur la page.

### Bonnes pratiques

- **Ne place pas ce fichier dans un dossier public** (comme `/public` ou `/src`), mais dans un dossier dédié comme `/cache` à la racine du projet.
- **Le fichier doit être accessible en lecture par le serveur web**.
- **Le fichier peut être mis à jour automatiquement** par un script externe (ex : script Python, cron, etc.) qui interroge le serveur Palworld et écrit les informations dans ce fichier JSON.

---
## Statut du serveur CS:GO (`csgo.php`)

La page `csgo.php` permet d’afficher en temps réel le statut du serveur CS:GO, la liste des joueurs connectés et d’accéder rapidement au serveur via Steam.

### Prérequis

- Le serveur CS:GO doit être lancé et accessible sur le réseau (adresse et port définis dans le code).
- La bibliothèque [xPaw/PHP-Source-Query](https://github.com/xPaw/PHP-Source-Query) doit être installée dans le dossier `lib/SourceQuery` à la racine du projet.
- Les ports nécessaires doivent être ouverts sur le pare-feu.

### Configuration

Dans le fichier `src/csgo.php`, vérifiez ou adaptez ces constantes selon votre configuration :

```php
define('SERVER_ADDR', '192.168.1.185'); // Adresse IP du serveur CS:GO
define('SERVER_PORT', 27015);           // Port du serveur CS:GO
define('RCON_PASS', 'votre_mdp_rcon');  // Mot de passe RCON (optionnel ici)
define('TIMEOUT', 3);                   // Timeout de connexion en secondes
```

### Tester la page

1. **Démarrez votre serveur CS:GO** et assurez-vous qu’il est accessible depuis la machine où tourne votre application web.
2. **Vérifiez que la bibliothèque SourceQuery est bien présente** dans `lib/SourceQuery/`.
3. **Accédez à la page** dans votre navigateur :

   ```
   http://localhost/srv-schmi-web/src/csgo.php
   ```

4. **Résultat attendu :**
   - Si le serveur est en ligne, la page affiche :
     - Le nom du serveur
     - Le statut 🟢 En ligne
     - La liste des joueurs connectés (ou "Aucun joueur connecté")
     - Des boutons pour rejoindre ou spectate via Steam
   - Si le serveur est hors ligne ou inaccessible, la page affiche :
     - Le statut 🔴 Hors ligne
     - Un message d’erreur si applicable

### Dépannage

- **Erreur de connexion** : Vérifiez l’adresse IP, le port et que le serveur CS:GO est bien lancé.
- **Erreur de bibliothèque manquante** : Vérifiez que tous les fichiers de `lib/SourceQuery/` sont présents.
- **Problème de droits** : Assurez-vous que le serveur web a accès au réseau et aux fichiers nécessaires.

---

### Mode développement / Données de test pour la page CS:GO

Pour développer ou tester la page `csgo.php` sans avoir besoin d’un vrai serveur CS:GO en ligne, vous pouvez activer un mode "mock" qui affiche des données fictives.

**Comment faire :**

- Ajoutez `?mock=1` à l’URL de la page pour activer le mode test :

  ```
  http://localhost/srv-schmi-web/src/csgo.php?mock=1
  ```

- La page affichera alors un serveur fictif "Serveur CS:GO de Test" avec des joueurs de test.

**À quoi ça sert ?**

- Permet de développer l’interface et de tester l’affichage sans dépendre d’un vrai serveur CS:GO.
- Pratique pour la démonstration ou le développement hors-ligne.

**Désactiver le mode test :**

- Retirez `?mock=1` de l’URL pour revenir au fonctionnement normal (connexion au vrai serveur).

---
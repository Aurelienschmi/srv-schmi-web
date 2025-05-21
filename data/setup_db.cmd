@echo off
REM Charger les variables depuis le .env
if not exist "..\.env" (
    echo Le fichier ..\.env est introuvable.
    pause
    exit /b 1
)
for /f "usebackq tokens=1,2 delims==" %%A in ("..\.env") do (
    if "%%A"=="DB_NAME" set DB_NAME=%%B
    if "%%A"=="DB_USER" set DB_USER=%%B
    if "%%A"=="DB_PASS" set DB_PASS=%%B
    if "%%A"=="DB_HOST" set DB_HOST=%%B
)

set DB_NAME=%DB_NAME:"=%
set DB_USER=%DB_USER:"=%
set DB_PASS=%DB_PASS:"=%
set DB_HOST=%DB_HOST:"=%

echo Suppression de la base %DB_NAME% si elle existe...
mysql --host=%DB_HOST% --user=%DB_USER% --password=%DB_PASS% --default-character-set=utf8mb4 -e "DROP DATABASE IF EXISTS `%DB_NAME%`;"

echo Création de la base %DB_NAME%...
mysql --host=%DB_HOST% --user=%DB_USER% --password=%DB_PASS% --default-character-set=utf8mb4 -e "CREATE DATABASE `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Import des migrations...
for %%f in (..\migration\*.sql) do (
    echo Import de %%f
    mysql --host=%DB_HOST% --user=%DB_USER% --password=%DB_PASS% --default-character-set=utf8mb4 %DB_NAME% < "%%f"
)

echo Base de données prête.
pause
#!/bin/bash

echo "=== DÉPLOIEMENT LAGENTO AVEC POSTGRESQL LOCAL ==="

SERVER="180.149.198.204"
USER="lamine"
PASSWORD="55887788Lb"
APP_DIR="/var/www/lagento"

echo "1. Copie des fichiers vers le serveur..."
scp -o StrictHostKeyChecking=no setup_local_postgres.sql ${USER}@${SERVER}:${APP_DIR}/
scp -o StrictHostKeyChecking=no install_postgres.sh ${USER}@${SERVER}:${APP_DIR}/
scp -o StrictHostKeyChecking=no .env.production ${USER}@${SERVER}:${APP_DIR}/.env

echo "2. Installation de PostgreSQL sur le serveur..."
echo "${PASSWORD}" | ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} "
    cd ${APP_DIR}
    chmod +x install_postgres.sh
    sudo ./install_postgres.sh
"

echo "3. Exécution des migrations Laravel..."
echo "${PASSWORD}" | ssh -o StrictHostKeyChecking=no ${USER}@${SERVER} "
    cd ${APP_DIR}
    git pull origin master
    composer install --optimize-autoloader --no-dev
    php artisan config:cache
    php artisan migrate --force
    php artisan db:seed --force
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    sudo systemctl restart php8.3-fpm
    sudo systemctl restart nginx
"

echo "=== DÉPLOIEMENT TERMINÉ ==="
#!/bin/bash

# Script d'installation PostgreSQL sur VPS Ubuntu
echo "Installation de PostgreSQL..."

# Mise à jour du système
sudo apt update

# Installation PostgreSQL
sudo apt install -y postgresql postgresql-contrib postgresql-server-dev-all

# Démarrer et activer PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Installer l'extension vector pour PostgreSQL
echo "Installation de l'extension pgvector..."
cd /tmp
git clone --branch v0.5.1 https://github.com/pgvector/pgvector.git
cd pgvector
make
sudo make install

# Redémarrer PostgreSQL
sudo systemctl restart postgresql

# Configuration de PostgreSQL
echo "Configuration de la base de données..."

# Exécuter le script SQL en tant que postgres
sudo -u postgres psql -f /var/www/lagento/setup_local_postgres.sql

# Configurer l'authentification
echo "Configuration de l'authentification..."
sudo sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/*/main/postgresql.conf
echo "host    all             all             127.0.0.1/32            md5" | sudo tee -a /etc/postgresql/*/main/pg_hba.conf
echo "local   all             all                                     md5" | sudo tee -a /etc/postgresql/*/main/pg_hba.conf

# Redémarrer PostgreSQL
sudo systemctl restart postgresql

echo "PostgreSQL installé et configuré avec succès!"
echo "Base: lagento"
echo "User: lagento_user"
echo "Password: Lagento2025!"
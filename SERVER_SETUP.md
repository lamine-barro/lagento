# CONFIGURATION POSTGRESQL LOCAL - À EXÉCUTER SUR LE SERVEUR

## Étape 1: Se connecter au serveur
```bash
ssh lamine@180.149.198.204
```

## Étape 2: Aller dans le répertoire du projet
```bash
cd /var/www/lagento
```

## Étape 3: Récupérer les dernières modifications
```bash
git pull origin master
```

## Étape 4: Installer PostgreSQL
```bash
sudo apt update
sudo apt install -y postgresql postgresql-contrib postgresql-server-dev-all
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

## Étape 5: Installer l'extension pgvector
```bash
cd /tmp
git clone --branch v0.5.1 https://github.com/pgvector/pgvector.git
cd pgvector
make
sudo make install
sudo systemctl restart postgresql
```

## Étape 6: Configurer la base de données
```bash
cd /var/www/lagento
sudo -u postgres psql -f setup_local_postgres.sql
```

## Étape 7: Configurer l'authentification PostgreSQL
```bash
sudo sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/*/main/postgresql.conf
echo "host    all             all             127.0.0.1/32            md5" | sudo tee -a /etc/postgresql/*/main/pg_hba.conf
echo "local   all             all                                     md5" | sudo tee -a /etc/postgresql/*/main/pg_hba.conf
sudo systemctl restart postgresql
```

## Étape 8: Configurer Laravel
```bash
cd /var/www/lagento
cp .env.production .env
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan migrate --force
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

## Étape 9: Tester
```bash
curl -I https://www.lagento.ci
```

## Informations de connexion PostgreSQL:
- Host: 127.0.0.1
- Port: 5432
- Database: lagento
- Username: lagento_user
- Password: Lagento2025!
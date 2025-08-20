-- Script pour configurer PostgreSQL local sur VPS
-- À exécuter en tant que postgres user

-- Créer la base de données
CREATE DATABASE lagento;

-- Créer l'utilisateur
CREATE USER lagento_user WITH ENCRYPTED PASSWORD 'Lagento2025!';

-- Donner tous les privilèges sur la base
GRANT ALL PRIVILEGES ON DATABASE lagento TO lagento_user;

-- Se connecter à la base lagento
\c lagento;

-- Activer l'extension UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "vector";

-- Donner les privilèges sur le schéma public
GRANT ALL ON SCHEMA public TO lagento_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO lagento_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO lagento_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO lagento_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO lagento_user;
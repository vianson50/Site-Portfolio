<?php
/**
 * BLACK_PROTOCOL - Configuration de la base de données
 *
 * Fichier de connexion à la base de données MySQL via PDO.
 * Ce fichier contient les informations d'identification et
 * le schéma SQL complet de la base de données.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

/*
|==========================================================================
| SCHÉMA SQL DE LA BASE DE DONNÉES
|==========================================================================
|
| Exécutez ce schéma pour créer la base de données et les tables.
|
| CREATE DATABASE IF NOT EXISTS black_protocol
| CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
|
| USE black_protocol;
|
| CREATE TABLE IF NOT EXISTS users (
|     id INT AUTO_INCREMENT PRIMARY KEY,
|     username VARCHAR(50) NOT NULL UNIQUE,
|     email VARCHAR(100) NOT NULL UNIQUE,
|     password VARCHAR(255) NOT NULL,
|     role ENUM('admin', 'user') DEFAULT 'user',
|     avatar VARCHAR(255) DEFAULT NULL,
|     bio TEXT DEFAULT NULL,
|     skills JSON DEFAULT NULL,
|     github_url VARCHAR(255) DEFAULT NULL,
|     linkedin_url VARCHAR(255) DEFAULT NULL,
|     discord_url VARCHAR(255) DEFAULT NULL,
|     is_active TINYINT(1) DEFAULT 1,
|     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
|     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
| );
|
| CREATE TABLE IF NOT EXISTS projects (
|     id INT AUTO_INCREMENT PRIMARY KEY,
|     user_id INT NOT NULL,
|     title VARCHAR(255) NOT NULL,
|     slug VARCHAR(255) NOT NULL UNIQUE,
|     description TEXT,
|     image_url VARCHAR(255),
|     category ENUM('cybersecurity', 'webdesign', 'gamedev', 'devops') NOT NULL,
|     tech_tags JSON,
|     featured TINYINT(1) DEFAULT 0,
|     status ENUM('draft', 'published', 'archived') DEFAULT 'published',
|     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
|     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
|     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
| );
|
| CREATE TABLE IF NOT EXISTS messages (
|     id INT AUTO_INCREMENT PRIMARY KEY,
|     name VARCHAR(100) NOT NULL,
|     email VARCHAR(100) NOT NULL,
|     subject VARCHAR(255),
|     message TEXT NOT NULL,
|     is_read TINYINT(1) DEFAULT 0,
|     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
| );
|
| -- Insertion de l'administrateur par défaut (mot de passe : admin123)
| INSERT INTO users (username, email, password, role, bio) VALUES
| ('admin', 'admin@blackprotocol.com',
|  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
|  'admin', 'Administrateur du système BLACK_PROTOCOL');
|
|==========================================================================
*/

// === Paramètres de connexion à la base de données ===
$DB_HOST = "localhost";
$DB_NAME = "black_protocol";
$DB_USER = "root";
$DB_PASS = "";
$DB_CHARSET = "utf8mb4";

/**
 * Établit et retourne la connexion PDO à la base de données
 *
 * Utilise les paramètres définis ci-dessus pour créer une instance PDO.
 * Configure le mode d'erreur sur exception et le jeu de caractères UTF-8.
 *
 * @return PDO Instance de connexion à la base de données
 * @throws PDOException Si la connexion échoue
 */
function getDBConnection(): ?PDO
{
    // Déclaration des variables globales pour accès dans la fonction
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET, $DB_ERROR;

    $DB_ERROR = null;

    // Construction du DSN (Data Source Name) avec le charset UTF-8
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

    // Options de configuration PDO
    $options = [
        // Activer le mode exception pour les erreurs SQL
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Retourner les résultats sous forme de tableau associatif par défaut
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Désactiver l'émulation des requêtes préparées (true prepared statements)
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        // Tentative de connexion à la base de données
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        $DB_ERROR = $e->getMessage();
        return null;
    }
}

// Retourner la connexion immédiatement lorsqu'inclu
return getDBConnection();

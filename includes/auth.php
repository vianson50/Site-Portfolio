<?php
/**
 * BLACK_PROTOCOL - Système d'authentification
 *
 * Ce fichier contient toutes les fonctions nécessaires à la gestion
 * de l'authentification des utilisateurs : connexion, inscription,
 * déconnexion, vérification des rôles et gestion des profils.
 *
 * @package BLACK_PROTOCOL
 * @version 1.0
 */

// === Démarrage de la session ===
// Vérifier si la session n'est pas déjà active avant de la démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Inclusion de la configuration de la base de données ===
require_once __DIR__ . "/../config/database.php";

// ============================================================================
// INSTANCE SINGLETON PDO
// ============================================================================

/**
 * @var PDO|null $pdoInstance Instance unique de connexion PDO (pattern Singleton)
 */
$pdoInstance = null;

/**
 * Retourne l'instance unique de la connexion PDO (Singleton)
 *
 * Si la connexion n'existe pas encore, elle est créée une seule fois
 * et réutilisée pour toutes les opérations ultérieures.
 *
 * @return PDO|null Instance de connexion ou null si échec
 */
function getDB(): ?PDO
{
    global $pdoInstance;

    // Si aucune instance n'existe, on en crée une nouvelle
    if ($pdoInstance === null) {
        $pdoInstance = getDBConnection();
    }

    return $pdoInstance;
}

// ============================================================================
// FONCTIONS DE VÉRIFICATION DE SESSION
// ============================================================================

/**
 * Vérifie si un utilisateur est actuellement connecté
 *
 * Contrôle l'existence des variables de session essentielles
 * pour déterminer si l'utilisateur est authentifié.
 *
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn(): bool
{
    return isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"]);
}

/**
 * Récupère les données de l'utilisateur actuellement connecté
 *
 * Interroge la base de données pour obtenir les informations complètes
 * de l'utilisateur à partir de son identifiant stocké en session.
 *
 * @return array|null Données de l'utilisateur ou null si non connecté
 */
function getCurrentUser(): ?array
{
    // Vérifier si l'utilisateur est connecté
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        // Préparation de la requête pour récupérer les données utilisateur
        // On exclut le mot de passe pour des raisons de sécurité
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, avatar, bio,
                   github_url, linkedin_url, discord_url, is_active,
                   created_at, updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([":id" => $_SESSION["user_id"]]);

        $user = $stmt->fetch();

        // Si l'utilisateur n'existe pas, on retourne null
        if (!$user) {
            return null;
        }
        // L'admin est toujours autorisé, même si is_active = 0
        if (!$user["is_active"] && ($user["role"] ?? "") !== "admin") {
            return null;
        }

        return $user;
    } catch (PDOException $e) {
        // En cas d'erreur, retourner null silencieusement
        return null;
    }
}

// ============================================================================
// FONCTIONS D'AUTHENTIFICATION
// ============================================================================

/**
 * Authentifie un utilisateur avec son email et son mot de passe
 *
 * Vérifie les identifiants fournis contre la base de données.
 * Si l'authentification réussit, les données de session sont initialisées.
 *
 * @param string $email    Adresse email de l'utilisateur
 * @param string $password Mot de passe en clair de l'utilisateur
 * @return bool True si la connexion réussit, false sinon
 */
function login(string $email, string $password): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        // Nettoyage de l'email
        $email = htmlspecialchars(trim($email), ENT_QUOTES, "UTF-8");

        // Recherche de l'utilisateur par email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, role, is_active
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([":email" => $email]);

        $user = $stmt->fetch();

        // Vérification : utilisateur trouvé
        // L'admin peut TOUJOURS se connecter, même si is_active = 0
        if (!$user) {
            return false;
        }
        if (!$user["is_active"] && $user["role"] !== "admin") {
            return false;
        }

        // Vérification du mot de passe hashé avec bcrypt
        if (!password_verify($password, $user["password"])) {
            return false;
        }

        // === Initialisation des variables de session ===
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = htmlspecialchars(
            $user["username"],
            ENT_QUOTES,
            "UTF-8",
        );
        $_SESSION["email"] = htmlspecialchars(
            $user["email"],
            ENT_QUOTES,
            "UTF-8",
        );
        $_SESSION["role"] = htmlspecialchars(
            $user["role"],
            ENT_QUOTES,
            "UTF-8",
        );

        // Régénérer l'ID de session pour prévenir le session fixation
        session_regenerate_id(true);

        return true;
    } catch (PDOException $e) {
        // En cas d'erreur de base de données, échec silencieux
        return false;
    }
}

/**
 * Inscrit un nouvel utilisateur dans la base de données
 *
 * Crée un nouveau compte utilisateur avec les informations fournies.
 * Le mot de passe est automatiquement haché avec bcrypt.
 *
 * @param string $username Nom d'utilisateur (unique)
 * @param string $email    Adresse email (unique)
 * @param string $password Mot de passe en clair (sera haché)
 * @param string $role     Rôle de l'utilisateur ('admin' ou 'user')
 * @return bool True si l'inscription réussit, false sinon
 */
function register(
    string $username,
    string $email,
    string $password,
    string $role = "user",
): bool {
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        // Nettoyage et validation des entrées
        $username = htmlspecialchars(trim($username), ENT_QUOTES, "UTF-8");
        $email = htmlspecialchars(trim($email), ENT_QUOTES, "UTF-8");
        $role = htmlspecialchars(trim($role), ENT_QUOTES, "UTF-8");

        // Vérification que le rôle est valide
        if (!in_array($role, ["admin", "user"], true)) {
            $role = "user";
        }

        // Hachage sécurisé du mot de passe avec bcrypt (coût par défaut : 10)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Vérifier que le hachage a fonctionné
        if ($hashedPassword === false) {
            return false;
        }

        // Insertion du nouvel utilisateur dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role)
            VALUES (:username, :email, :password, :role)
        ");

        $stmt->execute([
            ":username" => $username,
            ":email" => $email,
            ":password" => $hashedPassword,
            ":role" => $role,
        ]);

        return true;
    } catch (PDOException $e) {
        // L'erreur peut être un doublon (username ou email déjà existant)
        return false;
    }
}

/**
 * Déconnecte l'utilisateur et détruit la session
 *
 * Supprime toutes les données de session, détruit le cookie de session
 * si possible, puis redirige vers la page de connexion.
 *
 * @return void
 */
function logout(): void
{
    // Suppression de toutes les variables de session
    $_SESSION = [];

    // Suppression du cookie de session si il existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"],
        );
    }

    // Destruction complète de la session
    session_destroy();

    // Redirection vers la page de connexion
    header("Location: login.php");
    exit();
}

// ============================================================================
// FONCTIONS DE VÉRIFICATION DES RÔLES
// ============================================================================

/**
 * Vérifie si l'utilisateur actuel a le rôle administrateur
 *
 * @return bool True si l'utilisateur est admin, false sinon
 */
function isAdmin(): bool
{
    return isLoggedIn() &&
        isset($_SESSION["role"]) &&
        $_SESSION["role"] === "admin";
}

/**
 * Exige que l'utilisateur soit connecté
 *
 * Si l'utilisateur n'est pas connecté, il est redirigé vers
 * la page de connexion. À utiliser en haut des pages protégées.
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Exige que l'utilisateur soit administrateur
 *
 * Si l'utilisateur n'est pas connecté ou n'a pas le rôle admin,
 * il est redirigé vers la page de connexion.
 * À utiliser en haut des pages réservées aux administrateurs.
 *
 * @return void
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

// ============================================================================
// FONCTIONS DE GESTION DU PROFIL
// ============================================================================

/**
 * Met à jour les informations du profil utilisateur
 *
 * Permet de modifier les champs du profil d'un utilisateur.
 * Seuls les champs fournis dans le tableau $data seront mis à jour.
 *
 * @param int   $userId Identifiant de l'utilisateur à modifier
 * @param array $data   Tableau associatif des champs à mettre à jour
 *                      Clés possibles : username, email, avatar, bio,
 *                      skills, github_url, linkedin_url, discord_url
 * @return bool True si la mise à jour réussit, false sinon
 */
function updateProfile(int $userId, array $data): bool
{
    // Liste des champs autorisés pour la mise à jour
    $allowedFields = [
        "username",
        "email",
        "avatar",
        "bio",
        "skills",
        "github_url",
        "linkedin_url",
        "discord_url",
    ];

    // Construction dynamique de la requête UPDATE
    $setClauses = [];
    $params = [":id" => $userId];

    foreach ($data as $field => $value) {
        // Vérifier que le champ est autorisé
        if (!in_array($field, $allowedFields, true)) {
            continue;
        }

        // Nettoyage de la valeur
        $sanitizedValue = htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8");

        // Cas spécial pour le champ skills (JSON)
        if ($field === "skills" && is_array($value)) {
            $sanitizedValue = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $setClauses[] = "{$field} = :{$field}";
        $params[":{$field}"] = $sanitizedValue;
    }

    // Si aucun champ valide à mettre à jour, on retourne false
    if (empty($setClauses)) {
        return false;
    }

    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        // Construction de la requête SQL finale
        $sql =
            "UPDATE users SET " .
            implode(", ", $setClauses) .
            " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================================
// FONCTIONS D'ADMINISTRATION
// ============================================================================

/**
 * Récupère la liste de tous les utilisateurs
 *
 * Fonction réservée aux administrateurs. Retourne tous les comptes
 * utilisateurs enregistrés dans la base de données.
 *
 * @return array Liste des utilisateurs (sans les mots de passe)
 */
function getAllUsers(): array
{
    // Vérification des droits d'administration
    if (!isAdmin()) {
        return [];
    }

    try {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        // Récupération de tous les utilisateurs (mot de passe exclu)
        $stmt = $pdo->query("
            SELECT id, username, email, role, avatar, bio,
                   github_url, linkedin_url, discord_url,
                   is_active, created_at, updated_at
            FROM users
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Supprime un utilisateur de la base de données
 *
 * Fonction réservée aux administrateurs. Supprime définitivement
 * un compte utilisateur et toutes ses données associées (projets).
 * Un administrateur ne peut pas supprimer son propre compte.
 *
 * @param int $userId Identifiant de l'utilisateur à supprimer
 * @return bool True si la suppression réussit, false sinon
 */
function deleteUser(int $userId): bool
{
    // Vérification des droits d'administration
    if (!isAdmin()) {
        return false;
    }

    // Protection : un admin ne peut pas supprimer son propre compte
    if ($_SESSION["user_id"] === $userId) {
        return false;
    }

    // Protection : impossible de supprimer un compte admin
    try {
        $pdo = getDB();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
            $stmt->execute([":id" => $userId]);
            $target = $stmt->fetch();
            if ($target && ($target["role"] ?? "") === "admin") {
                return false;
            }
        }
    } catch (PDOException $e) {
        return false;
    }

    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        // Suppression de l'utilisateur (les projets associés seront
        // supprimés automatiquement grâce à ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([":id" => $userId]);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================================================
// FONCTIONS DE GESTION DES MESSAGES
// ============================================================================

/**
 * Récupère tous les messages de contact
 *
 * Fonction réservée aux administrateurs. Retourne tous les messages
 * envoyés via le formulaire de contact, triés par date décroissante.
 *
 * @return array Liste de tous les messages
 */
function getMessages(): array
{
    // Vérification des droits d'administration
    if (!isAdmin()) {
        return [];
    }

    try {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        // Récupération de tous les messages, les plus récents d'abord
        $stmt = $pdo->query("
            SELECT id, name, email, subject, message, is_read, created_at
            FROM messages
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Compte le nombre de messages non lus
 *
 * Retourne le nombre total de messages qui n'ont pas encore été
 * marqués comme lus par un administrateur.
 *
 * @return int Nombre de messages non lus (0 si erreur)
 */
function getMessageCount(): int
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return 0;
        }

        // Comptage des messages non lus
        $stmt = $pdo->query("
            SELECT COUNT(*) as unread_count
            FROM messages
            WHERE is_read = 0
        ");

        $result = $stmt->fetch();

        return $result ? (int) $result["unread_count"] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Met à jour le mot de passe d'un utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param string $newPassword Nouveau mot de passe (en clair, sera hashé)
 * @return bool True si la mise à jour a réussi
 */
function updatePassword(int $userId, string $newPassword): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "UPDATE users SET password = :password WHERE id = :id",
        );
        return $stmt->execute([":password" => $hash, ":id" => $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

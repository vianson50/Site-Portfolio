<?php
/**
 * BLACK_PROTOCOL — Newsletter Service
 * Gestion des abonnés à la newsletter
 */
require_once __DIR__ . "/auth.php";

/**
 * Ajoute un email à la newsletter
 * @return array ['success' => bool, 'message' => string]
 */
function subscribeNewsletter(string $email): array
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return [
                "success" => false,
                "message" => "Erreur de connexion à la base de données.",
            ];
        }

        // Auto-create table if not exists
        $check = $pdo->query("SHOW TABLES LIKE 'newsletter_subscribers'");
        if ($check->rowCount() === 0) {
            $pdo->exec("CREATE TABLE newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                is_active TINYINT(1) DEFAULT 1,
                subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unsubscribed_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        // Nettoyage et validation de l'email
        $email = htmlspecialchars(trim($email), ENT_QUOTES, "UTF-8");

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Adresse email invalide."];
        }

        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("
            SELECT id, is_active FROM newsletter_subscribers
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([":email" => $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing["is_active"]) {
                return [
                    "success" => false,
                    "message" => "Cet email est déjà abonné à la newsletter.",
                ];
            }

            // Réactiver l'abonnement
            $stmt = $pdo->prepare("
                UPDATE newsletter_subscribers
                SET is_active = 1, unsubscribed_at = NULL
                WHERE id = :id
            ");
            $stmt->execute([":id" => $existing["id"]]);

            return [
                "success" => true,
                "message" => "Votre abonnement a été réactivé avec succès !",
            ];
        }

        // Nouvel abonnement
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_subscribers (email)
            VALUES (:email)
        ");
        $stmt->execute([":email" => $email]);

        return [
            "success" => true,
            "message" => "Merci ! Vous êtes maintenant abonné à la newsletter.",
        ];
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Une erreur est survenue. Veuillez réessayer.",
        ];
    }
}

/**
 * Désabonne un email
 */
function unsubscribeNewsletter(string $email): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $email = htmlspecialchars(trim($email), ENT_QUOTES, "UTF-8");

        $stmt = $pdo->prepare("
            UPDATE newsletter_subscribers
            SET is_active = 0, unsubscribed_at = CURRENT_TIMESTAMP
            WHERE email = :email AND is_active = 1
        ");
        $stmt->execute([":email" => $email]);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupère tous les abonnés actifs
 */
function getNewsletterSubscribers(): array
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->query("
            SELECT * FROM newsletter_subscribers
            WHERE is_active = 1
            ORDER BY subscribed_at DESC
        ");

        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Compte les abonnés actifs
 */
function getNewsletterCount(): int
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return 0;
        }

        $stmt = $pdo->query("
            SELECT COUNT(*) as total FROM newsletter_subscribers
            WHERE is_active = 1
        ");

        $result = $stmt->fetch();
        return (int) ($result["total"] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Supprime un abonné (admin)
 */
function deleteSubscriber(int $id): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare("
            DELETE FROM newsletter_subscribers WHERE id = :id
        ");
        $stmt->execute([":id" => $id]);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

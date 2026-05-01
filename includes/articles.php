<?php
/**
 * BLACK_PROTOCOL — Articles Management
 * CRUD pour les articles publiés via l'Article Structurer
 */

require_once __DIR__ . "/../config/database.php";

/**
 * Sauvegarde un article généré en BDD
 */
function saveArticle(
    array $articleData,
    int $authorId,
    string $sourceUrl = "",
): ?int {
    try {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare("
            INSERT INTO articles (title, meta_description, category, introduction, full_summary, plan, tags, source_url, seo_title, author_id, status)
            VALUES (:title, :meta_desc, :category, :introduction, :full_summary, :plan, :tags, :source_url, :seo_title, :author_id, 'published')
        ");

        $stmt->execute([
            ":title" =>
                $articleData["subject"] ??
                ($articleData["seo_title"] ?? "Sans titre"),
            ":meta_desc" => $articleData["meta_description"] ?? "",
            ":category" => $articleData["category"] ?? "Technologie",
            ":introduction" => $articleData["introduction"] ?? "",
            ":full_summary" => $articleData["full_summary"] ?? "",
            ":plan" => json_encode(
                $articleData["plan"] ?? [],
                JSON_UNESCAPED_UNICODE,
            ),
            ":tags" => json_encode(
                $articleData["tags"] ?? [],
                JSON_UNESCAPED_UNICODE,
            ),
            ":source_url" => $sourceUrl,
            ":seo_title" => $articleData["seo_title"] ?? "",
            ":author_id" => $authorId,
        ]);

        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Récupère les articles publiés (pour le blog feed)
 */
function getPublishedArticles(int $limit = 10): array
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->prepare("
            SELECT a.*, u.username as author_name
            FROM articles a
            JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère un article par son ID
 */
function getArticleById(int $id): ?array
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare("
            SELECT a.*, u.username as author_name
            FROM articles a
            JOIN users u ON a.author_id = u.id
            WHERE a.id = :id
        ");
        $stmt->execute([":id" => $id]);
        $article = $stmt->fetch();
        return $article ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Supprime un article (propriétaire uniquement)
 */
function deleteArticle(int $id, int $authorId): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare(
            "DELETE FROM articles WHERE id = :id AND author_id = :author_id",
        );
        $stmt->execute([":id" => $id, ":author_id" => $authorId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Supprime un article (admin — n'importe quel article)
 */
function deleteAnyArticle(int $id): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
        $stmt->execute([":id" => $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Compte le nombre total d'articles publiés
 */
function getArticleCount(): int
{
    try {
        $pdo = getDB();
        if (!$pdo) {
            return 0;
        }

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as total FROM articles WHERE status = 'published'",
        );
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result["total"] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

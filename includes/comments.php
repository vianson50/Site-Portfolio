<?php
/**
 * BLACK_PROTOCOL — Comments System
 * Système de commentaires public pour les articles
 */

require_once __DIR__ . "/../config/database.php";

/**
 * Récupère les commentaires d'un article (public, triés par date)
 */
function getArticleComments(int $articleId): array
{
    try {
        $pdo = getDB();
        if (!$pdo) return [];

        $stmt = $pdo->prepare("
            SELECT * FROM comments
            WHERE article_id = :article_id
            ORDER BY created_at ASC
        ");
        $stmt->execute([":article_id" => $articleId]);
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Compte les commentaires d'un article
 */
function getCommentCount(int $articleId): int
{
    try {
        $pdo = getDB();
        if (!$pdo) return 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM comments WHERE article_id = :article_id");
        $stmt->execute([":article_id" => $articleId]);
        $result = $stmt->fetch();
        return (int) ($result["total"] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Ajoute un commentaire
 */
function addComment(int $articleId, string $authorName, string $content, string $authorEmail = "", bool $isAdmin = false, int $parentId = 0): ?int
{
    try {
        $pdo = getDB();
        if (!$pdo) return null;

        $stmt = $pdo->prepare("
            INSERT INTO comments (article_id, author_name, author_email, content, is_admin, parent_id)
            VALUES (:article_id, :author_name, :author_email, :content, :is_admin, :parent_id)
        ");
        $stmt->execute([
            ":article_id" => $articleId,
            ":author_name" => htmlspecialchars(trim($authorName), ENT_QUOTES, "UTF-8"),
            ":author_email" => htmlspecialchars(trim($authorEmail), ENT_QUOTES, "UTF-8"),
            ":content" => htmlspecialchars(trim($content), ENT_QUOTES, "UTF-8"),
            ":is_admin" => $isAdmin ? 1 : 0,
            ":parent_id" => $parentId > 0 ? $parentId : null,
        ]);

        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Supprime un commentaire (admin)
 */
function deleteComment(int $commentId): bool
{
    try {
        $pdo = getDB();
        if (!$pdo) return false;

        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute([":id" => $commentId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

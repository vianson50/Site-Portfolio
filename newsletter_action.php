<?php
/**
 * BLACK_PROTOCOL — Newsletter AJAX Endpoint
 * Handles subscribe and unsubscribe requests via POST
 */
require_once __DIR__ . "/includes/newsletter.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = $_POST['action'] ?? '';
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Adresse email requise.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

switch ($action) {
    case 'subscribe':
        $result = subscribeNewsletter($email);
        echo json_encode($result);
        break;

    case 'unsubscribe':
        $success = unsubscribeNewsletter($email);
        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Désabonnement réussi. Vous ne recevrez plus de newsletters.'
                : 'Erreur lors du désabonnement.'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
        break;
}

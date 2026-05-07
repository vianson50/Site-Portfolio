<?php
/**
 * BLACK_PROTOCOL — APK Upload & Download Handler
 * Gère l'upload et le téléchargement de l'APK Nouchi Lexicon
 *
 * Sécurité : seul un admin peut uploader, tout le monde peut télécharger
 */

require_once __DIR__ . '/includes/auth.php';

define('APK_DIR', __DIR__ . '/uploads/apk/');
define('APK_META_FILE', APK_DIR . 'apk_meta.json');
define('MAX_APK_SIZE', 100 * 1024 * 1024); // 100 MB

/**
 * Get APK metadata (version, size, date, filename)
 */
function getApkMeta() {
    if (!file_exists(APK_META_FILE)) {
        return null;
    }
    $json = file_get_contents(APK_META_FILE);
    $meta = json_decode($json, true);
    if (!$meta) return null;

    // Vérifier que le fichier existe toujours
    $filepath = APK_DIR . $meta['filename'];
    if (!file_exists($filepath)) return null;

    return $meta;
}

/**
 * Format file size in human readable format
 */
function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 1) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

// ─── Handle POST actions ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Upload — Admin only
    if (isset($_POST['action']) && $_POST['action'] === 'upload') {
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé. Admin uniquement.']);
            exit;
        }

        if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
            $errMsg = 'Erreur lors de l\'upload.';
            if (isset($_FILES['apk_file'])) {
                switch ($_FILES['apk_file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errMsg = 'Le fichier dépasse la taille maximale autorisée.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errMsg = 'Aucun fichier sélectionné.';
                        break;
                }
            }
            echo json_encode(['success' => false, 'error' => $errMsg]);
            exit;
        }

        $file = $_FILES['apk_file'];

        // Vérifier l'extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'apk') {
            echo json_encode(['success' => false, 'error' => 'Seuls les fichiers .apk sont acceptés.']);
            exit;
        }

        // Vérifier la taille
        if ($file['size'] > MAX_APK_SIZE) {
            echo json_encode(['success' => false, 'error' => 'Le fichier dépasse 100 MB.']);
            exit;
        }

        // Vérifier le MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Les APK peuvent avoir différents MIME types
        $allowedMimes = [
            'application/vnd.android.package-archive',
            'application/octet-stream',
            'application/zip',
        ];
        if (!in_array($mime, $allowedMimes)) {
            echo json_encode(['success' => false, 'error' => 'Type de fichier non valide. Attendu : APK.']);
            exit;
        }

        // Supprimer l'ancien APK s'il existe
        $oldMeta = getApkMeta();
        if ($oldMeta && file_exists(APK_DIR . $oldMeta['filename'])) {
            unlink(APK_DIR . $oldMeta['filename']);
        }

        // Générer un nom de fichier sécurisé
        $version = trim($_POST['version'] ?? '1.0.0');
        $safeName = 'nouchi-lexicon-v' . preg_replace('/[^a-zA-Z0-9.\-]/', '', $version) . '.apk';

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], APK_DIR . $safeName)) {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier.']);
            exit;
        }

        // Sauvegarder les métadonnées
        $meta = [
            'filename'     => $safeName,
            'original_name'=> $file['name'],
            'version'      => $version,
            'size'         => $file['size'],
            'size_formatted'=> formatSize($file['size']),
            'upload_date'  => date('Y-m-d H:i:s'),
            'changelog'    => trim($_POST['changelog'] ?? ''),
            'min_android'  => trim($_POST['min_android'] ?? '5.0'),
        ];

        file_put_contents(APK_META_FILE, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode(['success' => true, 'meta' => $meta]);
        exit;
    }

    // Delete — Admin only
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé.']);
            exit;
        }

        $meta = getApkMeta();
        if ($meta && file_exists(APK_DIR . $meta['filename'])) {
            unlink(APK_DIR . $meta['filename']);
        }
        if (file_exists(APK_META_FILE)) {
            unlink(APK_META_FILE);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action non reconnue.']);
    exit;
}

// ─── Handle GET: Download ───
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    $meta = getApkMeta();
    if (!$meta) {
        http_response_code(404);
        die('APK non trouvé.');
    }

    $filepath = APK_DIR . $meta['filename'];
    if (!file_exists($filepath)) {
        http_response_code(404);
        die('Fichier APK manquant.');
    }

    // Incrémenter le compteur de téléchargements
    $meta['downloads'] = ($meta['downloads'] ?? 0) + 1;
    file_put_contents(APK_META_FILE, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Forcer le téléchargement
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="' . $meta['filename'] . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($filepath);
    exit;
}

// ─── Handle GET: API info ───
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['info'])) {
    header('Content-Type: application/json; charset=utf-8');
    $meta = getApkMeta();
    echo json_encode(['success' => true, 'meta' => $meta]);
    exit;
}

// Si on arrive ici sans action, rediriger
header('Location: projet_nouchi.php');
exit;

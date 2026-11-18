<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/univ_clubs/');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $type    = in_array($_POST['type'] ?? '', ['reclamation', 'avis']) ? $_POST['type'] : 'reclamation';
    $message = trim($_POST['message'] ?? '');
    $uid     = $_SESSION['user_id'];

    if (strlen($message) < 10) {
        flash('danger', '⚠️ Le message doit contenir au moins 10 caractères.');
    } else {
        // Préfixe le message pour que l'admin identifie facilement le type
        $finalMsg = "[" . ucfirst($type) . "] " . $message;
        $pdo->prepare("INSERT INTO complaints(user_id, message, status) VALUES(?, ?, 'open')")
            ->execute([$uid, $finalMsg]);
        flash('success', '✅ Votre message a été envoyé à l\'administration. Vous serez notifié du traitement.');
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=student_dashboard')); exit;
}

header('Location: ' . BASE_URL . 'index.php?page=home'); exit;
?>
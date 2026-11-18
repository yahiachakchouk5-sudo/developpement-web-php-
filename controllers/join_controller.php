<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
requireLogin();

$action = $_REQUEST['action'] ?? '';
$uid    = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $clubId = (int)($_POST['club_id'] ?? 0);
    
    // Vérification sécurité
    if ($clubId <= 0) {
        flash('danger', 'Club invalide.');
    } 
    else {
        switch ($action) {
            case 'request_join':
                $motivation = trim($_POST['motivation'] ?? '');
                if (strlen($motivation) < 10) {
                    flash('danger', '⚠️ Motivation trop courte (min. 10 caractères).');
                } else {
                    // Vérifier si déjà membre ou en attente
                    $chk = $pdo->prepare("SELECT status FROM club_members WHERE club_id=? AND user_id=?");
                    $chk->execute([$clubId, $uid]);
                    $existing = $chk->fetchColumn();

                    if ($existing === 'accepted') {
                        flash('warning', 'Vous êtes déjà membre de ce club.');
                    } elseif ($existing === 'pending') {
                        flash('warning', '⚠️ Votre demande est déjà en attente de validation.');
                    } else {
                        // Insertion de la demande
                        $pdo->prepare("INSERT INTO club_members(club_id, user_id, status, motivation, date_demande) VALUES(?, ?, 'pending', ?, NOW())")
                           ->execute([$clubId, $uid, $motivation]);
                        flash('success', '📩 Demande envoyée ! L\'administration ou le responsable du club la validera sous peu.');
                    }
                }
                break;

            case 'cancel_request':
                // Suppression uniquement si c'est 'pending'
                $stmt = $pdo->prepare("DELETE FROM club_members WHERE club_id=? AND user_id=? AND status='pending'");
                $stmt->execute([$clubId, $uid]);
                flash('info', '🗑️ Demande annulée avec succès.');
                break;
        }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=clubs')); exit;
?>
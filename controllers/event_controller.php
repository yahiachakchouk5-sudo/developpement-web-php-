<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
requireLogin();

$action = $_REQUEST['action'] ?? '';
$uid    = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $eid = (int)($_POST['event_id'] ?? 0);
    if ($eid <= 0) { flash('danger', 'Événement invalide.'); header('Location: ' . $_SERVER['HTTP_REFERER']); exit; }

    if ($action === 'subscribe') {
        $nom     = trim($_POST['participant_nom'] ?? '');
        $contact = trim($_POST['participant_contact'] ?? '');
        $isMember = isset($_POST['is_club_member']) ? 1 : 0;

        if (!$nom || !$contact) { flash('danger', '⚠️ Coordonnées obligatoires.'); }
        else {
            $chk = $pdo->prepare("SELECT id, status FROM event_participants WHERE event_id = ? AND user_id = ?");
            $chk->execute([$eid, $uid]);
            $row = $chk->fetch();
            
            if ($row) {
                flash('warning', $row['status']==='accepted' ? '✅ Vous êtes déjà inscrit.' : '⏳ Votre demande est déjà en attente.');
            } else {
                $pdo->prepare("INSERT INTO event_participants(event_id, user_id, participant_nom, participant_contact, is_club_member, status) VALUES(?, ?, ?, ?, ?, 'pending')")
                   ->execute([$eid, $uid, $nom, $contact, $isMember]);
                flash('success', '📩 Demande envoyée à l\'administration. Attendez la validation.');
            }
        }
    } 
    elseif ($action === 'unsubscribe') {
        $pdo->prepare("DELETE FROM event_participants WHERE event_id = ? AND user_id = ? AND status='pending'")->execute([$eid, $uid]);
        flash('info', '🗑️ Demande annulée.');
    }
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=events')); exit;
?>
<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
requireLogin();

$action = $_REQUEST['action'] ?? '';
$uid    = $_SESSION['user_id'];
$myClubs = $_SESSION['my_responsible_clubs'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    
    // 🟢 CRÉER ÉVÉNEMENT (Uniquement pour les clubs gérés)
    if ($action === 'add_event') {
        $clubId = (int)$_POST['club_id'];
        if (!in_array($clubId, $myClubs)) { flash('danger', '⛔ Accès refusé : Vous ne gérez pas ce club.'); header('Location: '.BASE_URL.'index.php?page=student_dashboard'); exit; }
        
        $pdo->prepare("INSERT INTO events(club_id, titre, description, date_debut, date_fin, lieu) VALUES(?, ?, '', ?, ?, ?)")
           ->execute([$clubId, trim($_POST['titre']), $_POST['date_debut'], $_POST['date_fin'], trim($_POST['lieu'])]);
        flash('success', '📅 Événement créé avec succès.');
    } 
    
    // ✅ ACCEPTER MEMBRE (Sécurité : vérifier que le club appartient au responsable)
    elseif ($action === 'accept_request') {
        $reqId = (int)$_POST['req_id'];
        $chk = $pdo->prepare("SELECT cm.club_id FROM club_members cm WHERE cm.id = ? AND cm.status = 'pending'");
        $chk->execute([$reqId]); $row = $chk->fetch();
        
        if ($row && in_array($row['club_id'], $myClubs)) {
            $pdo->prepare("UPDATE club_members SET status='accepted', date_adhesion=NOW() WHERE id=?")->execute([$reqId]);
            flash('success', '✅ Membre accepté. Notification envoyée.');
        } else { flash('danger', '⛔ Accès refusé.'); }
    } 
    
    // ❌ REFUSER MEMBRE
    elseif ($action === 'reject_request') {
        $reqId = (int)$_POST['req_id'];
        $chk = $pdo->prepare("SELECT cm.club_id FROM club_members cm WHERE cm.id = ? AND cm.status = 'pending'");
        $chk->execute([$reqId]); $row = $chk->fetch();
        
        if ($row && in_array($row['club_id'], $myClubs)) {
            $pdo->prepare("UPDATE club_members SET status='rejected' WHERE id=?")->execute([$reqId]);
            flash('info', '❌ Demande refusée.');
        } else { flash('danger', '⛔ Accès refusé.'); }
    }

    // 📩 RÉSOUDRE RÉCLAMATION (Pour les clubs gérés)
    elseif ($action === 'resolve_complaint') {
        $compId = (int)$_POST['comp_id'];
        $chk = $pdo->prepare("SELECT club_id FROM complaints WHERE id = ? AND status = 'open'");
        $chk->execute([$compId]); $row = $chk->fetch();
        
        if ($row && in_array($row['club_id'], $myClubs)) {
            $pdo->prepare("UPDATE complaints SET status='resolved' WHERE id=?")->execute([$compId]);
            flash('success', '📩 Réclamation marquée comme résolue.');
        } else { flash('danger', '⛔ Accès refusé.'); }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=student_dashboard#gestion')); exit;
?>
<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isLogged() || ($_SESSION['user_role'] ?? '') !== 'admin') { header('Location: ' . BASE_URL . 'index.php?page=home'); exit; }

$action = $_REQUEST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    switch ($action) {
        case 'accept_request': $pdo->prepare("UPDATE club_members SET status='accepted', date_adhesion=NOW() WHERE id=?")->execute([(int)$_POST['req_id']]); flash('success','✅ Demande club acceptée.'); break;
        case 'reject_request': $pdo->prepare("UPDATE club_members SET status='rejected' WHERE id=?")->execute([(int)$_POST['req_id']]); flash('danger','❌ Demande club refusée.'); break;
        case 'accept_event_reg': $pdo->prepare("UPDATE event_participants SET status='accepted' WHERE id=?")->execute([(int)$_POST['reg_id']]); flash('success','✅ Inscription événement validée.'); break;
        case 'reject_event_reg': $pdo->prepare("UPDATE event_participants SET status='rejected' WHERE id=?")->execute([(int)$_POST['reg_id']]); flash('danger','❌ Inscription événement refusée.'); break;
        case 'resolve_complaint': $pdo->prepare("UPDATE complaints SET status='resolved' WHERE id=?")->execute([(int)$_POST['comp_id']]); flash('info','📩 Réclamation résolue.'); break;
        case 'add_club': if(trim($_POST['nom']??'')&&trim($_POST['desc']??'')) {$pdo->prepare("INSERT INTO clubs(nom,description,responsable_id) VALUES(?,?,?)")->execute([trim($_POST['nom']),trim($_POST['desc']),(int)$_POST['responsable_id']]); flash('success','🏫 Club ajouté.');} break;
        case 'add_event': if(trim($_POST['titre']??'')&&$_POST['date_debut']??'') {$pdo->prepare("INSERT INTO events(club_id,titre,description,date_debut,date_fin,lieu) VALUES(?,?,?,?,?,?)")->execute([(int)$_POST['club_id'],trim($_POST['titre']),'',$_POST['date_debut'],$_POST['date_fin'],trim($_POST['lieu'])]); flash('success','📅 Événement créé.');} break;
        case 'delete_club': $pdo->prepare("DELETE FROM clubs WHERE id=?")->execute([(int)$_POST['id']]); flash('warning','🗑️ Club supprimé.'); break;
        case 'delete_event': $pdo->prepare("DELETE FROM events WHERE id=?")->execute([(int)$_POST['id']]); flash('warning','🗑️ Événement supprimé.'); break;
    }
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?page=admin_dashboard')); exit;
?>
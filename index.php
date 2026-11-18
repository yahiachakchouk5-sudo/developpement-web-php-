<?php
session_start();
define('BASE_URL', 'http://localhost/univ_clubs/');

// ⚠️ Charge la BDD EN PREMIER. $pdo devient disponible dans tout le script.
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home','login','register','clubs','events','student_dashboard','admin_dashboard'];
if (!in_array($page, $allowed)) $page = 'home';

if (in_array($page,['login','register']) && isset($_SESSION['user_id'])) {
    $dest = ($_SESSION['role']==='admin') ? 'admin_dashboard' : 'student_dashboard';
    header('Location: '.BASE_URL."index.php?page=$dest"); exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
echo '<main class="main-container">';
require_once __DIR__ . "/views/{$page}.php";
echo '</main>';
require_once __DIR__ . '/includes/footer.php';
?>
<?php
// 🔒 Démarre la session UNIQUEMENT si elle n'existe pas déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ... (laissez toutes vos fonctions flash(), csrf(), isLogged(), etc. inchangées en dessous)
function flash($t,$m){ $_SESSION['flash']=['type'=>$t,'msg'=>$m]; }
function getFlash(){ $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function csrf(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verifyCsrf($t){ return hash_equals($_SESSION['csrf']??'',$t); }
function isLogged(){ return isset($_SESSION['user_id']); }
function requireLogin(){ if(!isLogged()){ flash('danger','Connexion requise.'); header('Location:'.BASE_URL.'index.php?page=login'); exit; } }
?>
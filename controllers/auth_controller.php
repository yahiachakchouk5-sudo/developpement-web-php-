<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/univ_clubs/');

$action = $_REQUEST['action'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    
    // ───────── INSCRIPTION ─────────
    if ($action === 'register') {
        $nom = trim($_POST['nom'] ?? ''); $prenom = trim($_POST['prenom'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? '')); $pass = $_POST['password'] ?? ''; $confirm = $_POST['confirm'] ?? '';
        if (!$nom || !$prenom || !$email || !$pass) $errors[] = 'Tous les champs sont obligatoires.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email invalide.';
        if (strlen($pass) < 6) $errors[] = 'Mot de passe ≥ 6 caractères.';
        if ($pass !== $confirm) $errors[] = 'Mots de passe différents.';
        
        if (empty($errors)) {
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?"); $chk->execute([$email]);
            if ($chk->fetch()) $errors[] = 'Email déjà utilisé.';
            else {
                $pdo->prepare("INSERT INTO users(nom,prenom,email,mot_de_passe,role) VALUES(?,?,?,?,'étudiant')")
                   ->execute([$nom, $prenom, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                flash('success', 'Inscription réussie ! Connectez-vous.');
                header('Location: ' . BASE_URL . 'index.php?page=login'); exit;
            }
        }
        $_SESSION['form_errors'] = $errors; $_SESSION['form_data'] = $_POST;
        header('Location: ' . BASE_URL . 'index.php?page=register'); exit;
    }

    // ───────── CONNEXION ─────────
    elseif ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? '')); $pass = $_POST['password'] ?? '';
        if (!$email || !$pass) $errors[] = 'Veuillez remplir tous les champs.';
        else {
            $stmt = $pdo->prepare("SELECT id, nom, prenom, mot_de_passe, role FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]); $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['mot_de_passe'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];

                // 🆕 Charger les clubs dont l'étudiant est responsable
                if ($user['role'] === 'étudiant') {
                    $r = $pdo->prepare("SELECT id FROM clubs WHERE responsable_id = ?"); $r->execute([$user['id']]);
                    $_SESSION['my_responsible_clubs'] = array_column($r->fetchAll(), 'id');
                } else {
                    $_SESSION['my_responsible_clubs'] = [];
                }

                flash('success', 'Bienvenue ' . htmlspecialchars($user['prenom']) . ' !');
                $dest = ($user['role'] === 'admin') ? 'admin_dashboard' : 'student_dashboard';
                header('Location: ' . BASE_URL . "index.php?page={$dest}"); exit;
            }
            $errors[] = 'Email ou mot de passe incorrect.';
        }
        $_SESSION['form_errors'] = $errors; header('Location: ' . BASE_URL . 'index.php?page=login'); exit;
    }
}

if ($action === 'logout') { session_unset(); session_destroy(); flash('info', 'Déconnecté.'); header('Location: ' . BASE_URL . 'index.php?page=home'); exit; }
header('Location: ' . BASE_URL . 'index.php?page=home'); exit;
?>
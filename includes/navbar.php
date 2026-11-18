<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/univ_clubs/');
$pageActuelle = $page ?? 'home';
$isLogged     = isset($_SESSION['user_id']);
$roleUser     = $_SESSION['user_role'] ?? '';
$nomUser      = htmlspecialchars($_SESSION['user_nom'] ?? 'Mon Espace');
?>
<nav class="navbar">
    <div class="nav-container">
        <a class="brand" href="<?= BASE_URL ?>index.php?page=home">🎓 UniClubs</a>
        <button class="nav-toggle" aria-label="Menu">☰</button>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= BASE_URL ?>index.php?page=home" class="<?= $pageActuelle === 'home' ? 'active' : '' ?>">Accueil</a></li>
            <li>
                <?php if ($isLogged): ?>
                    <a href="<?= BASE_URL ?>index.php?page=clubs" class="<?= $pageActuelle === 'clubs' ? 'active' : '' ?>">🏫 Clubs</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>index.php?page=login" class="<?= $pageActuelle === 'login' ? 'active' : '' ?>">🏫 Clubs</a>
                <?php endif; ?>
            </li>
            <li><a href="<?= BASE_URL ?>index.php?page=events" class="<?= $pageActuelle === 'events' ? 'active' : '' ?>">📅 Événements</a></li>
            
            <?php if ($isLogged): ?>
                <li><a href="<?= BASE_URL ?>index.php?page=<?= $roleUser === 'admin' ? 'admin_dashboard' : 'student_dashboard' ?>" class="<?= in_array($pageActuelle, ['student_dashboard', 'admin_dashboard']) ? 'active' : '' ?>">👤 <?= $nomUser ?></a></li>
                <li><a href="<?= BASE_URL ?>controllers/auth_controller.php?action=logout" class="text-danger">🚪 Déconnexion</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>index.php?page=login" class="btn-nav-outline">Connexion</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=register" class="btn-nav-primary">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
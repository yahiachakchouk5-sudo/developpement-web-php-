<?php
// 🔒 Fallbacks de sécurité
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/univ_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }

// ✅ Initialisation sécurisée (évite les warnings "Undefined variable")
$flash   = getFlash() ?? null;
$errors  = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];

// 🧹 Nettoyage immédiat de la session pour ne pas réafficher les anciennes erreurs
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>✨ Inscription</h2>
            <p>Créez votre compte pour rejoindre les clubs universitaires</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>controllers/auth_controller.php">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="action" value="register">

            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($oldData['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($oldData['prenom'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($oldData['email'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 caractères" required>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirmation</label>
                    <input type="password" id="confirm" name="confirm" required>
                </div>
            </div>

            <button type="submit" class="btn-auth">Créer mon compte</button>
        </form>

        <div class="auth-footer">
            Déjà inscrit ? <a href="<?= BASE_URL ?>index.php?page=login">Se connecter</a>
            <span style="margin: 0 0.6rem; color: var(--border);">|</span>
            <a href="<?= BASE_URL ?>index.php?page=home">← Retour à l'accueil</a>
        </div>
    </div>
</div>
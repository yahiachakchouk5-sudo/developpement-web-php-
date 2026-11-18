<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/univ_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }

$flash    = getFlash() ?? null;
$errors   = $_SESSION['form_errors'] ?? [];
$oldEmail = trim($_SESSION['form_data']['email'] ?? '');
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>

<style>
/* CSS INLINE FORCÉ - NE PEUT PAS ÊTRE IGNORÉ */
.login-wrapper {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 80vh !important;
    padding: 2rem 1rem !important;
    width: 100% !important;
}
.login-card {
    width: 100% !important;
    max-width: 420px !important;
    margin: 0 auto !important;
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 20px !important;
    padding: 2.5rem !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
}
.login-header { text-align: center !important; margin-bottom: 2rem !important; }
.login-logo {
    width: 64px !important;
    height: 64px !important;
    background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
    border-radius: 16px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 2rem !important;
    margin: 0 auto 1rem !important;
}
.login-header h1 { font-size: 1.8rem !important; font-weight: 700 !important; color: #0f172a !important; margin-bottom: 0.5rem !important; }
.login-header p { color: #64748b !important; font-size: 0.95rem !important; }
.form-group { margin-bottom: 1.2rem !important; }
.form-group label { display: block !important; font-weight: 600 !important; color: #374151 !important; margin-bottom: 0.4rem !important; }
.form-input {
    width: 100% !important;
    padding: 0.85rem 1rem !important;
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important;
    font-size: 1rem !important;
    background: #f8fafc !important;
}
.form-input:focus { outline: none !important; border-color: #3b82f6 !important; box-shadow: 0 0 0 4px rgba(59,130,246,0.15) !important; background: #fff !important; }
.btn-login {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    width: 100% !important;
    padding: 0.95rem !important;
    background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 1rem !important;
    cursor: pointer !important;
}
.btn-login:hover { transform: translateY(-2px) !important; box-shadow: 0 8px 20px rgba(37,99,235,0.4) !important; }
.divider { display: flex !important; align-items: center !important; margin: 1.5rem 0 !important; color: #94a3b8 !important; }
.divider::before, .divider::after { content: '' !important; flex: 1 !important; height: 1px !important; background: #e2e8f0 !important; }
.divider span { padding: 0 0.75rem !important; }
.login-footer { text-align: center !important; margin-top: 1.5rem !important; color: #64748b !important; }
.login-footer a { color: #3b82f6 !important; text-decoration: none !important; }
.back-link { display: inline-block !important; margin-top: 0.5rem !important; color: #64748b !important; }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">🎓</div>
            <h1>Connexion</h1>
            <p>Accédez à votre espace UniClubs</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>controllers/auth_controller.php">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($oldEmail) ?>" placeholder="nom@univ.edu" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">
                <span>Se connecter</span>
                <span>→</span>
            </button>
        </form>

        <div class="divider"><span>ou</span></div>

        <div class="login-footer">
            <p>Pas encore de compte ? <a href="<?= BASE_URL ?>index.php?page=register">Créer un compte</a></p>
            <br>
            <a href="<?= BASE_URL ?>index.php?page=home" class="back-link">← Retour à l'accueil</a>
        </div>
    </div>
</div>
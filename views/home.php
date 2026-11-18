<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }

$search = trim($_GET['search'] ?? ''); $dateFilter = $_GET['date'] ?? ''; $sortIn = $_GET['sort'] ?? 'e.date_debut ASC';
$allowed = ['e.date_debut ASC','e.date_debut DESC','e.titre ASC','e.titre DESC'];
$sort = in_array($sortIn, $allowed) ? $sortIn : 'e.date_debut ASC';
$page = max(1, (int)($_GET['p'] ?? 1)); $perPage = 6; $offset = ($page - 1) * $perPage;
$uid = $_SESSION['user_id'] ?? null; $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin'; $myClubs = $_SESSION['my_responsible_clubs'] ?? [];

$sql = "SELECT e.*, c.nom as club_nom FROM events e JOIN clubs c ON e.club_id=c.id WHERE (e.titre LIKE :st OR c.nom LIKE :sc) AND e.date_debut >= NOW()";
if (!empty($dateFilter)) $sql .= " AND DATE(e.date_debut) = :dt";
$sql .= " ORDER BY {$sort} LIMIT :o, :p";
$stmt = $pdo->prepare($sql); $sv = "%{$search}%";
$stmt->bindValue(':st', $sv, PDO::PARAM_STR); $stmt->bindValue(':sc', $sv, PDO::PARAM_STR);
if (!empty($dateFilter)) $stmt->bindValue(':dt', $dateFilter, PDO::PARAM_STR);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT); $stmt->bindValue(':p', $perPage, PDO::PARAM_INT);
$stmt->execute(); $events = $stmt->fetchAll();

$cntSql = "SELECT COUNT(*) FROM events e JOIN clubs c ON e.club_id=c.id WHERE (e.titre LIKE ? OR c.nom LIKE ?) AND e.date_debut>=NOW()";
$cntParams = [$sv, $sv]; if (!empty($dateFilter)) { $cntSql .= " AND DATE(e.date_debut)=?"; $cntParams[] = $dateFilter; }
$cntStmt = $pdo->prepare($cntSql); $cntStmt->execute($cntParams);
$totalPages = max(1, ceil($cntStmt->fetchColumn() / $perPage));
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM clubs) as c, (SELECT COUNT(*) FROM events WHERE date_debut>=NOW()) as e, (SELECT COUNT(*) FROM users WHERE role='étudiant') as u")->fetch();
$flash = getFlash();
?>

<div class="hero"><h1>🎓 Bienvenue sur MeilleursClubs</h1><p>Découvrez les clubs, participez aux événements et rejoignez votre communauté.</p></div>
<main class="container" style="padding-top: 0;">
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-num"><?= (int)$stats['c'] ?></div><div class="stat-label">Clubs Actifs</div></div>
        <div class="stat-card"><div class="stat-num"><?= (int)$stats['e'] ?></div><div class="stat-label">Événements à venir</div></div>
        <div class="stat-card"><div class="stat-num"><?= (int)$stats['u'] ?></div><div class="stat-label">Étudiants inscrits</div></div>
    </div>
    <form class="filter-bar" method="GET"><input type="hidden" name="page" value="home">
        <div class="filter-item"><label>🔍 Rechercher</label><input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Événement ou club..."></div>
        <div class="filter-item"><label>📅 Date</label><input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>"></div>
        <div class="filter-item"><label>🔄 Trier</label><select name="sort" onchange="this.form.submit()">
            <option value="e.date_debut ASC" <?= $sort==='e.date_debut ASC'?'selected':'' ?>>Date ↑</option>
            <option value="e.date_debut DESC" <?= $sort==='e.date_debut DESC'?'selected':'' ?>>Date ↓</option>
            <option value="e.titre ASC" <?= strpos($sort,'titre ASC')!==false?'selected':'' ?>>Titre A-Z</option>
        </select></div>
        <button type="submit" class="btn-primary" style="max-width: 140px; margin-top: auto;">Filtrer</button>
    </form>
    <?php if ($flash): ?><div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
    <?php if (empty($events)): ?><div class="empty-state"><span>📭</span><p>Aucun événement trouvé.</p></div><?php else: ?>
    <div class="grid-3">
        <?php foreach ($events as $e): 
            $d = new DateTime($e['date_debut']); $isPast = $d < new DateTime();
            $isResponsible = in_array($e['club_id'], $myClubs);
            $isRegistered = false; if ($uid && !$isResponsible) { $chk = $pdo->prepare("SELECT id FROM event_participants WHERE event_id = ? AND user_id = ?"); $chk->execute([$e['id'], $uid]); $isRegistered = (bool) $chk->fetch(); }
        ?>
        <div class="event-card">
            <div class="event-header"><div class="event-date-badge"><?= $d->format('d') ?><span><?= strtoupper($d->format('M')) ?></span></div><span class="event-club-name">👥 <?= htmlspecialchars($e['club_nom']) ?></span></div>
            <div class="card-body">
                <h3 class="event-title"><?= htmlspecialchars($e['titre']) ?></h3>
                <div class="event-meta"><span>📍 <?= htmlspecialchars($e['lieu']) ?></span><span>🕒 <?= $d->format('H:i') ?> - <?= date('H:i', strtotime($e['date_fin'])) ?></span></div>
                <p class="event-desc"><?= htmlspecialchars(substr($e['description'], 0, 110)) ?>...</p>
                <div class="event-footer">
                    <span style="font-size:0.85rem;font-weight:600;color:var(--text-muted);"><?= $d->format('d M Y') ?></span>
                    <?php if ($isAdmin): ?>
                        <div style="display:flex;gap:0.4rem;"><a href="<?=BASE_URL?>index.php?page=admin_dashboard" class="btn-outline" style="padding:0.5rem 0.7rem;font-size:0.8rem;text-decoration:none;">✏️ Gérer</a>
                        <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="id" value="<?=$e['id']?>">
                        <button type="submit" class="btn-delete" style="padding:0.5rem 0.7rem;font-size:0.8rem;" onclick="return confirm('Supprimer ?')">🗑️</button></form></div>
                    <?php elseif($isResponsible): ?>
                        <a href="<?=BASE_URL?>index.php?page=admin_dashboard" class="btn-card-action btn-primary" style="flex:1;margin-left:0.5rem;text-align:center;text-decoration:none;">👑 Gérer</a>
                    <?php elseif($isRegistered): ?>
                        <form method="POST" action="<?=BASE_URL?>controllers/event_controller.php" style="margin:0;flex:1;margin-left:0.5rem;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="unsubscribe"><input type="hidden" name="event_id" value="<?=$e['id']?>">
                        <button type="submit" class="btn-card-action btn-danger">🗑️ Annuler</button></form>
                    <?php elseif($isPast): ?>
                        <button class="btn-card-action is-disabled" disabled style="flex:1;margin-left:0.5rem;">🔒 Passé</button>
                    <?php elseif($uid): ?>
                        <form method="POST" action="<?=BASE_URL?>controllers/event_controller.php" style="margin:0;flex:1;margin-left:0.5rem;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="subscribe"><input type="hidden" name="event_id" value="<?=$e['id']?>">
                        <button type="submit" class="btn-card-action btn-primary">📝 S'inscrire</button></form>
                    <?php else: ?>
                        <a href="<?=BASE_URL?>index.php?page=login" class="btn-card-action btn-outline" style="flex:1;margin-left:0.5rem;text-align:center;text-decoration:none;">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?><nav class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): ?><a href="?page=home&search=<?= urlencode($search) ?>&date=<?= urlencode($dateFilter) ?>&sort=<?= $sort ?>&p=<?= $i ?>" class="page-link <?= $page == $i ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
    <?php endif; ?>
</main>
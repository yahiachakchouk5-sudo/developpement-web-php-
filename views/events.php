<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }

$search = trim($_GET['search'] ?? ''); $sortIn = $_GET['sort'] ?? 'e.date_debut ASC';
$allowed = ['e.date_debut ASC','e.date_debut DESC','e.titre ASC','e.titre DESC','c.nom ASC'];
$sort = in_array($sortIn, $allowed) ? $sortIn : 'e.date_debut ASC';
$page = max(1, (int)($_GET['p'] ?? 1)); $perPage = 6; $offset = ($page - 1) * $perPage;
$uid = $_SESSION['user_id'] ?? null;
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$myClubs = $_SESSION['my_responsible_clubs'] ?? [];

$sql = "SELECT e.*, c.nom as club_nom FROM events e JOIN clubs c ON e.club_id=c.id WHERE e.titre LIKE :st OR c.nom LIKE :sc ORDER BY $sort LIMIT :o, :p";
$stmt = $pdo->prepare($sql); $sv = "%{$search}%";
$stmt->bindValue(':st', $sv, PDO::PARAM_STR); $stmt->bindValue(':sc', $sv, PDO::PARAM_STR);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT); $stmt->bindValue(':p', $perPage, PDO::PARAM_INT);
$stmt->execute(); $events = $stmt->fetchAll();

$cnt = $pdo->prepare("SELECT COUNT(*) FROM events e JOIN clubs c ON e.club_id=c.id WHERE e.titre LIKE ? OR c.nom LIKE ?"); $cnt->execute([$sv, $sv]);
$totalPages = max(1, ceil($cnt->fetchColumn() / $perPage)); $flash = getFlash();
?>

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:1rem; }
.modal-overlay.active { display:flex; }
.modal-card { background:var(--bg-surface); width:100%; max-width:460px; border-radius:var(--radius-xl); padding:2rem; box-shadow:0 20px 50px rgba(0,0,0,0.2); animation:slideUp 0.3s ease; }
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.form-group { margin-bottom: 1rem; }
.form-group label { display:block; font-weight:600; font-size:0.9rem; margin-bottom:0.4rem; color:var(--text-primary); }
.form-input { width:100%; padding:0.8rem; border:1.5px solid var(--border); border-radius:var(--radius-md); background:var(--bg-body); font-family:inherit; }
.form-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.15); }
.checkbox-group { display:flex; align-items:center; gap:0.6rem; margin-top:0.8rem; padding:0.8rem; background:var(--bg-body); border-radius:var(--radius-md); border:1px solid var(--border); }
.checkbox-group input { width:18px; height:18px; cursor:pointer; }
.checkbox-group label { margin:0; font-size:0.9rem; cursor:pointer; }
</style>

<div class="container" style="padding-top:1.5rem;">
    <div class="hero" style="padding:2rem 0 1rem; text-align:center;"><h1>📅 Événements</h1><p>Inscrivez-vous ou gérez vos événements.</p></div>
    <form class="filter-bar" method="GET"><input type="hidden" name="page" value="events">
        <div class="filter-item"><label>🔍 Rechercher</label><input type="text" name="search" value="<?=htmlspecialchars($search)?>" placeholder="Événement ou club..."></div>
        <div class="filter-item"><label>🔄 Trier</label><select name="sort" onchange="this.form.submit()">
            <option value="e.date_debut ASC" <?=$sort==='e.date_debut ASC'?'selected':''?>>Date ↑</option><option value="e.date_debut DESC" <?=$sort==='e.date_debut DESC'?'selected':''?>>Date ↓</option>
            <option value="e.titre ASC" <?=strpos($sort,'titre ASC')!==false?'selected':''?>>Titre A-Z</option>
        </select></div><button type="submit" class="btn-primary" style="max-width:140px;margin-top:auto">Filtrer</button>
    </form>
    <?php if($flash):?><div class="alert alert-<?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>
    
    <?php if(empty($events)):?><div class="empty-state"><span>📭</span><p>Aucun événement trouvé.</p></div><?php else: ?>
    <div class="grid-3"><?php foreach($events as $e): 
        $d = new DateTime($e['date_debut']); $isPast = $d < new DateTime();
        $isResponsible = in_array($e['club_id'], $myClubs);
        $isRegistered = false; if ($uid && !$isResponsible) { $chk = $pdo->prepare("SELECT id FROM event_participants WHERE event_id = ? AND user_id = ?"); $chk->execute([$e['id'], $uid]); $isRegistered = (bool) $chk->fetch(); }
    ?>
        <div class="event-card">
            <div class="event-header"><div class="event-date-badge"><?=$d->format('d')?><span><?=strtoupper($d->format('M'))?></span></div><span class="event-club-name">👥 <?=htmlspecialchars($e['club_nom'])?></span></div>
            <div class="card-body">
                <h3 class="event-title"><?=htmlspecialchars($e['titre'])?></h3>
                <div class="event-meta"><span>📍 <?=htmlspecialchars($e['lieu'])?></span><span>🕒 <?=$d->format('H:i')?> - <?=date('H:i',strtotime($e['date_fin']))?></span></div>
                <p class="event-desc"><?=htmlspecialchars(substr($e['description'],0,110))?>...</p>
                
                <div class="event-footer">
                    <span style="font-size:0.85rem;font-weight:600;color:var(--text-muted);"><?=$d->format('d M Y')?></span>
                    <?php if($isAdmin): ?>
                        <div style="display:flex;gap:0.4rem;">
                            <a href="<?=BASE_URL?>index.php?page=admin_dashboard" class="btn-outline" style="padding:0.5rem 0.7rem;font-size:0.8rem;text-decoration:none;">✏️ Gérer</a>
                            <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="id" value="<?=$e['id']?>"><button type="submit" class="btn-danger" style="padding:0.5rem 0.7rem;font-size:0.8rem;" onclick="return confirm('Supprimer ?')">🗑️</button></form>
                        </div>
                    <?php elseif($isResponsible): ?>
                        <a href="<?=BASE_URL?>index.php?page=student_dashboard#gestion" class="btn-card-action btn-primary" style="flex:1;margin-left:0.5rem;text-align:center;text-decoration:none;">👑 Gérer</a>
                    <?php elseif($isRegistered): ?>
                        <button class="btn-card-action is-registered" disabled style="flex:1;margin-left:0.5rem;">✅ Inscrit (En attente)</button>
                    <?php elseif($isPast): ?>
                        <button class="btn-card-action is-disabled" disabled style="flex:1;margin-left:0.5rem;">🔒 Passé</button>
                    <?php elseif($uid): ?>
                        <button class="btn-card-action btn-primary" style="flex:1;margin-left:0.5rem;" onclick="openEventForm(<?=$e['id']?>)">📝 S'inscrire</button>
                    <?php else: ?>
                        <a href="<?=BASE_URL?>index.php?page=login" class="btn-card-action btn-outline" style="flex:1;margin-left:0.5rem;text-align:center;text-decoration:none;">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?></div>
    <?php if($totalPages>1):?><nav class="pagination"><?php for($i=1;$i<=$totalPages;$i++):?><a href="?page=events&search=<?=urlencode($search)?>&sort=<?=urlencode($sort)?>&p=<?=$i?>" class="page-link <?=$page==$i?'active':''?>"><?=$i?></a><?php endfor; ?></nav><?php endif; ?>
    <?php endif; ?>
</div>

<div id="eventFormModal" class="modal-overlay" onclick="if(event.target===this) closeEventForm()">
    <div class="modal-card">
        <h4 style="margin:0 0 0.3rem;">📅 Demande d'inscription</h4>
        <p style="color:var(--text-muted);margin-bottom:1.2rem;font-size:0.95rem;">Complétez vos coordonnées. L'administrateur validera votre participation.</p>
        <form method="POST" action="<?=BASE_URL?>controllers/event_controller.php">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="action" value="subscribe">
            <input type="hidden" name="event_id" id="formEventId">
            <div class="form-group"><label>Nom & Prénom</label><input type="text" name="participant_nom" class="form-input" placeholder="Ex: Islem Benzaid" required></div>
            <div class="form-group"><label>Email ou Téléphone</label><input type="text" name="participant_contact" class="form-input" placeholder="islem@email.com" required></div>
            <div class="checkbox-group"><input type="checkbox" id="is_member" name="is_club_member" value="1"><label for="is_member">✅ Je suis membre du club organisateur</label></div>
            <div style="display:flex; gap:0.8rem; margin-top:1.2rem;">
                <button type="submit" class="btn-primary" style="flex:1; padding:0.85rem;">✅ Envoyer la demande</button>
                <button type="button" class="btn-outline" style="padding:0.85rem 1.2rem;" onclick="closeEventForm()">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
function openEventForm(id){document.getElementById('formEventId').value=id;document.getElementById('eventFormModal').classList.add('active');}
function closeEventForm(){document.getElementById('eventFormModal').classList.remove('active');}
</script>
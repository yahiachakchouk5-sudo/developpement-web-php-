<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }

$search = trim($_GET['search'] ?? '');
$sortIn = $_GET['sort'] ?? 'c.nom ASC';
$allowed = ['c.nom ASC','c.nom DESC','membres DESC','membres ASC','c.created_at DESC'];
$sort = in_array($sortIn, $allowed) ? $sortIn : 'c.nom ASC';
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$uid = $_SESSION['user_id'] ?? null;
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$myClubs = $_SESSION['my_responsible_clubs'] ?? [];

$sql = "SELECT c.*, COUNT(cm.user_id) as membres FROM clubs c LEFT JOIN club_members cm ON c.id = cm.club_id WHERE c.nom LIKE :sn OR c.description LIKE :sd GROUP BY c.id, c.nom, c.description, c.responsable_id, c.created_at, c.updated_at ORDER BY $sort LIMIT :o, :p";
$stmt = $pdo->prepare($sql); $sv = "%{$search}%";
$stmt->bindValue(':sn', $sv, PDO::PARAM_STR); $stmt->bindValue(':sd', $sv, PDO::PARAM_STR);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT); $stmt->bindValue(':p', $perPage, PDO::PARAM_INT);
$stmt->execute(); $clubs = $stmt->fetchAll();

$cnt = $pdo->prepare("SELECT COUNT(DISTINCT c.id) FROM clubs c WHERE c.nom LIKE ? OR c.description LIKE ?"); $cnt->execute([$sv, $sv]);
$totalPages = max(1, ceil($cnt->fetchColumn() / $perPage));
$flash = getFlash();
?>

<div class="container" style="padding-top:1.5rem;">
    <div class="hero" style="padding:2rem 0 1rem; text-align:center;"><h1>🏫 Clubs Universitaires</h1><p>Découvrez les associations, postulez ou gérez celles dont vous êtes responsable.</p></div>
    
    <form class="filter-bar" method="GET">
        <input type="hidden" name="page" value="clubs">
        <div class="filter-item"><label>🔍 Rechercher</label><input type="text" name="search" value="<?=htmlspecialchars($search)?>" placeholder="Nom ou description..."></div>
        <div class="filter-item"><label>🔄 Trier</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="c.nom ASC" <?=$sort==='c.nom ASC'?'selected':''?>>Nom A→Z</option>
                <option value="c.nom DESC" <?=$sort==='c.nom DESC'?'selected':''?>>Nom Z→A</option>
                <option value="membres DESC" <?=$sort==='membres DESC'?'selected':''?>>Populaires</option>
                <option value="c.created_at DESC" <?=strpos($sort,'created_at DESC')!==false?'selected':''?>>Récents</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="max-width:140px;margin-top:auto">Filtrer</button>
    </form>

    <?php if($flash):?><div class="alert alert-<?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

    <?php if(empty($clubs)):?>
        <div class="empty-state"><span>📭</span><p>Aucun club trouvé.</p></div>
    <?php else: ?>
    <div class="grid-3">
        <?php foreach($clubs as $club): 
            $userStatus = null; 
            if($uid && !$isAdmin) { 
                $chk=$pdo->prepare("SELECT status FROM club_members WHERE club_id=? AND user_id=?"); 
                $chk->execute([$club['id'],$uid]); 
                $userStatus=$chk->fetchColumn(); 
            }
            $isResponsible = in_array($club['id'], $myClubs);
        ?>
        <div class="club-card">
            <div class="card-header"><span class="club-icon">🎯</span><h3 class="club-name"><?=htmlspecialchars($club['nom'])?></h3></div>
            <div class="card-body">
                <div class="club-meta"><span class="meta-tag">👥 <?= (int)$club['membres'] ?> membres</span></div>
                <p class="club-desc"><?=htmlspecialchars(substr($club['description'],0,120))?>...</p>
                
                <!-- 🔘 BOUTONS CONDITIONNELS -->
                <?php if($isAdmin): ?>
                    <div style="margin-top:auto;padding-top:0.8rem;border-top:1px solid var(--border);display:flex;gap:0.5rem;">
                        <a href="<?=BASE_URL?>index.php?page=admin_dashboard" class="btn-outline" style="flex:1;padding:0.7rem;font-size:0.85rem;text-align:center;text-decoration:none;">✏️ Gérer</a>
                        <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php" style="margin:0;">
                            <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete_club"><input type="hidden" name="id" value="<?=$club['id']?>">
                            <button type="submit" class="btn-danger" style="padding:0.7rem 0.9rem;font-size:0.85rem;" onclick="return confirm('Supprimer définitivement ?')">🗑️</button>
                        </form>
                    </div>
                <?php elseif($isResponsible): ?>
                    <a href="<?=BASE_URL?>index.php?page=student_dashboard#gestion" class="btn-card-action btn-primary" style="text-align:center;text-decoration:none;width:100%;margin-top:auto;">👑 Gérer mon club</a>
                <?php elseif($uid): ?>
                    <?php if($userStatus === 'pending'): ?>
                        <button class="btn-card-action is-pending" disabled style="width:100%;margin-top:auto;">⏳ En attente</button>
                    <?php elseif($userStatus === 'accepted'): ?>
                        <button class="btn-card-action is-registered" disabled style="width:100%;margin-top:auto;">✅ Membre Actif</button>
                    <?php elseif($userStatus === 'rejected'): ?>
                        <button class="btn-card-action is-disabled" disabled style="width:100%;margin-top:auto;">❌ Refusée</button>
                    <?php else: ?>
                        <button class="btn-card-action btn-primary" style="width:100%;margin-top:auto;" onclick="openJoinModal(<?=$club['id']?>, '<?=htmlspecialchars($club['nom'])?>')">📝 Postuler</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?=BASE_URL?>index.php?page=login" class="btn-card-action btn-outline" style="text-align:center;text-decoration:none;width:100%;margin-top:auto;">🔒 Connexion</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if($totalPages>1):?>
    <nav class="pagination">
        <?php for($i=1;$i<=$totalPages;$i++):?>
            <a href="?page=clubs&search=<?=urlencode($search)?>&sort=<?=urlencode($sort)?>&p=<?=$i?>" class="page-link <?=$page==$i?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- 📝 MODALE CORRIGÉE & STYLISÉE -->
<style>
.modal-overlay {
    display: none; position: fixed; inset: 0; 
    background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px);
    z-index: 2000; align-items: center; justify-content: center; padding: 1rem;
}
.modal-overlay.active { display: flex; }
.modal-card {
    background: #ffffff; width: 100%; max-width: 480px;
    border-radius: 16px; padding: 2rem; 
    box-shadow: 0 25px 50px rgba(0,0,0,0.35);
    animation: modalSlideUp 0.3s cubic-bezier(0.4,0,0.2,1);
}
@keyframes modalSlideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.modal-card h4 { margin:0 0 0.5rem; font-size:1.4rem; color:#0f172a; }
.modal-card > p { color:#64748b; margin-bottom:1.5rem; font-size:0.95rem; line-height:1.5; }
.modal-form-group { margin-bottom: 1.2rem; }
.modal-form-group label { display:block; font-weight:600; font-size:0.9rem; margin-bottom:0.5rem; color:#334155; }
.modal-input, .modal-textarea {
    width: 100%; padding: 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 10px;
    background: #f8fafc; font-family: inherit; font-size: 0.95rem; transition: 0.2s;
}
.modal-input:focus, .modal-textarea:focus {
    outline: none; border-color: #2563eb; background: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.modal-textarea { resize: vertical; min-height: 100px; line-height: 1.5; }
.modal-actions { display: flex; gap: 0.8rem; margin-top: 1.5rem; }
.modal-btn-primary {
    flex: 1; padding: 0.9rem; background: #2563eb; color: #fff; border: none;
    border-radius: 10px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: 0.2s;
}
.modal-btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
.modal-btn-outline {
    padding: 0.9rem 1.5rem; background: transparent; border: 1.5px solid #e2e8f0;
    border-radius: 10px; cursor: pointer; font-weight: 600; color: #64748b; transition: 0.2s;
}
.modal-btn-outline:hover { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
</style>

<div id="joinModal" class="modal-overlay" onclick="if(event.target===this) closeJoinModal()">
    <div class="modal-card">
        <h4>📝 Demande d'adhésion</h4>
        <p>Remplissez ce formulaire. Votre demande sera transmise au responsable ou à l'administrateur pour validation.</p>
        
        <form method="POST" action="<?=BASE_URL?>controllers/join_controller.php">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="action" value="request_join">
            <input type="hidden" name="club_id" id="modalClubId">
            
            <div class="modal-form-group">
                <label>Club sélectionné</label>
                <input type="text" id="modalClubName" class="modal-input" disabled style="background:#e2e8f0; font-weight:600; color:#0f172a;">
            </div>
            
            <div class="modal-form-group">
                <label for="motivation">Pourquoi souhaitez-vous nous rejoindre ? *</label>
                <textarea name="motivation" id="motivation" class="modal-textarea" placeholder="Vos motivations, compétences, ce que vous pouvez apporter au club..." required></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="modal-btn-primary">✅ Envoyer la demande</button>
                <button type="button" class="modal-btn-outline" onclick="closeJoinModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openJoinModal(id, name) {
    document.getElementById('modalClubId').value = id;
    document.getElementById('modalClubName').value = name;
    document.getElementById('joinModal').classList.add('active');
    document.body.style.overflow = 'hidden'; // Empêche le scroll arrière-plan
}
function closeJoinModal() {
    document.getElementById('joinModal').classList.remove('active');
    document.body.style.overflow = '';
}
// Ferme avec Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeJoinModal();
});
</script>
<?php
// 🔒 CONFIGURATION & SÉCURITÉ
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }
requireLogin();

// 📊 RÉCUPÉRATION SESSION
$uid     = $_SESSION['user_id'];
$nom     = htmlspecialchars($_SESSION['user_nom'] ?? 'Étudiant');
$myClubs = $_SESSION['my_responsible_clubs'] ?? [];
$flash   = getFlash();

// 1️⃣ MES ADHÉSIONS & ÉVÉNEMENTS REJOINTS
$myClubsJoined = $pdo->prepare("SELECT c.id, c.nom, cm.status, cm.date_demande FROM club_members cm JOIN clubs c ON cm.club_id=c.id WHERE cm.user_id=? ORDER BY cm.date_demande DESC");
$myClubsJoined->execute([$uid]);
$clubsList = $myClubsJoined->fetchAll();

$myEventsJoined = $pdo->prepare("SELECT e.id, e.titre, e.date_debut, e.lieu, c.nom as club FROM event_participants ep JOIN events e ON ep.event_id=e.id JOIN clubs c ON e.club_id=c.id WHERE ep.user_id=? ORDER BY e.date_debut DESC");
$myEventsJoined->execute([$uid]);
$eventsList = $myEventsJoined->fetchAll();

// 2️⃣ BOÎTE DE RÉCEPTION RESPONSABLE (Corrigé : ne plante plus si club_id manque dans complaints)
$respInbox = [];
if (!empty($myClubs)) {
    $safeIds = array_map('intval', $myClubs);
    $idsStr = implode(',', $safeIds);
    // Récupère UNIQUEMENT les demandes d'adhésion en attente pour vos clubs
    $respInbox = $pdo->query("
        SELECT 'request' as type, cm.id, u.nom, u.prenom, c.nom as target, cm.motivation as msg, cm.date_demande as date, cm.status 
        FROM club_members cm 
        JOIN users u ON cm.user_id=u.id 
        JOIN clubs c ON cm.club_id=c.id 
        WHERE cm.club_id IN ($idsStr) AND cm.status='pending'
        ORDER BY date DESC
    ")->fetchAll();
}

// 3️⃣ MES RÉCLAMATIONS ENVOYÉES À L'ADMIN GLOBAL
$myComplaints = $pdo->prepare("SELECT message, status, created_at FROM complaints WHERE user_id=? AND club_id IS NULL ORDER BY created_at DESC");
$myComplaints->execute([$uid]);
$complaintsSent = $myComplaints->fetchAll();
?>

<!-- 🎨 CSS INTÉGRÉ -->
<style>
.dash-container { max-width:1200px; margin:0 auto; padding:2rem 1rem; }
.dash-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem; }
.card { background:var(--bg-surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow-sm); margin-bottom:1.5rem; }
.card h3 { margin:0 0 1rem; font-size:1.2rem; display:flex; align-items:center; gap:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.8rem; }
.grid-2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:1.5rem; margin-bottom:1.5rem; }
.badge-st { padding:0.3rem 0.7rem; border-radius:99px; font-size:0.8rem; font-weight:600; }
.st-pending { background:#fef3c7; color:#92400e; } .st-accepted { background:#dcfce7; color:#166534; } .st-rejected { background:#fee2e2; color:#991b1b; } .st-open { background:#e0f2fe; color:#075985; }

.resp-panel { border-left:4px solid var(--warning); }
.inbox-item { background:var(--bg-body); border:1px solid var(--border); border-radius:var(--radius-md); padding:1rem; margin-bottom:0.8rem; transition:0.2s; }
.inbox-item:hover { border-color:var(--primary); }
.inbox-header { display:flex; justify-content:space-between; margin-bottom:0.4rem; }
.inbox-msg { font-size:0.9rem; color:var(--text-secondary); font-style:italic; margin:0.5rem 0; background:#fff; padding:0.5rem; border-radius:6px; border:1px solid #f1f5f9; }
.inbox-actions { display:flex; gap:0.5rem; margin-top:0.5rem; }
.btn-xs { padding:0.4rem 0.8rem; font-size:0.8rem; border-radius:var(--radius-sm); border:none; cursor:pointer; font-weight:600; }
.btn-primary-xs { background:var(--primary); color:#fff; }
.btn-danger-xs { background:var(--danger); color:#fff; }
.btn-outline-xs { background:transparent; border:1.5px solid var(--border); color:var(--text-secondary); }
.btn-outline-xs:hover { background:var(--bg-body); }

.table-wrap { overflow-x:auto; background:#fff; border-radius:var(--radius-md); border:1px solid var(--border); }
table { width:100%; border-collapse:collapse; font-size:0.95rem; }
th, td { padding:0.8rem; text-align:left; border-bottom:1px solid var(--border); }
th { background:#f8fafc; font-weight:600; color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; }
tr:last-child td { border-bottom:none; }

.form-styled { display:flex; flex-direction:column; gap:0.8rem; }
.form-styled input, .form-styled select, .form-styled textarea { width:100%; padding:0.8rem; border:1.5px solid var(--border); border-radius:var(--radius-md); background:var(--bg-body); font-family:inherit; }
.form-styled input:focus, .form-styled select:focus, .form-styled textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.15); }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:1rem; }
.modal-overlay.active { display:flex; }
.modal-card { background:var(--bg-surface); width:100%; max-width:480px; border-radius:var(--radius-xl); padding:2rem; box-shadow:0 20px 50px rgba(0,0,0,0.2); animation:slideUp 0.3s ease; }
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.btn-back { display:inline-flex; align-items:center; gap:0.5rem; padding:0.8rem 1.5rem; margin-top:1rem; border:1.5px solid var(--border); border-radius:var(--radius-md); color:var(--text-secondary); text-decoration:none; transition:0.2s; background:transparent; }
.btn-back:hover { background:var(--bg-body); border-color:var(--primary); color:var(--primary); }
</style>

<div class="dash-container">
    <div class="dash-header">
        <h2>🎯 Espace Étudiant <span style="color:var(--text-muted);font-weight:400;">(<?= $nom ?>)</span></h2>
        <span class="badge badge-primary">👤 Profil Étudiant</span>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <!-- 👑 ESPACE RESPONSABLE -->
    <?php if (!empty($myClubs)): ?>
    <div class="card resp-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
            <h3 style="margin:0;border:0;padding:0;">👑 Gestion de Mes Clubs Assignés</h3>
            <span class="badge-st st-pending">🔔 <?= count($respInbox) ?> demande(s) en attente</span>
        </div>
        <div class="grid-2" style="margin:0;">
            <div>
                <h4 style="margin:0 0 1rem;">📥 Demandes d'adhésion (Mes clubs)</h4>
                <?php if (empty($respInbox)): ?>
                    <p style="color:var(--text-muted);text-align:center;padding:1.5rem;background:var(--bg-body);border-radius:var(--radius-md);">✅ Aucune demande en attente</p>
                <?php else: ?>
                    <?php foreach ($respInbox as $item): ?>
                    <div class="inbox-item">
                        <div class="inbox-header">
                            <strong>📝 Demande d'adhésion</strong>
                            <span class="badge-st st-pending"><?= ucfirst($item['status']) ?></span>
                        </div>
                        <div style="font-size:0.85rem;color:var(--text-muted);"><?= htmlspecialchars($item['prenom'].' '.$item['nom']) ?> → Club: <?= htmlspecialchars($item['target']) ?></div>
                        <div class="inbox-msg">"<?= htmlspecialchars(substr($item['msg'],0,70)) ?>..."</div>
                        <div class="inbox-actions">
                            <form method="POST" action="<?=BASE_URL?>controllers/club_manager.php" style="margin:0;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="accept_request"><input type="hidden" name="req_id" value="<?=$item['id']?>"><button type="submit" class="btn-xs btn-primary-xs">✅ Accepter</button></form>
                            <form method="POST" action="<?=BASE_URL?>controllers/club_manager.php" style="margin:0;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="reject_request"><input type="hidden" name="req_id" value="<?=$item['id']?>"><button type="submit" class="btn-xs btn-danger-xs">❌ Refuser</button></form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <h4 style="margin:0 0 1rem;">🛠️ Ajouter un Événement à Mes Clubs</h4>
                <form method="POST" action="<?=BASE_URL?>controllers/club_manager.php" class="form-styled">
                    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add_event">
                    <select name="club_id" required>
                        <option value="">Sélectionner un club à gérer</option>
                        <?php foreach($myClubs as $cid): ?><option value="<?=$cid?>"><?= htmlspecialchars($pdo->query("SELECT nom FROM clubs WHERE id=$cid")->fetchColumn()) ?></option><?php endforeach; ?>
                    </select>
                    <input type="text" name="titre" placeholder="Titre de l'événement" required>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;">
                        <input type="datetime-local" name="date_debut" required>
                        <input type="datetime-local" name="date_fin" required>
                    </div>
                    <input type="text" name="lieu" placeholder="Lieu" required>
                    <button type="submit" class="btn-primary" style="width:100%;margin-top:0.5rem;padding:0.8rem;">➕ Créer l'événement</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 📚 MES ACTIVITÉS -->
    <div class="grid-2">
        <div class="card">
            <h3>🏫 Clubs rejoints</h3>
            <?php if (empty($clubsList)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:1rem;">Aucun club rejoint.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Club</th><th>Date</th><th>Statut</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($clubsList as $c): 
                            $badge = match($c['status']) { 'accepted' => 'st-accepted', 'rejected' => 'st-rejected', default => 'st-pending' };
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($c['nom']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['date_demande'])) ?></td>
                                <td><span class="badge-st <?= $badge ?>"><?= ucfirst($c['status']) ?></span></td>
                                <td>
                                    <?php if ($c['status']==='pending'): ?>
                                        <form method="POST" action="<?=BASE_URL?>controllers/join_controller.php" style="margin:0;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="cancel_request"><input type="hidden" name="club_id" value="<?=$c['id']?>"><button type="submit" class="btn-xs btn-danger-xs">Annuler</button></form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>📅 Mes événements inscrits</h3>
            <?php if (empty($eventsList)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:1rem;">Aucune inscription.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Événement</th><th>Club</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($eventsList as $ev): $d=new DateTime($ev['date_debut']); ?>
                            <tr>
                                <td><?= htmlspecialchars($ev['titre']) ?></td>
                                <td><?= htmlspecialchars($ev['club']) ?></td>
                                <td><?=$d->format('d/m/Y H:i')?></td>
                                <td>
                                    <form method="POST" action="<?=BASE_URL?>controllers/event_controller.php" style="margin:0;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="unsubscribe"><input type="hidden" name="event_id" value="<?=$ev['id']?>"><button type="submit" class="btn-xs btn-danger-xs">🗑️ Quitter</button></form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 📤 RÉCLAMATIONS & AVIS -->
    <div class="card">
        <h3>📤 Envoyer une réclamation ou avis à l'administration</h3>
        <form method="POST" action="<?=BASE_URL?>controllers/complaint_controller.php" class="form-styled" style="max-width:600px;">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <select name="type" required>
                <option value="reclamation">🔴 Réclamation / Problème</option>
                <option value="avis">💡 Avis / Suggestion</option>
            </select>
            <textarea name="message" rows="4" placeholder="Décrivez votre message..." required></textarea>
            <button type="submit" class="btn-primary" style="width:auto;padding:0.8rem 1.5rem;">📩 Envoyer</button>
        </form>
    </div>

    <a href="<?=BASE_URL?>index.php?page=home" class="btn-back">← Retour à l'accueil</a>
</div>

<!-- 📝 MODALE -->
<div id="joinModal" class="modal-overlay" onclick="if(event.target===this) closeJoinModal()">
    <div class="modal-card">
        <h4 style="margin:0 0 0.5rem;">📝 Demande d'adhésion</h4>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;font-size:0.95rem;">Votre demande sera transmise au responsable pour validation.</p>
        <form method="POST" action="<?=BASE_URL?>controllers/join_controller.php" class="form-styled">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="action" value="request_join">
            <input type="hidden" name="club_id" id="modalClubId">
            <div style="background:var(--bg-body);padding:0.8rem;border-radius:var(--radius-md);border:1px solid var(--border);margin-bottom:1rem;">
                <label style="font-size:0.85rem;color:var(--text-muted);font-weight:600;">Club sélectionné</label>
                <input type="text" id="modalClubName" disabled style="background:transparent;border:none;padding:0.3rem 0;font-weight:600;font-size:1.05rem;color:var(--text-primary);width:100%;">
            </div>
            <label style="font-weight:600;font-size:0.9rem;">Motivation</label>
            <textarea name="motivation" rows="4" placeholder="Pourquoi souhaitez-vous nous rejoindre ?" required></textarea>
            <div style="display:flex;gap:0.8rem;margin-top:0.5rem;">
                <button type="submit" class="btn-primary" style="flex:1;padding:0.8rem;">✅ Envoyer</button>
                <button type="button" class="btn-outline" style="padding:0.8rem 1.2rem;" onclick="closeJoinModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openJoinModal(id, name) {
    document.getElementById('modalClubId').value = id;
    document.getElementById('modalClubName').value = name;
    document.getElementById('joinModal').classList.add('active');
}
function closeJoinModal() { document.getElementById('joinModal').classList.remove('active'); }
</script>
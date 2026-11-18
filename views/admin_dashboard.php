<?php
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/meilleurs_clubs/');
if (!isset($pdo)) { require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../includes/auth.php'; }
if (($_SESSION['user_role'] ?? '') !== 'admin') { header('Location: '.BASE_URL.'index.php?page=home'); exit; }

$flash = getFlash();
$topClub = $pdo->query("SELECT c.nom, COUNT(cm.user_id) as total FROM clubs c JOIN club_members cm ON c.id = cm.club_id WHERE cm.status = 'accepted' GROUP BY c.id ORDER BY total DESC LIMIT 1")->fetch();
$totalPending = $pdo->query("SELECT COUNT(*) FROM club_members WHERE status='pending'")->fetchColumn();

// ✅ Correction : tri par ep.id au lieu de ep.created_at
$pendingEventRegs = $pdo->query("SELECT ep.id, ep.participant_nom, ep.participant_contact, ep.is_club_member, e.titre as event_title, c.nom as club_name FROM event_participants ep JOIN events e ON ep.event_id=e.id JOIN clubs c ON e.club_id=c.id WHERE ep.status='pending' ORDER BY ep.id DESC")->fetchAll();

$pendingReqs = $pdo->query("SELECT cm.id, c.nom as club, u.nom, u.prenom FROM club_members cm JOIN clubs c ON cm.club_id=c.id JOIN users u ON cm.user_id=u.id WHERE cm.status='pending' ORDER BY cm.id DESC")->fetchAll();
$complaints = $pdo->query("SELECT comp.id, comp.message, comp.status, u.nom, u.prenom FROM complaints comp JOIN users u ON comp.user_id=u.id ORDER BY comp.id DESC")->fetchAll();
$clubs = $pdo->query("SELECT c.id, c.nom, c.description, CONCAT(u.nom,' ',u.prenom) as responsable FROM clubs c LEFT JOIN users u ON c.responsable_id=u.id ORDER BY c.id DESC")->fetchAll();
$events = $pdo->query("SELECT e.id, e.titre, e.date_debut, c.nom as club FROM events e JOIN clubs c ON e.club_id=c.id ORDER BY e.id DESC")->fetchAll();
$usersList = $pdo->query("SELECT id, CONCAT(nom,' ',prenom) as full FROM users WHERE role='étudiant'")->fetchAll();
?>

<div class="container" style="padding-top:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;"><h2>🛡️ Tableau de Bord Admin</h2><span class="badge badge-primary">👤 Admin</span></div>
    <?php if($flash):?><div class="alert alert-<?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

    <div class="grid-3" style="margin-top:0;">
        <div class="card text-center"><h3 style="color:var(--primary);font-size:2rem;margin:0;">🏆 <?=htmlspecialchars($topClub['nom']??'Aucun')?></h3><p>Club le plus populaire (<?= (int)$topClub['total'] ?> membres)</p></div>
        <div class="card text-center"><h3 style="color:var(--warning);font-size:2rem;margin:0;">📋 <?= (int)$totalPending ?></h3><p>Demandes clubs en attente</p></div>
        <div class="card text-center"><h3 style="color:var(--accent);font-size:2rem;margin:0;">📅 <?= count($pendingEventRegs) ?></h3><p>Inscriptions événements en attente</p></div>
    </div>

    <div class="grid-2">
        <!-- 📅 DEMANDES INSCRIPTION ÉVÉNEMENTS -->
        <div class="card" style="grid-column: span 2; border-left:4px solid var(--accent);">
            <h3>📅 Demandes d'inscription aux événements</h3>
            <?php if(empty($pendingEventRegs)): ?><p class="text-muted" style="padding:1rem;">✅ Aucune demande en attente.</p>
            <?php else: ?>
            <div class="table-wrap" style="margin:0;"><table>
                <thead><tr><th>Participant</th><th>Contact</th><th>Événement</th><th>Club</th><th>Membre ?</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach($pendingEventRegs as $reg): ?>
                <tr>
                    <td><strong><?=htmlspecialchars($reg['participant_nom'])?></strong></td>
                    <td><?=htmlspecialchars($reg['participant_contact'])?></td>
                    <td><?=htmlspecialchars($reg['event_title'])?></td>
                    <td><?=htmlspecialchars($reg['club_name'])?></td>
                    <td><?= $reg['is_club_member']?'<span class="badge badge-success">Oui</span>':'<span class="badge badge-warning">Non</span>' ?></td>
                    <td style="display:flex;gap:0.4rem;">
                        <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="accept_event_reg"><input type="hidden" name="reg_id" value="<?=$reg['id']?>"><button type="submit" class="btn-primary" style="padding:0.4rem 0.7rem;font-size:0.8rem;">✅</button></form>
                        <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="reject_event_reg"><input type="hidden" name="reg_id" value="<?=$reg['id']?>"><button type="submit" class="btn-danger" style="padding:0.4rem 0.7rem;font-size:0.8rem;">❌</button></form>
                    </td>
                </tr><?php endforeach; ?></tbody></table></div>
            <?php endif; ?>
        </div>

        <!-- 📥 DEMANDES CLUBS & RÉCLAMATIONS -->
        <div class="card" style="grid-column: span 2;">
            <h3>📥 Demandes Clubs & Réclamations</h3>
            <div class="table-wrap" style="margin:0;"><table>
                <thead><tr><th>Type</th><th>Utilisateur</th><th>Détail</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach($pendingReqs as $r): ?>
                <tr><td><span class="badge badge-warning">Adhésion</span></td><td><?=htmlspecialchars($r['prenom'].' '.$r['nom'])?></td><td>Club: <b><?=htmlspecialchars($r['club'])?></b></td><td>⏳ En attente</td><td style="display:flex;gap:0.5rem;">
                    <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="accept_request"><input type="hidden" name="req_id" value="<?=$r['id']?>"><button type="submit" class="btn-primary" style="padding:0.4rem 0.8rem;font-size:0.85rem;">✅</button></form>
                    <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="reject_request"><input type="hidden" name="req_id" value="<?=$r['id']?>"><button type="submit" class="btn-danger" style="padding:0.4rem 0.8rem;font-size:0.85rem;">❌</button></form>
                </td></tr><?php endforeach; ?>
                <?php foreach($complaints as $c): ?>
                <tr><td><span class="badge badge-danger">Réclamation</span></td><td><?=htmlspecialchars($c['prenom'].' '.$c['nom'])?></td><td><?=htmlspecialchars(substr($c['message'],0,40))?>...</td><td><span class="badge <?= $c['status']==='resolved'?'badge-success':'badge-warning' ?>"><?=ucfirst($c['status'])?></span></td><td>
                    <?php if($c['status']!=='resolved'):?><form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="resolve_complaint"><input type="hidden" name="comp_id" value="<?=$c['id']?>"><button type="submit" class="btn-outline" style="padding:0.4rem 0.8rem;font-size:0.85rem;">📩 Résolu</button></form><?php endif; ?>
                </td></tr><?php endforeach; ?>
                </tbody></table></div>
        </div>

        <!-- 🏫 CRUD CLUBS -->
        <div class="card">
            <h3>🏫 Gestion Clubs</h3>
            <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php" style="margin-bottom:1rem;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add_club">
                <div class="form-group"><input type="text" name="nom" placeholder="Nom du club" class="form-input" required></div>
                <div class="form-group"><textarea name="desc" placeholder="Description" rows="2" class="form-input" required></textarea></div>
                <div class="form-group"><select name="responsable_id" class="form-input" required><option value="">Responsable</option><?php foreach($usersList as $u):?><option value="<?=$u['id']?>"><?=htmlspecialchars($u['full'])?></option><?php endforeach;?></select></div>
                <button type="submit" class="btn-primary btn-full">➕ Ajouter</button>
            </form>
            <div class="table-wrap"><table><thead><tr><th>Nom</th><th>Resp.</th><th></th></tr></thead><tbody>
            <?php foreach($clubs as $cl):?><tr><td><?=htmlspecialchars($cl['nom'])?></td><td><?=htmlspecialchars($cl['responsable']??'-')?></td><td><form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete_club"><input type="hidden" name="id" value="<?=$cl['id']?>"><button type="submit" class="btn-danger" style="padding:0.3rem;font-size:0.7rem;" onclick="return confirm('Supprimer ?')">🗑️</button></form></td></tr><?php endforeach;?></tbody></table></div>
        </div>

        <!-- 📅 CRUD EVENTS -->
        <div class="card">
            <h3>📅 Gestion Événements</h3>
            <form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php" style="margin-bottom:1rem;"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add_event">
                <div class="form-group"><input type="text" name="titre" placeholder="Titre" class="form-input" required></div>
                <div class="form-group"><select name="club_id" class="form-input" required><option value="">Club</option><?php foreach($clubs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['nom'])?></option><?php endforeach;?></select></div>
                <div class="form-row"><input type="datetime-local" name="date_debut" class="form-input" required><input type="datetime-local" name="date_fin" class="form-input" required></div>
                <div class="form-group"><input type="text" name="lieu" placeholder="Lieu" class="form-input" required></div>
                <button type="submit" class="btn-primary btn-full">➕ Créer</button>
            </form>
            <div class="table-wrap"><table><thead><tr><th>Titre</th><th>Club</th><th></th></tr></thead><tbody>
            <?php foreach($events as $ev):?><tr><td><?=htmlspecialchars($ev['titre'])?></td><td><?=htmlspecialchars($ev['club'])?></td><td><form method="POST" action="<?=BASE_URL?>controllers/admin_controller.php"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="id" value="<?=$ev['id']?>"><button type="submit" class="btn-danger" style="padding:0.3rem;font-size:0.7rem;" onclick="return confirm('Supprimer ?')">🗑️</button></form></td></tr><?php endforeach;?></tbody></table></div>
        </div>
    </div>
</div>
<?php
// Connexion brute et directe à votre base
$pdo = new PDO('mysql:host=127.0.0.1;dbname=univ_clubs;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$mdp = '123456';
// Génération du hash PAR VOTRE PHP (garanti 100% compatible)
$hash = password_hash($mdp, PASSWORD_DEFAULT);

// Insertion
$pdo->prepare("INSERT INTO users(nom,prenom,email,mot_de_passe,role,created_at) 
               VALUES('Admin','Système','admin@univ.edu',:hash,'admin',NOW())")
    ->execute(['hash' => $hash]);

// Vérification immédiate
$ok = password_verify($mdp, $hash);

echo "<!DOCTYPE html><body style='font-family:system-ui,sans-serif;padding:40px;background:#f8fafc;text-align:center;'>";
echo $ok 
    ? "<h1 style='color:#10b981;'>✅ COMPTE ADMIN CRÉÉ & VÉRIFIÉ</h1>" 
    : "<h1 style='color:#ef4444;'>❌ ERREUR CRITIQUE</h1>";
echo "<p style='font-size:18px;'><b>📧 Email :</b> admin@univ.edu</p>";
echo "<p style='font-size:18px;'><b>🔑 Mot de passe :</b> 123456</p>";
echo "<div style='background:#fee2e2;padding:15px;border-radius:8px;margin-top:25px;font-weight:bold;color:#991b1b;'>
        ⚠️ SUPPRIMEZ CE FICHIER MAINTENANT : creer_admin.php
      </div>";
echo "</body></html>";
?>
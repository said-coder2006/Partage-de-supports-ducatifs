<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';
$typeMessage = '';

// Redirection si déjà connecté
if(isset($_SESSION['prenom'])){
    header("Location: index.php");
    exit;
}

// Connexion à la base
try {
    $pdo = new PDO('mysql:host=localhost;dbname=saim', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification formulaire
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $matricule = $_POST['matricule'] ?? '';
    $pass = $_POST['pass'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM eleves WHERE matricule=?");
    $stmt->execute([$matricule]);
    $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

     // ===== CONNEXION ADMIN (ROOT) =====
    if ($matricule === 'D00' && $pass === 'root@1234') {
        $_SESSION['admin'] = true;
        $_SESSION['prenom'] = 'Administrateur';
        header("Location: upload.php");
        exit;
    }
    // ===== FIN ADMIN =====

    if($eleve && password_verify($pass, $eleve['pass'])){
        $_SESSION['prenom'] = $eleve['prenom'];
        $_SESSION['matricule'] = $eleve['matricule'];
        $message = "Connexion réussie ! Bienvenue " . htmlspecialchars($eleve['prenom']) . ".";
        $typeMessage = "success";
        header("refresh:2;url=index.php"); // redirection après 2 secondes
    } else {
        $message = "Matricule ou mot de passe incorrect.";
        $typeMessage = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion</title>
<style>
body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; margin:0; padding:0; }
.conteneur-auth { display:flex; justify-content:center; align-items:center; min-height:100vh; padding:20px; }
.carte-auth { background:linear-gradient(to right,#e0e0e0,#fff); padding:30px; border-radius:12px; border:2px solid #000; width:100%; max-width:450px; box-shadow:0 10px 20px rgba(0,0,0,0.1);}
h1 { text-align:center; margin-bottom:15px; color:#000; }
.groupe-champ { margin-bottom:15px; position:relative; }
label { display:block; margin-bottom:5px; font-weight:600; color:#111; }
input { width:100%; padding:10px 40px 10px 10px; border:2px solid #000; border-radius:6px; background:transparent; color:#000; }
.btn-plein { width:100%; padding:12px; font-weight:bold; cursor:pointer; margin-top:10px; }
.alerte { padding:12px 16px; margin-bottom:15px; border-radius:8px; font-weight:600; text-align:center; }
.alerte.success { background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alerte.error { background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.retour { display:block; text-align:center; margin-top:20px; text-decoration:none; font-weight:600; color:#000; }
.show-pass { position:absolute; right:10px; top:38px; cursor:pointer; font-size:18px; color:#555; user-select:none; }
</style>
</head>
<body>

<div class="conteneur-auth">
    <div class="carte-auth">
        <h1>Connexion</h1>

        <?php if($message): ?>
        <div class="alerte <?= $typeMessage ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form method="post" id="form-connexion">
            <div class="groupe-champ">
                <label>Numéro matricule</label>
                <input type="text" name="matricule" required>
            </div>
            <div class="groupe-champ">
                <label>Mot de passe</label>
                <input type="password" name="pass" id="pass" required>
                <span class="show-pass" onclick="togglePass('pass')">&#128065;</span>
            </div>
            <button type="submit" class="btn-plein">Se connecter</button>
        </form>
        <a class="retour" href="inscription.php">Pas encore de compte ? S’inscrire</a>
    </div>
</div>

<script>
function togglePass(id){
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Faire disparaître le message après 3 secondes
const alerte = document.querySelector('.alerte');
if(alerte){
    setTimeout(()=>{ alerte.style.display='none'; },3000);
}
</script>

</body>
</html>

<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';
$typeMessage = '';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=saim', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $matricule = $_POST['matricule'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $confirmPass = $_POST['confirm_pass'] ?? '';

    if ($pass !== $confirmPass) {
        $message = "Les mots de passe ne correspondent pas.";
        $typeMessage = "error";
    } else {
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO eleves (nom, prenom, niveau, matricule, pass) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $niveau, $matricule, $hashedPass]);
            $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $typeMessage = "success";
            header("refresh:2;url=connexion.php");
        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
            $typeMessage = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription</title>
<style>
body { font-family:'Segoe UI', sans-serif; background:#f5f5f5; margin:0; padding:0; }
.conteneur-auth { display:flex; justify-content:center; align-items:center; min-height:100vh; padding:20px; }
.carte-auth { background:linear-gradient(to right,#e0e0e0,#fff); padding:30px; border-radius:12px; border:2px solid #000; width:100%; max-width:450px; box-shadow:0 10px 20px rgba(0,0,0,0.1);}
h1 { text-align:center; margin-bottom:15px; color:#000; }
.groupe-champ { margin-bottom:15px; position:relative; }
label { display:block; margin-bottom:5px; font-weight:600; color:#111; }
input, select { width:100%; padding:10px 40px 10px 10px; border:2px solid #000; border-radius:6px; background:transparent; color:#000; }
input.match { border-color:green; }
input.nomatch { border-color:red; }
.btn-plein { width:100%; padding:12px; font-weight:bold; cursor:pointer; margin-top:10px; }
.alerte { padding:12px 16px; margin-bottom:15px; border-radius:8px; font-weight:600; text-align:center; }
.alerte.success { background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alerte.error { background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.retour { display:block; text-align:center; margin-top:20px; text-decoration:none; font-weight:600; color:#000; }

.show-pass { position:absolute; right:10px; top:38px; cursor:pointer; font-size:18px; color:#555; user-select:none; }
.indicateur { margin-top:-10px; margin-bottom:10px; font-size:0.9rem; font-weight:600; }
.faible { color:red; }
.moyen { color:orange; }
.fort { color:green; }
</style>
</head>
<body>

<div class="conteneur-auth">
    <div class="carte-auth">
        <h1>Inscription</h1>

        <?php if($message): ?>
        <div class="alerte <?= $typeMessage ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form method="post" id="form-inscription">
            <div class="groupe-champ">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div class="groupe-champ">
                <label>Prénom</label>
                <input type="text" name="prenom" required>
            </div>
            <div class="groupe-champ">
                <label>Niveau</label>
                <select name="niveau" required>
                    <option value="L1">L1 Informatique</option>
                    <option value="L2">L2 Informatique</option>
                </select>
            </div>
            <div class="groupe-champ">
                <label>Numéro matricule</label>
                <input type="text" name="matricule" required>
            </div>
            <div class="groupe-champ">
                <label>Mot de passe</label>
                <input type="password" name="pass" id="pass" required>
                <span class="show-pass" onclick="togglePass('pass')">&#128065;</span>
                <div id="force-pass" class="indicateur"></div>
            </div>
            <div class="groupe-champ">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_pass" id="confirm_pass" required>
                <span class="show-pass" onclick="togglePass('confirm_pass')">&#128065;</span>
            </div>
            <button type="submit" class="btn-plein">S’inscrire</button>
        </form>
        <a class="retour" href="connexion.php">Déjà un compte ? Se connecter</a>
    </div>
</div>

<script>
function togglePass(id){
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Vérification force mot de passe et correspondance
const pass = document.getElementById('pass');
const confirmPass = document.getElementById('confirm_pass');
const indicateur = document.getElementById('force-pass');

function evaluerMotDePasse(pwd){
    let force = 0;
    if(pwd.length >= 6) force++;
    if(/[A-Z]/.test(pwd)) force++;
    if(/[0-9]/.test(pwd)) force++;
    if(/[\W]/.test(pwd)) force++;
    if(force <=1) return {texte:'Faible', classe:'faible'};
    if(force==2 || force==3) return {texte:'Moyen', classe:'moyen'};
    return {texte:'Fort', classe:'fort'};
}

pass.addEventListener('input',()=>{
    const result = evaluerMotDePasse(pass.value);
    indicateur.textContent = result.texte;
    indicateur.className = 'indicateur ' + result.classe;
    verifierCorrespondance();
});

confirmPass.addEventListener('input', verifierCorrespondance);
pass.addEventListener('input', verifierCorrespondance);

function verifierCorrespondance(){
    if(confirmPass.value==='') return;
    if(pass.value === confirmPass.value){
        pass.classList.add('match'); pass.classList.remove('nomatch');
        confirmPass.classList.add('match'); confirmPass.classList.remove('nomatch');
    } else {
        pass.classList.add('nomatch'); pass.classList.remove('match');
        confirmPass.classList.add('nomatch'); confirmPass.classList.remove('match');
    }
}
</script>

</body>
</html>

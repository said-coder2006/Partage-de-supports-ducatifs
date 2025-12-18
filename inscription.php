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
<link rel="stylesheet" href="inscrip.css">
</head>
<body>

<!-- Barre de navigation intégrée de la page d'accueil -->
<nav class="barre_navigation" id="nav_bar">
    <div class="logo">Saim</div>

    <div class="menu_navigation" id="menu">
        <a href="acceuil.html">Accueil</a>
        <a href="inscription.php" class="actif">Inscription</a>
    </div>

    <div class="bouton_menu" id="bouton_menu">
        <span class="barre"></span>
        <span class="barre"></span>
        <span class="barre"></span>
    </div>
</nav>

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
// Fondu d’entrée (de la page d'accueil)
window.addEventListener('load', () => {
    document.body.classList.add('visible');
});

// Navbar au scroll (de la page d'accueil)
const nav = document.getElementById('nav_bar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Menu burger (de la page d'accueil)
const boutonMenu = document.getElementById('bouton_menu');
const menu = document.getElementById('menu');

boutonMenu.addEventListener('click', () => {
    boutonMenu.classList.toggle('actif');
    menu.classList.toggle('visible');
});

// Fermer le menu en cliquant sur un lien (mobile)
document.querySelectorAll('#menu a').forEach(link => {
    link.addEventListener('click', () => {
        boutonMenu.classList.remove('actif');
        menu.classList.remove('visible');
    });
});

// Smooth scroll (adapté, mais moins pertinent ici ; conservé pour cohérence)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Scripts existants pour la page d'inscription
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
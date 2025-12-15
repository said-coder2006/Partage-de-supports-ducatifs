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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body { 
    font-family:'Segoe UI', system-ui, sans-serif; 
    background:#f5f5f5; 
    color: #111;
    line-height: 1.6;
    opacity: 0;
    transition: opacity 0.8s ease;
}
body.visible {
    opacity: 1;
}

/* Barre de navigation (intégrée de la page d'accueil) */
.barre_navigation {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    z-index: 1000;
    transition: all 0.4s ease;
}

.barre_navigation.scrolled {
    height: 70px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.logo {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -1px;
    z-index: 10;
}

.menu_navigation {
    display: flex;
    gap: 40px;
}

.menu_navigation a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    position: relative;
    transition: color 0.3s;
}

.menu_navigation a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -8px;
    left: 0;
    background: #000;
    transition: width 0.4s ease;
}

.menu_navigation a:hover,
.menu_navigation a.actif {
    color: #000;
}

.menu_navigation a:hover::after,
.menu_navigation a.actif::after {
    width: 100%;
}

/* Menu burger */
.bouton_menu {
    display: none;
    flex-direction: column;
    gap: 6px;
    cursor: pointer;
    z-index: 10;
}

.barre {
    width: 28px;
    height: 3px;
    background: #000;
    border-radius: 3px;
    transition: all 0.4s ease;
}

.bouton_menu.actif .barre:nth-child(1) {
    transform: rotate(45deg) translate(7px, 7px);
}

.bouton_menu.actif .barre:nth-child(2) {
    opacity: 0;
}

.bouton_menu.actif .barre:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -7px);
}

/* Styles existants pour la page d'inscription (fusionnés) */
.conteneur-auth { 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    min-height: 100vh; 
    padding: 120px 20px 80px; /* Ajusté pour la nav fixed */
}

.carte-auth { 
    background:linear-gradient(to right,#e0e0e0,#fff); 
    padding:30px; 
    border-radius:12px; 
    border:2px solid #000; 
    width:100%; 
    max-width:450px; 
    box-shadow:0 10px 20px rgba(0,0,0,0.1);
}

h1 { 
    text-align:center; 
    margin-bottom:15px; 
    color:#000; 
}

.groupe-champ { 
    margin-bottom:15px; 
    position:relative; 
}

label { 
    display:block; 
    margin-bottom:5px; 
    font-weight:600; 
    color:#111; 
}

input, select { 
    width:100%; 
    padding:10px 40px 10px 10px; 
    border:2px solid #000; 
    border-radius:6px; 
    background:transparent; 
    color:#000; 
}

input.match { 
    border-color:green; 
}

input.nomatch { 
    border-color:red; 
}

.btn-plein { 
    width:100%; 
    padding:12px; 
    font-weight:bold; 
    cursor:pointer; 
    margin-top:10px; 
    border: 2px solid #000;
    background: transparent;
    color: #000;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    transition: color 0.4s ease;
    z-index: 1;
}

.btn-plein::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 0;
    background: #000;
    transition: height 0.4s ease;
    z-index: -1;
}

.btn-plein:hover::before {
    height: 100%;
}

.btn-plein:hover {
    color: white;
}

.alerte { 
    padding:12px 16px; 
    margin-bottom:15px; 
    border-radius:8px; 
    font-weight:600; 
    text-align:center; 
}

.alerte.success { 
    background-color:#d4edda; 
    color:#155724; 
    border:1px solid #c3e6cb; 
}

.alerte.error { 
    background-color:#f8d7da; 
    color:#721c24; 
    border:1px solid #f5c6cb; 
}

.retour { 
    display:block; 
    text-align:center; 
    margin-top:20px; 
    text-decoration:none; 
    font-weight:600; 
    color:#000; 
}

.show-pass { 
    position:absolute; 
    right:10px; 
    top:38px; 
    cursor:pointer; 
    font-size:18px; 
    color:#555; 
    user-select:none; 
}

.indicateur { 
    margin-top:-10px; 
    margin-bottom:10px; 
    font-size:0.9rem; 
    font-weight:600; 
}

.faible { 
    color:red; 
}

.moyen { 
    color:orange; 
}

.fort { 
    color:green; 
}

/* Responsive pour la nav (adapté) */
@media (max-width: 868px) {
    .menu_navigation {
        position: fixed;
        top: 0;
        right: -100%;
        height: 100vh;
        width: 80%;
        max-width: 350px;
        background: #fff;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 50px;
        font-size: 1.5rem;
        transition: right 0.5s cubic-bezier(0.77, 0, 0.18, 1);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
    }

    .menu_navigation.visible {
        right: 0;
    }

    .bouton_menu {
        display: flex;
    }

    .conteneur-auth {
        padding: 120px 20px 80px;
    }
}

@media (max-width: 480px) {
    .conteneur-auth {
        padding: 120px 10px 80px;
    }
}
</style>
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
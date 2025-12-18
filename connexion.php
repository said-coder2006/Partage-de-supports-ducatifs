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
<link rel="stylesheet" href="connexion.css">
</head>
<body>

<!-- Barre de navigation intégrée de la page d'accueil -->
<nav class="barre_navigation" id="nav_bar">
    <div class="logo">Saim</div>

    <div class="menu_navigation" id="menu">
        <a href="acceuil.html">Accueil</a>
        <a href="connexion.php" class="actif">Connexion</a>
    </div>

    <div class="bouton_menu" id="bouton_menu">
        <span class="barre"></span>
        <span class="barre"></span>
        <span class="barre"></span>
    </div>
</nav>

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

// Scripts existants pour la page de connexion
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
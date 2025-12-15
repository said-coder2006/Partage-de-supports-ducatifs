<?php
// ================= CONNEXION BD =================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=saim;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur connexion BD");
}

$message = "";

// ================= LISTE DES MATIERES =================
$matieres = [
    "l1" => ["Architecture de l'ordinateur et Historique de l'informatique", "Systèmes d'exploitation", "Introduction au Web","Arthimétique et nombres","Analyse et algèbres 1","Statistique 1","Bureautique","Internet 1","Français général 1","Anglais général 1","Comptabilité générale","Structures des données et algorythmes fondamentaux","Introduction aux bases de données","Introduction à la programmation","Typologie et topologie réseaux","Administration et sécurité réseaux","Organisation des entreprises","Système d'information","Français général 2","Anglais général 2","Méthodes d'analyse"],
    "l2" => ["Recherches opérationnelles", "Analyse et algèbre 2","Statistique 2","Concepts et mise en oeuvre réseaux","Maintenance et configuration informatique","Méthode MERISE","Programation procédurale","Intelligence Artificielle","Français spécialisé 1","Anglais spécialisé 1","Gestion des stocks","Introduction à la sécurité des réseaux","Administration et language SQL serveur","Language PHP et MY SQL","Programmation orientée objet","Développement d'application en VB","Développement d'application en Delphi","Français spécialisé 2","Anglais spécialisé 2","EC42 Gestion des Ressources Humaines"]
];

// ================= TRAITEMENT UPLOAD =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'upload') {

    $type   = $_POST['type']; // cours | exercice
    $niveau = $_POST['niveau'];
    $matiere = $_POST['matiere'];
    $annee  = $_POST['annee'];

    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));

        if ($ext !== "pdf") {
            $message = " Fichier non autorisé (PDF seulement)";
        } else {

            $nomFichier = time() . "_" . basename($_FILES['pdf']['name']);
            $destination = "uploads/" . $nomFichier;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $destination)) {

                $table = ($type === "cours") ? "cours" : "exercices";

                $stmt = $pdo->prepare("
                    INSERT INTO $table (titre, niveau, annee, matiere, fichier_pdf)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$matiere, $niveau, $annee, $matiere, $nomFichier]);

                $message = " PDF ajouté avec succès";
            } else {
                $message = " Erreur upload";
            }
        }
    } else {
        $message = " Aucun fichier sélectionné";
    }
}

// ================= RECHERCHE =================
$resultats = [];
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'search') {
    $niveau = $_POST['niveau_search'];
    $matiere = $_POST['matiere_search'] ?? "";

    $stmt = $pdo->prepare("SELECT titre, fichier_pdf FROM cours WHERE niveau=? AND matiere=? UNION ALL SELECT titre, fichier_pdf FROM exercices WHERE niveau=? AND matiere=?");
    $stmt->execute([$niveau,$matiere,$niveau,$matiere]);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion PDF</title>
<style>
/* Styles pour la barre de navigation (ajoutés) */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body { 
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #f5f5f5; 
    color: #111;
    line-height: 1.6;
    opacity: 0;
    transition: opacity 0.8s ease;
}
body.visible {
    opacity: 1;
}

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

/* Styles originaux fusionnés et adaptés */
body { font-family: 'Segoe UI', system-ui, sans-serif; background:#f5f5f5; padding:20px;}
.container { max-width: 600px; margin:120px auto 20px; background:linear-gradient(to right, #e0e0e0, #ffffff); padding:40px; border-radius:12px; border:2px solid #000;}
input, select, button { width:100%; padding:12px; margin:10px 0; border:2px solid #000; border-radius:6px; background:transparent; color:#000; }
button { background:transparent; color:#000; border:2px solid #000; cursor:pointer; border-radius:8px; font-weight:600; position:relative; overflow:hidden; transition:color 0.4s ease; z-index:1; }
button::before { content:''; position:absolute; bottom:0; left:0; width:100%; height:0; background:#000; transition:height 0.4s ease; z-index:-1; }
button:hover::before { height:100%; }
button:hover { color:white; }
.card { padding:10px; border-bottom:1px solid #ccc; display:flex; justify-content:space-between; }
label { display:block; margin:10px 0 5px; font-weight:600; color:#111; }

/* Responsive pour la nav */
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

    .container {
        margin: 120px 20px 20px;
        padding: 30px 20px;
    }
}
</style>
<script>
// Changement dynamique des matières lors de la sélection du niveau
const matieres = <?php echo json_encode($matieres); ?>;

function updateMatieres(selectId, matiereId) {
    const niveau = document.getElementById(selectId).value;
    const matiereSelect = document.getElementById(matiereId);
    matiereSelect.innerHTML = "";
    matieres[niveau].forEach(m => {
        const opt = document.createElement("option");
        opt.value = m;
        opt.text = m;
        matiereSelect.appendChild(opt);
    });
}

// Fondu d’entrée
window.addEventListener('load', () => {
    document.body.classList.add('visible');
});

// Navbar au scroll
const nav = document.getElementById('nav_bar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Menu burger
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

// Smooth scroll
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
</script>
</head>

<body>

<!-- Barre de navigation ajoutée -->
<nav class="barre_navigation" id="nav_bar">
    <div class="logo">Saim</div>

    <div class="menu_navigation" id="menu">
        <a href="#" class="actif">Admin</a>
        <a href="acceuil.html">Acceuil</a>
    </div>

    <div class="bouton_menu" id="bouton_menu">
        <span class="barre"></span>
        <span class="barre"></span>
        <span class="barre"></span>
    </div>
</nav>

<div class="container">
    <h2> Ajouter un PDF</h2>
     <a href="index.php"><button class="retour">← Retour</button></a>
    <?php if ($message): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">

        <label>Type</label>
        <select name="type">
            <option value="cours">Cours</option>
            <option value="exercice">Exercice</option>
        </select>

        <label>Niveau</label>
        <select name="niveau" id="niveau_upload" onchange="updateMatieres('niveau_upload','matiere_upload')">
            <?php foreach($matieres as $niv => $m) echo "<option value='$niv'>$niv</option>"; ?>
        </select>

        <label>Matière</label>
        <select name="matiere" id="matiere_upload">
            <?php foreach($matieres['l1'] as $m) echo "<option value='$m'>$m</option>"; ?>
        </select>

        <label>Année</label>
        <select name="annee">
            <option value="2024_2025">2024-2025</option>
            <option value="2025_2026">2025-2026</option>
        </select>

        <label>Fichier PDF</label>
        <input type="file" name="pdf" accept="application/pdf" required>

        <button type="submit">Uploader</button>
    </form>
</div>


<script>
updateMatieres('niveau_upload','matiere_upload');
updateMatieres('niveau_search','matiere_search');
</script>
</body>
</html>
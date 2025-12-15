<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ================= CONNEXION BD =================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=saim;charset=utf8",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// ================= LISTE DES MATIERES =================
$matieres = [
    "l1" => [
        "Architecture de l'ordinateur et Historique de l'informatique",
        "Systèmes d'exploitation",
        "Introduction au Web",
        "Arthimétique et nombres",
        "Analyse et algèbres 1",
        "Statistique 1",
        "Bureautique",
        "Internet 1",
        "Français général 1",
        "Anglais général 1",
        "Comptabilité générale",
        "Structures des données et algorythmes fondamentaux",
        "Introduction aux bases de données",
        "Introduction à la programmation",
        "Typologie et topologie réseaux",
        "Administration et sécurité réseaux",
        "Organisation des entreprises",
        "Système d'information",
        "Français général 2",
        "Anglais général 2",
        "Méthodes d'analyse"
    ],
    "l2" => [
        "Recherches opérationnelles",
        "Analyse et algèbre 2",
        "Statistique 2",
        "Concepts et mise en oeuvre réseaux",
        "Maintenance et configuration informatique",
        "Méthode MERISE",
        "Programation procédurale",
        "Intelligence Artificielle",
        "Français spécialisé 1",
        "Anglais spécialisé 1",
        "Gestion des stocks",
        "Introduction à la sécurité des réseaux",
        "Administration et language SQL serveur",
        "Language PHP et MY SQL",
        "Programmation orientée objet",
        "Développement d'application en VB",
        "Développement d'application en Delphi",
        "Français spécialisé 2",
        "Anglais spécialisé 2",
        "EC42 Gestion des Ressources Humaines"
    ]
];

// ================= TRAITEMENT RECHERCHE =================
$resultats = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'] ?? 'all'; // "cours" | "exercice" | "all"
    $niveau = $_POST['niveau'] ?? '';
    $matiere = $_POST['matiere'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $recherche = $_POST['recherche'] ?? '';

    if ($recherche) {
        // Recherche globale par mot clé dans les deux tables
        $sql = "
            SELECT 'cours' as type, titre, fichier_pdf FROM cours WHERE titre LIKE ?
            UNION ALL
            SELECT 'exercice' as type, titre, fichier_pdf FROM exercices WHERE titre LIKE ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$recherche%", "%$recherche%"]);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Filtrage par type / niveau / matière / année
        if ($type === 'all') {
            // Tous types
            $sql = "
                SELECT 'cours' as type, titre, fichier_pdf FROM cours WHERE niveau=? AND matiere=?
                UNION ALL
                SELECT 'exercice' as type, titre, fichier_pdf FROM exercices WHERE niveau=? AND matiere=?
            ";
            $params = [$niveau, $matiere, $niveau, $matiere];
            if ($annee) {
                $sql = "
                    SELECT 'cours' as type, titre, fichier_pdf FROM cours WHERE niveau=? AND matiere=? AND annee=?
                    UNION ALL
                    SELECT 'exercice' as type, titre, fichier_pdf FROM exercices WHERE niveau=? AND matiere=? AND annee=?
                ";
                $params = [$niveau, $matiere, $annee, $niveau, $matiere, $annee];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $table = ($type === 'cours') ? 'cours' : 'exercices';
            $sql = "SELECT titre, fichier_pdf FROM $table WHERE niveau=? AND matiere=?";
            $params = [$niveau, $matiere];
            if ($annee) {
                $sql .= " AND annee=?";
                $params[] = $annee;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// ================= TELECHARGEMENT ==================
if (isset($_GET['download'])) {
    $fichier = basename($_GET['download']);
    $path = __DIR__ . "/uploads/" . $fichier;

    if (!file_exists($path)) die("Fichier introuvable !");

    // Historique
    $titre = $_GET['titre'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO historique_download (titre, fichier, ip_user) VALUES (?, ?, ?)");
    $stmt->execute([$titre,$fichier,$ip]);

    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"$fichier\"");
    readfile($path);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard PDF</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ===== Reset & Fonts (adapté au thème Saim) ===== */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
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

h1 {
    text-align: center;
    margin: 20px 0;
    font-weight: 700;
    color: #000;
}

/* ===== Container (adapté avec gradient et border) ===== */
#ultime {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

#form {
    flex: 1 1 350px;
    background: linear-gradient(to right, #e0e0e0, #ffffff);
    padding: 40px;
    border-radius: 12px;
    border: 2px solid #000;
}

/* ===== Menue (boutons avec style btn Saim) ===== */
#menue {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

#menue button {
    flex: 1 1 calc(33% - 10px);
    padding: 14px 20px;
    border: 2px solid #000;
    background: transparent;
    color: #000;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: color 0.4s ease;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    z-index: 1;
}

#menue button::before {
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

#menue button:hover::before {
    height: 100%;
}

#menue button:hover {
    color: white;
}

/* ===== Sections ===== */
.section {
    display: none;
}

.section label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
    color: #111;
}

.section select,
.section input {
    width: 100%;
    padding: 12px;
    border: 2px solid #000;
    border-radius: 6px;
    background: transparent;
    color: #000;
    margin-bottom: 10px;
    font-family: inherit;
}

.section button {
    width: 100%;
    padding: 14px;
    border: 2px solid #000;
    background: transparent;
    color: #000;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: color 0.4s ease;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.section button::before {
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

.section button:hover::before {
    height: 100%;
}

.section button:hover {
    color: white;
}

.section h2 {
    margin-bottom: 15px;
    font-weight: 700;
    color: #000;
}

/* ===== Retour (style btn adapté, avec bg clair) ===== */
.retour {
    border: 2px solid #000;
    background: transparent;
    color: #000;
    margin-bottom: 15px;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: color 0.4s ease;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    display: inline-block;
    z-index: 1;
}

.retour::before {
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

.retour:hover::before {
    height: 100%;
}

.retour:hover {
    color: white;
}

/* ===== Affichage PDF (adapté avec gradient et border) ===== */
#affichage {
    flex: 2 1 600px;
    background: linear-gradient(to right, #e0e0e0, #ffffff);
    padding: 40px;
    border-radius: 12px;
    border: 2px solid #000;
    max-height: 80vh;
    overflow-y: auto;
}

#affichage .card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    margin-bottom: 12px;
    border: 2px solid #000;
    border-radius: 8px;
    transition: box-shadow 0.3s ease;
    background: #ffffff;
}

#affichage .card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

#affichage .card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #000;
    flex: 1;
    margin-right: 10px;
}

#affichage .card a {
    padding: 10px 20px;
    background: #000;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s ease;
    white-space: nowrap;
}

#affichage .card a:hover {
    background: #333;
}

/* ===== Scrollbar (minimaliste) ===== */
#affichage::-webkit-scrollbar {
    width: 8px;
}

#affichage::-webkit-scrollbar-thumb {
    background: #000;
    border-radius: 4px;
}

#affichage::-webkit-scrollbar-track {
    background: #f5f5f5;
}

/* ===== Responsive (basique pour cohérence) ===== */
@media (max-width: 868px) {
    #ultime {
        flex-direction: column;
        padding: 20px 10px;
    }

    #form, #affichage {
        flex: none;
        width: 100%;
    }

    #menue button {
        flex: 1 1 calc(50% - 10px);
    }
}

@media (max-width: 480px) {
    #menue button {
        flex: 1 1 100%;
    }
}

/* Styles pour la barre de navigation (ajoutés) */
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

/* Ajustements pour le contenu (espace pour nav fixed) */
h1 {
    margin-top: 120px; /* Espace pour la nav */
    text-align: center;
    padding: 20px 5%;
}

#ultime {
    padding-top: 20px; /* Ajustement mineur pour cohérence */
}

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

    h1 {
        margin-top: 140px; /* Ajusté pour mobile */
    }
}
</style>

</head>
<body>

<!-- Barre de navigation ajoutée -->
<nav class="barre_navigation" id="nav_bar">
    <div class="logo">Sam</div>

    <div class="menu_navigation" id="menu">
        <a href="#" class="actif">Filtre</a>
        <a href="acceuil.html">Accueil</a>
    </div>

    <div class="bouton_menu" id="bouton_menu">
        <span class="barre"></span>
        <span class="barre"></span>
        <span class="barre"></span>
    </div>
</nav>

<h1>Apprenez différemment, réussissez durablement</h1>

<div id="ultime">

    <div id="form">

        <div id="menue">
            <button onclick="openSection('search')">Faire une recherche</button>
            <button onclick="openSection('formcours')">Voir les cours</button>
            <button onclick="openSection('formexo')">Pour s'entraîner</button>
        </div>

        <!-- Recherche -->
        <div id="search" class="section">
            <button class="retour" onclick="goBack()">← Retour</button>
            <form method="POST">
                <label>Recherche globale</label>
                <input type="text" name="recherche" placeholder="Nom du fichier...">

                <label>Type</label>
                <select name="type">
                    <option value="all">Tous</option>
                    <option value="cours">Cours</option>
                    <option value="exercice">Exercice</option>
                </select>

                <label>Niveau</label>
                <select name="niveau" id="niveau_search" onchange="updateMatieres('niveau_search','matiere_search')">
                    <?php foreach($matieres as $niv=>$m) echo "<option value='$niv'>$niv</option>"; ?>
                </select>

                <label>Matière</label>
                <select name="matiere" id="matiere_search">
                    <?php foreach($matieres['l1'] as $m) echo "<option value='$m'>$m</option>"; ?>
                </select>

                <label>Année scolaire</label>
                <select name="annee">
                    <option value="">Toutes</option>
                    <option value="2024_2025">2024-2025</option>
                    <option value="2025_2026">2025-2026</option>
                </select>

                <button type="submit">Rechercher</button>
            </form>
        </div>

        <!-- Formulaire Cours -->
        <form id="formcours" class="section" method="POST">
            <button class="retour" onclick="goBack()">← Retour</button>
            <h2>Des cours personnalisés pour révéler ton potentiel</h2>

            <select name="niveau" id="niveau_formcours" onchange="updateMatieres('niveau_formcours','matiere_formcours')">
                <option value="l1">Première année</option>
                <option value="l2">Deuxième année</option>
                <option value="l3">Troisième année</option>
               
            </select>

            <label>Matière :</label>
            <select name="matiere" id="matiere_formcours"></select>

            <label>Année scolaire :</label>
            <select name="annee">
                <option value="2024_2025">2024-2025</option>
                <option value="2025_2026">2025-2026</option>
            </select>

            <select name="type" hidden>
                <option value="cours" selected>Cours</option>
            </select>

            <button type="submit" id="butsou">Soumettre</button>
        </form>

        <!-- Formulaire Exo -->
        <form id="formexo" class="section" method="POST">
            <button class="retour" onclick="goBack()">← Retour</button>
            <h2>Des exercices pour t'entraîner efficacement</h2>

            <select name="niveau" id="niveau_formexo" onchange="updateMatieres('niveau_formexo','matiere_formexo')">
                <option value="l1">Première année</option>
                <option value="l2">Deuxième année</option>
            </select>

            <label>Matière :</label>
            <select name="matiere" id="matiere_formexo"></select>

            <label>Année scolaire :</label>
            <select name="annee">
                <option value="2024_2025">2024-2025</option>
                <option value="2025_2026">2025-2026</option>
            </select>

            <select name="type" hidden>
                <option value="exercice" selected>Exercice</option>
            </select>

            <button type="submit" id="butsou">Soumettre</button>
        </form>

    </div>

    <div id="affichage">
        <?php if($resultats): foreach($resultats as $r): ?>
        <div class='card'>
            <h3><?= htmlspecialchars($r['titre']) ?></h3>
            <a href='?download=<?= urlencode($r['fichier_pdf']) ?>&titre=<?= urlencode($r['titre']) ?>'> Télécharger PDF</a>
        </div>
        <?php endforeach; else: ?>
        <p style='opacity:.6'>Aucun résultat</p>
        <?php endif; ?>
    </div>

</div>

<script>
// Fondu d’entrée pour body
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

// Smooth scroll (pour cohérence)
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

const matieres = <?php echo json_encode($matieres); ?>;

function updateMatieres(niveauSelectId, matiereSelectId) {
    const niveau = document.getElementById(niveauSelectId).value;
    const matiereSelect = document.getElementById(matiereSelectId);
    matiereSelect.innerHTML = "";
    if(matieres[niveau]) {
        matieres[niveau].forEach(m => {
            const opt = document.createElement("option");
            opt.value = m;
            opt.text = m;
            matiereSelect.appendChild(opt);
        });
    }
}

// Initialisation
updateMatieres('niveau_formcours','matiere_formcours');
updateMatieres('niveau_formexo','matiere_formexo');
updateMatieres('niveau_search','matiere_search');

// Gestion des sections
function openSection(id) {
    document.querySelectorAll('.section').forEach(s=>s.style.display='none');
    document.getElementById(id).style.display='block';
}
function goBack() {
    document.querySelectorAll('.section').forEach(s=>s.style.display='none');
}
</script>

</body>
</html>
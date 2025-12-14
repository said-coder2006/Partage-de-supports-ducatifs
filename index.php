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

// ================= TELECHARGEMENT =================
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

<link rel="stylesheet" href="style2.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>

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

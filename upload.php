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
<title>Gestion PDF</title>
<style>
body { font-family: Poppins, sans-serif; background:#f5f5f5; padding:20px;}
.container { max-width: 600px; margin:auto; background:white; padding:20px; border-radius:12px;}
input, select, button { width:100%; padding:10px; margin:10px 0;}
button { background:#222; color:white; border:none; cursor:pointer; border-radius:6px;}
.card { padding:10px; border-bottom:1px solid #ccc; display:flex; justify-content:space-between;}
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
</script>
</head>

<body>

<div class="container">
    <h2> Ajouter un PDF</h2>
     <a href="acceuil.html"><button class="retour">← Retour</button></a>
    <?php if ($message): ?><p><?= $message ?></p><?php endif; ?>
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

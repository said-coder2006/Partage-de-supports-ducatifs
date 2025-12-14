function openSection(id) {
    // On cache toutes les sections
    const sections = document.querySelectorAll('.section');
    sections.forEach(s => s.style.display = 'none');

    // Afficher uniquement la section cliquée
    document.getElementById(id).style.display = 'flex';

    // Afficher le menu uniquement avant sélection
    document.getElementById('menue').style.display = 'none';
}


function openSection(id) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(s => s.style.display = 'none');

    document.getElementById(id).style.display = 'flex';
    document.getElementById('menue').style.display = 'none';
}

// Bouton retour
function goBack() {
    const sections = document.querySelectorAll('.section');
    sections.forEach(s => s.style.display = 'none');

    document.getElementById('menue').style.display = 'flex';
}
// Bouton retour
function goBack() {
    const sections = document.querySelectorAll('.section');
    sections.forEach(s => s.style.display = 'none');

    document.getElementById('menue').style.display = 'flex';
}

// Dictionnaire des matières par niveau
const matieres = {
    l1: ["Architecture de l'ordinateur et Historique de l'informatique", "Systèmes d'exploitation ", "Introduction au Web","Arthimétique et nombres","Analyse et algèbres 1","Statistique 1","Bureautique","Internet 1","Français général 1","Anglais général 1","Comptabilité générale","Structures des données et algorythmes fondamentaux","Introduction aux bases de données ","introduction à la programation","Typologie et topologie réseaux","Administration et sécurité réseaux","Organisation des entreprises","Système d'information","Français général 2","Anglais général 2","Méthodes d'analyse"],
    l2: ["Recherches opérationnelles", "Analyse et algèbre 2","Statistique 2","Concepts et mise en oeuvre réseaux","Maintenance et configuration informatique","Méthode MERISE","Programation procédurale","Intelligence Artificielle","Français spécialié 1","Anglais spécialisé 1","Gestion des stocks","Introduction à la sécurité des réseaux","Administration et language SQL serveur","Language PHP et MY SQL","Programmation orientée objet","Développement d'application en VB","Développement d'application en Delphi","Français spécialisé 2","Anglais spécialisé 2","EC42 Gestion des Ressources Humaines"],
};

// Fonction pour mettre à jour le select matière
function updateMatieres(selectNiveau, selectMatiere) {
    const niveau = selectNiveau.value;  // récupère le niveau choisi
    selectMatiere.innerHTML = ""; // vide les options existantes

    matieres[niveau].forEach(matiere => {
        const option = document.createElement("option");
        option.value = matiere;
        option.textContent = matiere;
        selectMatiere.appendChild(option);
    });
}

// Sélection des formulaires
const forms = document.querySelectorAll("#formcours, #formexo");
forms.forEach(form => {
    const selectNiveau = form.querySelector("select[name='niveau']");
    const inputMatiere = form.querySelector("input[placeholder^='ex']"); // si tu veux garder input
    // ou créer un select pour les matières dynamiques
    const selectMatiere = document.createElement("select");
    selectMatiere.name = "matiere";
    inputMatiere.replaceWith(selectMatiere); // remplace l'input par le select

    // remplir initialement
    updateMatieres(selectNiveau, selectMatiere);

    // mettre à jour quand le niveau change
    selectNiveau.addEventListener("change", () => updateMatieres(selectNiveau, selectMatiere));
});

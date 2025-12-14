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

// Texte dynamique
const phrases = [
    "Apprenez facilement",
    "Progressez rapidement !",
    "Étudiez efficacement"
];
const textElement = document.getElementById("changing-text");
let phraseIndex = 0;
let charIndex = 0;
let isDeleting = false;

function type() {
    const currentPhrase = phrases[phraseIndex];
    const displayedText = currentPhrase.substring(0, charIndex);
    textElement.textContent = displayedText;

    if (!isDeleting && charIndex < currentPhrase.length) {
        charIndex++;
        setTimeout(type, 80);
    } else if (isDeleting && charIndex > 0) {
        charIndex--;
        setTimeout(type, 90);
    } else {
        isDeleting = !isDeleting;
        if (!isDeleting) {
            phraseIndex = (phraseIndex + 1) % phrases.length;
        }
        setTimeout(type, 1000);
    }
}

type();
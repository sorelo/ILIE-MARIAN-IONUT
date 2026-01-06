document.addEventListener("DOMContentLoaded", () => {
    // Referinte catre elementele principale ale paginii
    const body = document.body;
    const main = document.querySelector('main'); 

    // --- 1. DETECTIE LOGIN SI ROL ---
    // Cautam div-ul marker pus de PHP pentru a sti daca cineva este logat
    const marker = document.getElementById('user-logged-in');
    const isLoggedIn = marker !== null;
    const userRole = isLoggedIn ? marker.getAttribute('data-rol') : null;
    
    // Stabilim textul si link-ul pentru butonul de login/profil
    let authLabel = "Autentificare";
    let authHref = "login.php";

    if (isLoggedIn) {
        if (userRole === 'admin') {
            authLabel = "Admin";
            authHref = "admin.php"; // Administratorul merge la panoul de control
        } else {
            authLabel = "Profil";
            authHref = "login.php"; // Clientul merge la pagina sa de profil
        }
    }

    // --- 2. CONFIGURARE MENIU ---
    // Lista de pagini pentru bara de navigare
    const menuItems = [
        { name: "Acasă", href: "index.php" },
        { name: "Rezervă Acum", href: "rezerva.php" },
        { name: "Flotă", href: "flota.php" },
        { name: "Oferte", href: "oferte.php" },
        { name: "Contact", href: "contact.php" },
        { name: "Despre noi", href: "despre.php" },
        { name: authLabel, href: authHref }
    ];

    // Identificam pagina curenta pentru a o marca in meniu
    const currentPage = window.location.pathname.split("/").pop() || "index.php";

    let navHTML = '<nav>';
    menuItems.forEach(item => {
        // Adaugam clasa active-nav daca link-ul coincide cu pagina pe care suntem
        const isActive = item.href === currentPage ? 'class="active-nav"' : '';
        navHTML += `<a href="${item.href}" ${isActive}>${item.name}</a>`;
    });
    navHTML += '</nav>';

    // --- 3. CONSTRUCTIE PAGINA (Header, Aside, Footer) ---
    // Generam header-ul dinamic daca acesta nu exista deja in HTML
    if (!document.querySelector('header')) {
        const header = document.createElement('header');
        header.innerHTML = `<h1>Rent-A-Car</h1>${navHTML}`;
        if(main) body.insertBefore(header, main);
        else body.prepend(header);
    }

    // Generam sectiunea laterala (Aside) cu informatii utile
    if (main && !document.querySelector('aside')) {
        const aside = document.createElement('aside');
        aside.innerHTML = `
            <h3>De ce noi?</h3>
            <ul>
                <li>Flotă verificată</li>
                <li>Prețuri corecte</li>
                <li>Asistență 24/7</li>
            </ul>
            <h3>Contact rapid</h3>
            <p>📞 +40 700 123 456</p>
        `;
        body.appendChild(aside);
    }

    // Generam footer-ul cu anul curent extras automat
    if (!document.querySelector('footer')) {
        const footer = document.createElement('footer');
        footer.innerHTML = `<p>&copy; ${new Date().getFullYear()} Rent-A-Car. Toate drepturile rezervate.</p>`;
        body.appendChild(footer);
    }

    // --- 4. BUTON BACK TO TOP ---
    // Cream butonul care ne trimite inapoi la inceputul paginii
    const scrollBtn = document.createElement('button');
    scrollBtn.id = "scrollTopBtn";
    scrollBtn.innerHTML = "&#8679;"; 
    scrollBtn.title = "Înapoi sus";
    body.appendChild(scrollBtn);

    // Afisam sau ascundem butonul in functie de cat de mult a derulat utilizatorul
    window.onscroll = function() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            scrollBtn.style.display = "block";
        } else {
            scrollBtn.style.display = "none";
        }
    };

    // Adaugam efectul de scroll lin (smooth) la apasarea butonului
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
<?php 
// Pornim sesiunea pentru a putea identifica utilizatorul pe parcursul navigarii
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Includem conexiunea la baza de date
require 'db.php'; 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rent-a-Car - Despre noi</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>

<?php 
// Markerul pentru utilizator logat: trimite ID-ul si rolul catre script.js
// Acest element este invizibil pentru vizitator (display:none)
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
    <h2>Cine suntem?</h2>
    <p>Fondată în 2015, Rent-A-Car a pornit din dorința de a oferi servicii de închirieri auto simple, transparente și de încredere. Misiunea noastră este să asigurăm fiecărui client o experiență plăcută.</p>
    
    <h3>Valorile noastre</h3>
    
    <div class="value-group">
        <h4>⭐ Calitate</h4>
        <p>Toate mașinile din flota noastră sunt noi și verificate periodic pentru a garanta siguranța și confortul dumneavoastră.</p>
    </div>
    
    <div class="value-group">
        <h4>💎 Transparență</h4>
        <p>Prețurile noastre sunt clare, fără costuri ascunse. Ceea ce vedeți este ceea ce plătiți.</p>
    </div>

    <div class="value-group">
        <h4>🚀 Flexibilitate</h4>
        <p>Ne adaptăm nevoilor dumneavoastră, oferind soluții personalizate pentru orice tip de călătorie.</p>
    </div>
</main>

</body>
</html>
<?php 
// Verificam daca sesiunea este deja pornita; necesar pentru a pastra statusul de login
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Includem fisierul de conectare la baza de date (necesar pentru consistenta)
require 'db.php'; 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rent-a-Car - Oferte Speciale</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js?v=2" defer></script>
</head>
<body>

<?php 
// Div invizibil care functioneaza ca un semnal pentru script.js
// Daca acest element exista, JavaScript va sti ca utilizatorul este logat si ce rol are
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
    <h2>Ofertele noastre speciale</h2>
    
    <div class="offer">
        <h3>🎉 Ofertă Weekend</h3>
        <p>Închiriază orice mașină de vineri până luni și primești o <strong>reducere de 15%</strong>!</p>
    </div>
    
    <div class="offer">
        <h3>🏢 Închiriere pe termen lung</h3>
        <p>Ai nevoie de o mașină pentru mai mult de 30 de zile? Contactează-ne pentru o ofertă personalizată cu prețuri speciale.</p>
    </div>

    <div class="offer">
        <h3>⏳ Reducere Early Booking</h3>
        <p>Rezervă mașina cu cel puțin 60 de zile în avans și beneficiezi de <strong>10% reducere</strong> la tariful standard.</p>
    </div>
</main>

</body>
</html>
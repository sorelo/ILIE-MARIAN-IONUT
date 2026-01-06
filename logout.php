<?php
// Pornim sesiunea pentru a avea acces la datele care trebuie sterse
session_start();

// Stergem toate valorile salvate in variabilele de sesiune (ex: nume, rol, id)
session_unset();

// Distrugem sesiunea complet de pe server
session_destroy();

// Redirectionam vizitatorul inapoi la pagina principala (Acasă)
header("Location: index.php");

// Oprim executia codului pentru a asigura finalizarea redirectionarii
exit();
?>
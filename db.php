<?php
// Configurare parametri conexiune – adaptati pentru containerele Docker
$host = 'mysql'; // Numele serviciului definit in docker-compose
$port = 3306;
$db = 'studenti';
$user = 'user'; // Utilizatorul bazei de date
$pass = 'password'; // Parola bazei de date
$charset = 'utf8mb4';

// DSN (Data Source Name) – stringul care contine detaliile de localizare a bazei de date
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// Optiuni pentru obiectul PDO: 
// 1. Activam raportarea erorilor prin exceptii
// 2. Setam modul de preluare a datelor ca array asociativ
// 3. Dezactivam emularea interogarilor pregatite pentru securitate sporita
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Incercam crearea unei noi instante PDO pentru conectare
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Daca apare o eroare, oprim executia si afisam mesajul de eroare tehnic
    http_response_code(500);
    echo "Eroare conectare DB: " . $e->getMessage();
    exit;
}
?>
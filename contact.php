<?php 
// Pornirea sesiunii pentru a pastra utilizatorul logat intre pagini
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Conexiunea la baza de date
require 'db.php'; 
$mesajStatus = "";

// Verificam daca s-a apasat butonul de trimitere (metoda POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nume = $_POST['nume'];
    $email = $_POST['email'];
    $text = $_POST['mesaj'];

    try {
        // Pregatim interogarea pentru a insera mesajul in baza de date
        $stmt = $pdo->prepare("INSERT INTO contact_messages (nume, email, mesaj) VALUES (:n, :e, :m)");
        $stmt->execute(['n' => $nume, 'e' => $email, 'm' => $text]);
        
        // Daca insertia a reusit, setam mesajul de succes
        $mesajStatus = "Mesajul a fost trimis! Te vom contacta curând.";
    } catch (PDOException $e) {
        // In caz de eroare, afisam motivul tehnic al esecului
        $mesajStatus = "Eroare la trimitere: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rent-a-Car - Contact</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>

<?php 
// Generam un marker invizibil daca utilizatorul este logat, folosit de meniul din script.js
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
    <h2>Contactează-ne</h2>
    
    <?php 
    // Daca variabila mesajStatus are continut, afisam caseta de notificare
    if ($mesajStatus): 
    ?>
        <div style="background-color: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $mesajStatus; ?>
        </div>
    <?php endif; ?>

    <div class="contact-container">
        <div>
            <form method="POST">
                <div class="form-group">
                    <label>Nume:</label>
                    <input type="text" name="nume" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Mesaj:</label>
                    <textarea name="mesaj" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit">Trimite</button>
                </div>
            </form>
        </div>
        <div>
            <h3>Info Contact</h3>
            <p>Adresă: Str. Studenților nr 1</p>
            <p>Tel: 0700 123 456</p>
        </div>
    </div>
</main>
</body>
</html>
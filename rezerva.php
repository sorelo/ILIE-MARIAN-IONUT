<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
require 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rezervare</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>

<?php 
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
    <h2>Rezervă un model</h2>
    <p>Utilizator: <strong><?php echo htmlspecialchars($_SESSION['nume']); ?></strong></p>
    
    <form action="procesare_rezervare.php" method="POST">
        <input type="hidden" name="id_user" value="<?php echo $_SESSION['user_id']; ?>">
        
        <div class="form-group">
            <label for="model_input">Alege Modelul Dorit:</label>
            <select name="model_ales" id="model_input" style="width:100%; padding:8px;" required>
                <option value="">-- Selectează un model --</option>
                <?php
                try {
                    $sql = "SELECT DISTINCT marca, model, pret_zi FROM cars WHERE status='disponibil' ORDER BY pret_zi ASC";
                    $stmt = $pdo->query($sql);
                    while ($row = $stmt->fetch()) {
                        $valoare = $row['marca'] . '|' . $row['model'];
                        $text = $row['marca'] . ' ' . $row['model'] . ' (' . $row['pret_zi'] . ' RON/zi)';
                        $selected = (isset($_GET['model_selectat']) && urldecode($_GET['model_selectat']) == $valoare) ? 'selected' : '';
                        echo "<option value='{$valoare}' $selected>{$text}</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option>Eroare încărcare</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Data Preluare:</label>
            <input type="date" name="data_start" required>
        </div>
        <div class="form-group">
            <label>Data Returnare:</label>
            <input type="date" name="data_sfarsit" required>
        </div>

        <div class="form-group">
            <button type="submit">Calculează și Rezervă</button>
        </div>
    </form>
</main>
</body>
</html>
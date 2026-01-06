<?php 
// Pornim sesiunea pentru a putea afisa corect meniul de utilizator (Profil/Admin)
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Includem conexiunea la baza de date
require 'db.php'; 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Flota noastră</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>

<?php 
// Marker invizibil pentru script.js: permite adaptarea meniului la statusul logarii
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
    <h2>Flota noastră (Modele Disponibile)</h2>
    
    <?php
    try {
        // Interogare SQL avansata cu GROUP BY:
        // Grupam masinile dupa marca si model pentru a nu afisa aceeasi masina de mai multe ori.
        // Folosim COUNT pentru a vedea cate unitati din acel model sunt disponibile.
        $sql = "SELECT marca, model, MIN(clasa) as clasa, MIN(pret_zi) as pret_zi, MAX(imagine) as imagine, COUNT(*) as stoc 
                FROM cars 
                WHERE status = 'disponibil'
                GROUP BY marca, model 
                ORDER BY pret_zi ASC";
        
        $stmt = $pdo->query($sql);

        // Verificam daca baza de date a returnat modele disponibile
        if ($stmt->rowCount() > 0) {
            // Parcurgem fiecare grup de masini gasit
            while ($row = $stmt->fetch()) {
                // Setam o imagine de rezerva (placeholder) daca nu exista poza in baza de date
                $imgSrc = !empty($row['imagine']) ? $row['imagine'] : 'https://via.placeholder.com/300x200';
                $titlu = htmlspecialchars($row['marca'] . ' ' . $row['model']);
                
                // Pregatim datele pentru link-ul de rezervare (folosim model|marca ca identificator)
                $valoareModel = htmlspecialchars($row['marca'] . '|' . $row['model']);
                ?>
                
                <div class="car-listing">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="car-image" onerror="this.src='https://via.placeholder.com/300x200'">
                    <div>
                        <h3><?php echo $titlu; ?></h3>
                        <p><strong>Clasa:</strong> <?php echo htmlspecialchars($row['clasa']); ?></p>
                        <p><strong>Disponibile:</strong> <?php echo $row['stoc']; ?> buc.</p>
                        <p style="font-size: 1.2em; color: green;"><strong>Preț: <?php echo $row['pret_zi']; ?> RON / zi</strong></p>
                        
                        <a href="rezerva.php?model_selectat=<?php echo urlencode($valoareModel); ?>" 
                           style="display:inline-block; background-color:#4CAF50; color:white; padding:8px 15px; text-decoration:none; border-radius:4px;">
                           Alege acest model ➔
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            // Mesaj afisat daca nicio masina nu are statusul 'disponibil'
            echo "<p>Nu există modele disponibile momentan.</p>";
        }
    } catch (PDOException $e) {
        // Capturam erorile de conexiune sau sintaxa SQL
        echo "<div style='color:red'>Eroare SQL: " . $e->getMessage() . "</div>";
    }
    ?>
</main>
</body>
</html>
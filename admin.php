<?php
// Includem conexiunea la baza de date
require 'db.php';

// Verificam statusul sesiunii pentru a gestiona logarea
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// VERIFICARE ACCES: Permitem intrarea doar daca utilizatorul este logat si este admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    die("<div style='text-align:center; margin-top:50px;'>ACCES INTERZIS. <a href='login.php'>Mergi la Login</a></div>");
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Stiluri specifice pentru tabelul administrativ */
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        .admin-table th, .admin-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .admin-table th { background-color: #4CAF50; color: white; }
        .admin-table tr:nth-child(even) { background-color: #f9f9f9; }
        .header-admin { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-logout { background-color: #e74c3c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<main>
    <div class="header-admin">
        <h2>Panou Administrare</h2>
        <div>
            Salut, <strong><?php echo htmlspecialchars($_SESSION['nume']); ?></strong>! 
            <a href="logout.php" class="btn-logout">Deconectare</a>
        </div>
    </div>

    <h3>Istoric Rezervări</h3>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Mașină</th>
                <th>Perioada</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                // Interogare SQL complexa cu JOIN pentru a aduce date din 3 tabele diferite
                $sql = "SELECT r.id_inchiriere, u.nume, c.marca, c.model, r.data_start, r.data_sfarsit, r.pret_total, r.status 
                        FROM rentals r
                        JOIN users u ON r.id_user = u.id_user
                        JOIN cars c ON r.id_masina = c.id_masina
                        ORDER BY r.id_inchiriere DESC";
                
                $stmt = $pdo->query($sql);

                // Bucla care parcurge rezultatele si le afiseaza in randuri de tabel
                if ($stmt->rowCount() > 0) {
                    while($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>" . $row['id_inchiriere'] . "</td>";
                        // Folosim htmlspecialchars pentru a afisa numele in siguranta
                        echo "<td>" . htmlspecialchars($row['nume']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['marca'] . " " . $row['model']) . "</td>";
                        echo "<td>" . $row['data_start'] . " <br> " . $row['data_sfarsit'] . "</td>";
                        echo "<td><strong>" . $row['pret_total'] . " RON</strong></td>";
                        
                        // Determinam culoarea textului in functie de statusul rezervarii
                        $color = ($row['status'] == 'activ') ? 'green' : (($row['status'] == 'anulat') ? 'red' : 'blue');
                        echo "<td style='color:$color; font-weight:bold;'>" . ucfirst($row['status']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Nu există rezervări.</td></tr>";
                }
            } catch (PDOException $e) {
                // Afisarea erorii in cazul in care conexiunea SQL esueaza
                echo "<tr><td colspan='6' style='color:red;'>Eroare: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <br>
    <a href="index.php" style="text-decoration: underline;">&larr; Înapoi la Site</a>
</main>

</body>
</html>
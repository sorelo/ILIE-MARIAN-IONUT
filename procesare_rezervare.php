<?php
// Includem conexiunea si pornim sesiunea pentru a identifica utilizatorul
require 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SECURITATE: Daca utilizatorul nu este logat, il trimitem la pagina de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initializam variabilele pentru mesajele de stare
$titlu = ""; $mesaj = ""; $tipMesaj = "";

// Verificam daca datele au fost trimise prin metoda POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Preluam datele din formular si le curatam (intval pentru ID)
    $id_user = intval($_POST['id_user']);
    $model_ales = $_POST['model_ales'] ?? ''; 
    $data_start = $_POST['data_start'] ?? '';
    $data_sfarsit = $_POST['data_sfarsit'] ?? '';

    // Validare de baza pentru campuri goale
    if (empty($model_ales) || empty($data_start) || empty($data_sfarsit)) {
        $titlu = "Eroare"; $mesaj = "Date incomplete."; $tipMesaj = "eroare";
    } else {
        // Desfacem sirul "Marca|Model" primit din select in doua variabile separate
        list($marca, $model) = explode('|', $model_ales);

        try {
            // Cautam o masina reala (ID) care corespunde marcii si modelului si este disponibila
            $sqlCar = "SELECT * FROM cars WHERE marca = :m AND model = :mo AND status = 'disponibil' ORDER BY RAND() LIMIT 1";
            $stmt = $pdo->prepare($sqlCar);
            $stmt->execute(['m' => $marca, 'mo' => $model]);
            $masina = $stmt->fetch();

            if (!$masina) {
                // Mesaj in cazul in care modelul a fost ocupat intre timp
                $titlu = "Ne pare rău";
                $mesaj = "Nu mai avem nicio mașină disponibilă din modelul <strong>$marca $model</strong> în acest moment.";
                $tipMesaj = "eroare";
            } else {
                $id_masina_reala = $masina['id_masina'];
                $pret_zi_base = floatval($masina['pret_zi']);

                // Calculam numarul de zile de inchiriere folosind obiecte de tip DateTime
                $start = new DateTime($data_start);
                $end = new DateTime($data_sfarsit);
                $today = new DateTime();
                
                if ($end < $start) {
                    throw new Exception("Data de returnare invalidă.");
                }
                
                $diff = $start->diff($end);
                $zile = $diff->days < 1 ? 1 : $diff->days; // Minim o zi de plata

                // LOGICA DE REDUCERI (Discount-uri)
                $discount_total = 0;
                $discount_messages = [];

                // 1. Reducere de Weekend (Vineri pana Luni)
                if ($start->format('N') == 5 && $end->format('N') == 1) {
                    $discount_total += 0.15;
                    $discount_messages[] = "Ofertă Weekend (-15%)";
                }

                // 2. Reducere Early Booking (rezervare facuta cu peste 60 de zile in avans)
                $days_until_start = $today->diff($start)->days;
                if ($days_until_start >= 60) {
                    $discount_total += 0.10;
                    $discount_messages[] = "Early Booking (-10%)";
                }

                // 3. Reducere Termen Lung (peste 30 de zile)
                if ($zile > 30) {
                    $discount_total += 0.20;
                    $discount_messages[] = "Long Term (-20%)";
                }

                // Calculul financiar final
                $pret_brut = $zile * $pret_zi_base;
                $suma_reducere = $pret_brut * $discount_total;
                $pret_final = $pret_brut - $suma_reducere;

                // SALVARE IN BAZA DE DATE
                // Inseram noua inregistrare in tabelul de inchirieri
                $sqlInsert = "INSERT INTO rentals (id_user, id_masina, data_start, data_sfarsit, pret_total, status) 
                              VALUES (:uid, :mid, :start, :end, :total, 'activ')";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    'uid' => $id_user,
                    'mid' => $id_masina_reala,
                    'start' => $data_start,
                    'end' => $data_sfarsit,
                    'total' => $pret_final
                ]);

                // Actualizam statusul masinii in tabelul de masini pentru a nu mai aparea ca disponibila
                $pdo->prepare("UPDATE cars SET status='inchiriat' WHERE id_masina=?")->execute([$id_masina_reala]);

                // Pregatim afisarea rezultatului catre utilizator
                $titlu = "Rezervare Confirmată!";
                $msg_reduceri = empty($discount_messages) ? "Nicio reducere aplicată." : implode(", ", $discount_messages);
                
                $mesaj = "
                    <p>Ai rezervat un <strong>$marca $model</strong> (An: {$masina['an_fabricatie']}).</p>
                    <p>Sistemul a alocat automat mașina cu numărul: <strong>{$masina['numar_inmatriculare']}</strong>.</p>
                    <hr>
                    <ul style='text-align:left; display:inline-block;'>
                        <li>Perioada: $data_start -> $data_sfarsit ($zile zile)</li>
                        <li>Preț Standard: $pret_brut RON</li>
                        <li><strong>Reduceri aplicate:</strong> <span style='color:green'>$msg_reduceri</span></li>
                    </ul>
                    <h2 style='color:#4CAF50'>Total Final: $pret_final RON</h2>
                    <br><a href='index.php' class='btn'>Înapoi la Acasă</a>
                ";
                $tipMesaj = "succes";
            }
        } catch (Exception $e) {
            // Prindem eventualele erori (ex: date gresite) si le afisam
            $titlu = "Eroare"; $mesaj = $e->getMessage(); $tipMesaj = "eroare";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Status Rezervare</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
  <style>
      /* Stiluri locale pentru caseta de status a operatiunii */
      .status-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 20px auto; text-align: center; }
      .succes h2 { color: #4CAF50; } .eroare h2 { color: #e74c3c; }
      .btn { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display:inline-block; margin-top:15px;}
  </style>
</head>
<body>
<main>
    <div class="status-box <?php echo $tipMesaj; ?>">
        <h2><?php echo $titlu; ?></h2>
        <div><?php echo $mesaj; ?></div>
    </div>
</main>
</body>
</html>
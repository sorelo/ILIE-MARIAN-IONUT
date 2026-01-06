<?php 
// Pornim sesiunea pentru a verifica daca utilizatorul este logat
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Includem fisierul de configurare pentru conexiunea la baza de date
require 'db.php'; 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rent-a-Car - Acasa</title>
  <link rel="stylesheet" href="style.css">
  <script src="script.js?v=2" defer></script>
  
  <style>
      /* Stiluri pentru sectiunea principala de bun venit */
      .hero-section { text-align: center; padding: 20px; margin-bottom: 30px; }
      
      /* Cardul special pentru Recomandarea Zilei */
      .model-zilei-card {
          background: #fff;
          border-radius: 12px;
          box-shadow: 0 5px 15px rgba(0,0,0,0.1);
          max-width: 750px;
          margin: 0 auto;
          overflow: hidden;
          border: 2px solid #4CAF50;
          position: relative;
          display: flex;
          align-items: stretch;
      }

      /* Eticheta portocalie din coltul cardului */
      .badge-special {
          position: absolute;
          top: 0;
          left: 0;
          background: #ff9800;
          color: white;
          padding: 5px 12px;
          font-weight: bold;
          font-size: 0.9em;
          border-bottom-right-radius: 10px;
          z-index: 10;
          box-shadow: 1px 1px 3px rgba(0,0,0,0.2);
      }

      /* Containerul care incadreaza imaginea masinii */
      .model-img-container {
          flex: 0 0 260px;
          width: 260px;
          height: 220px;
          position: relative;
          overflow: hidden;
          border-right: 1px solid #eee;
          background-color: #f8f9fa;
          display: flex;
          align-items: center;
          justify-content: center;
      }

      /* Ajustarea imaginii pentru a nu fi taiata (contain) */
      .model-img-container img {
          width: auto;
          height: auto;
          max-width: 100%;
          max-height: 100%;
          object-fit: contain;
          display: block;
          transition: transform 0.3s ease;
      }
      
      /* Efect de marire usoara la trecerea mouse-ului peste card */
      .model-zilei-card:hover .model-img-container img {
          transform: scale(1.03);
      }

      /* Zona cu texte si detalii despre masina */
      .model-info {
          flex: 1;
          padding: 15px 25px;
          text-align: left;
          display: flex;
          flex-direction: column;
          justify-content: center;
      }

      .model-info h3 { margin: 0 0 5px 0; font-size: 1.6em; color: #333; }
      
      /* Stiluri pentru lista de dotari cu bifa verde */
      .detalii-lista { list-style: none; padding: 0; margin: 10px 0; color: #666; font-size: 0.9em; }
      .detalii-lista li { margin-bottom: 4px; padding-left: 18px; position: relative; }
      .detalii-lista li::before {
          content: "✓";
          color: #4CAF50;
          position: absolute;
          left: 0;
          font-weight: bold;
      }

      .pret-mare { font-size: 1.8em; color: #4CAF50; font-weight: 800; margin: 10px 0; }

      /* Butonul de rezervare stilizat */
      .btn-rezerva-mare {
          display: inline-block;
          background-color: #4CAF50;
          color: white;
          padding: 10px 20px;
          text-decoration: none;
          border-radius: 5px;
          text-align: center;
          margin-top: auto;
          font-weight: bold;
      }
      
      .btn-rezerva-mare:hover { background-color: #45a049; }

      /* Adaptarea cardului pentru ecrane mici (telefoane) */
      @media (max-width: 700px) {
          .model-zilei-card { flex-direction: column; max-width: 350px; }
          .model-img-container { 
              flex: auto; 
              width: 100%; 
              height: 200px; 
              border-right: none; 
              border-bottom: 1px solid #eee; 
          }
          .model-info { text-align: center; padding: 20px; }
          .detalii-lista { text-align: left; display: inline-block; }
          .btn-rezerva-mare { width: 100%; }
      }
  </style>
</head>
<body>

<?php 
// Marker invizibil pentru script.js pentru a detecta statusul logarii global
if (isset($_SESSION['user_id'])) {
    echo '<div id="user-logged-in" data-rol="'.$_SESSION['rol'].'" style="display:none;"></div>';
}
?>

<main>
  <div class="hero-section">
      <h2 style="font-size: 2em; margin-bottom: 5px;">Bun venit la Rent-A-Car!</h2>
      <p style="color: #555;">Solutia ideala pentru mobilitatea dumneavoastra.</p>
  </div>
  
  <h3 style="text-align: center; margin-bottom: 20px; color: #333;">🔥 RECOMANDAREA ZILEI 🔥</h3>

  <?php
  try {
      // Selectam o singura masina la intamplare (RAND) din cele marcate ca disponibile
      $sql = "SELECT * FROM cars WHERE status = 'disponibil' ORDER BY RAND() LIMIT 1";
      $stmt = $pdo->query($sql);

      // Verificam daca am gasit vreo masina
      if ($stmt->rowCount() > 0) {
          $row = $stmt->fetch();
          
          // Fallback pentru imagine daca aceasta lipseste din baza de date
          $imgSrc = !empty($row['imagine']) ? $row['imagine'] : 'https://via.placeholder.com/300x200?text=Fara+Imagine';
          $marcaModel = htmlspecialchars($row['marca'] . ' ' . $row['model']);
          $clasa = htmlspecialchars($row['clasa']);
          $pret = number_format($row['pret_zi'], 2);
          
          // Codificam marca si modelul pentru a fi trimise corect prin URL
          $valoareModel = htmlspecialchars($row['marca'] . '|' . $row['model']);
          ?>

          <div class="model-zilei-card">
              <div class="badge-special">⭐ MODELUL ZILEI</div>
              
              <div class="model-img-container">
                  <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo $marcaModel; ?>" onerror="this.src='https://via.placeholder.com/300x200?text=Eroare'">
              </div>
              
              <div class="model-info">
                  <h3><?php echo $marcaModel; ?></h3>
                  <p style="color: #777; font-size:0.9em; margin-bottom: 10px;">Clasa <strong><?php echo $clasa; ?></strong></p>
                  
                  <ul class="detalii-lista">
                      <li>An fabricatie: <?php echo $row['an_fabricatie']; ?></li>
                      <li>Aer Conditionat</li>
                      <li>Verificare tehnica la zi</li>
                      <li>Asistenta rutiera inclusa</li>
                  </ul>

                  <div class="pret-mare"><?php echo $pret; ?> <span style="font-size: 0.5em; color: #666; font-weight: normal;">RON/ZI</span></div>
                  
                  <a href="rezerva.php?model_selectat=<?php echo urlencode($valoareModel); ?>" class="btn-rezerva-mare">
                      Rezerva Acum ➔
                  </a>
              </div>
          </div>

          <?php
      } else {
          // Mesaj afisat daca flota este goala sau toate masinile sunt inchiriate
          echo "<div style='text-align:center; padding:20px; background:#fff; border-radius:8px;'>Momentan nu avem masini disponibile.</div>";
      }
  } catch (PDOException $e) {
      // Afisarea erorii in caz de probleme cu serverul de baza de date
      echo "<div style='color:red; text-align:center;'>Eroare conexiune: " . $e->getMessage() . "</div>";
  }
  ?>

  <div style="text-align:center; margin-top: 40px;">
      <p><a href="flota.php" style="color: #4CAF50; font-weight: bold;">Vezi toata flota noastra &rarr;</a></p>
  </div>

</main>

</body>
</html>
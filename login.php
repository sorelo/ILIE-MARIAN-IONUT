<?php
// Pornim sesiunea pentru a gestiona logarea utilizatorului pe tot site-ul
if (session_status() === PHP_SESSION_NONE) { session_start(); } 

// Includem conexiunea la baza de date
require 'db.php';

// --- REDIRECTIONARE AUTOMATA ADMIN ---
// Daca un admin incearca sa acceseze profilul de client, il trimitem la pagina de admin
if (isset($_SESSION['user_id']) && $_SESSION['rol'] == 'admin') {
    header("Location: admin.php");
    exit();
}

$mesaj = '';
$tipMesaj = ''; 

// --- LOGICA 1: PROCESARE ANULARE REZERVARE (Doar pentru Clienti) ---
// Verificam daca s-a primit o cerere de anulare prin formular (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actiune']) && $_POST['actiune'] == 'anuleaza_rezervare') {
    $id_rezervare = intval($_POST['id_rezervare']);
    $id_masina = intval($_POST['id_masina']);

    try {
        // Incepem o tranzactie: ori se fac ambele modificari, ori niciuna (pentru siguranta datelor)
        $pdo->beginTransaction();
        
        // 1. Schimbam statusul rezervarii in 'anulat'
        $stmt1 = $pdo->prepare("UPDATE rentals SET status = 'anulat' WHERE id_inchiriere = :id AND id_user = :uid");
        $stmt1->execute(['id' => $id_rezervare, 'uid' => $_SESSION['user_id']]);
        
        // 2. Facem masina din nou 'disponibila' in flota
        $stmt2 = $pdo->prepare("UPDATE cars SET status = 'disponibil' WHERE id_masina = :mid");
        $stmt2->execute(['mid' => $id_masina]);
        
        // Salvam modificarile definitiv
        $pdo->commit();
        $mesaj = "✅ Rezervarea a fost anulată cu succes!";
        $tipMesaj = "succes";
    } catch (Exception $e) {
        // In caz de eroare, anulam orice modificare facuta in tranzactie
        $pdo->rollBack();
        $mesaj = "❌ Eroare la anulare: " . $e->getMessage();
        $tipMesaj = "eroare";
    }
}

// --- LOGICA 2: PROCESARE LOGIN / REGISTER ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sub-Logica pentru Login
    if (isset($_POST['actiune']) && $_POST['actiune'] == 'login') {
        $email = $_POST['email'];
        $pass = $_POST['parola'];
        
        // Cautam utilizatorul dupa email si parola
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND parola = :pass");
        $stmt->execute(['email' => $email, 'pass' => $pass]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Daca datele sunt corecte, salvam informatiile esentiale in sesiune
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nume'] = $user['nume'];
            $_SESSION['rol'] = $user['rol'];
            
            // Trimitem adminul sau clientul catre paginile corespunzatoare
            if ($user['rol'] == 'admin') { 
                header("Location: admin.php"); 
            } else {
                header("Location: login.php");
            }
            exit();
        } else {
            $mesaj = "Email sau parolă incorectă!";
            $tipMesaj = "eroare";
        }
    } 
    
    // Sub-Logica pentru Inregistrare (Register)
    elseif (isset($_POST['actiune']) && $_POST['actiune'] == 'register') {
        $nume = $_POST['nume_nou']; $email = $_POST['email_nou']; $pass = $_POST['parola_noua'];
        
        // Verificam intai daca email-ul nu este deja folosit
        $check = $pdo->prepare("SELECT id_user FROM users WHERE email = :email");
        $check->execute(['email' => $email]);
        
        if ($check->rowCount() > 0) {
            $mesaj = "Acest email este deja înregistrat!";
            $tipMesaj = "eroare";
        } else {
            // Introducem noul utilizator in baza de date cu rolul implicit de 'client'
            $ins = $pdo->prepare("INSERT INTO users (nume, email, parola, rol) VALUES (:nume, :email, :pass, 'client')");
            $ins->execute(['nume' => $nume, 'email' => $email, 'pass' => $pass]);
            $mesaj = "Cont creat cu succes! Te poți autentifica.";
            $tipMesaj = "succes";
        }
    }
}

// --- LOGICA 3: PRELUARE DATE PROFIL ---
// Daca utilizatorul este logat, ii incarcam datele personale si istoricul de inchirieri
$userData = null; $istoric = [];
if (isset($_SESSION['user_id'])) {
    // Luam datele profilului
    $stmtUser = $pdo->prepare("SELECT nume, email, data_inregistrare FROM users WHERE id_user = :id");
    $stmtUser->execute(['id' => $_SESSION['user_id']]);
    $userData = $stmtUser->fetch();

    // Luam istoricul rezervarilor folosind JOIN pentru a vedea numele masinii, nu doar ID-ul
    $sqlIstoric = "SELECT r.*, c.marca, c.model, c.id_masina 
                   FROM rentals r 
                   JOIN cars c ON r.id_masina = c.id_masina 
                   WHERE r.id_user = :id 
                   ORDER BY r.id_inchiriere DESC";
    $stmtIstoric = $pdo->prepare($sqlIstoric);
    $stmtIstoric->execute(['id' => $_SESSION['user_id']]);
    $istoric = $stmtIstoric->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($_SESSION['user_id']) ? 'Profilul Meu' : 'Autentificare'; ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js?v=3" defer></script>
    <style>
        /* Stiluri pentru butonul de anulare si aspectul cardului de profil */
        .btn-anuleaza { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
        .btn-anuleaza:hover { background: #c0392b; }
        .status-anulat { color: #999; text-decoration: line-through; }
        .profile-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
    </style>
</head>
<body>

<?php 
// Marker invizibil pentru ca script.js sa stie ca utilizatorul este logat si ce rol are
if (isset($_SESSION['user_id'])): 
?>
    <div id="user-logged-in" data-rol="<?php echo $_SESSION['rol']; ?>" style="display:none;"></div>
<?php endif; ?>

<main>
    <?php 
    // Afisarea mesajelor de alerta (succes sau eroare)
    if($mesaj): 
    ?>
        <div style="padding:15px; border-radius:5px; margin-bottom:20px; text-align:center; background: <?php echo ($tipMesaj == 'succes') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($tipMesaj == 'succes') ? '#155724' : '#721c24'; ?>;">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>

    <?php 
    // AFISARE 1: Daca NU este logat, aratam formularele de Login si Inregistrare
    if (!isset($_SESSION['user_id'])): 
    ?>
        <div class="auth-container">
            <div class="auth-box">
                <h3>🔐 Logare</h3>
                <form method="POST">
                    <input type="hidden" name="actiune" value="login">
                    <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" name="parola" placeholder="Parolă" required></div>
                    <button type="submit">Intră în cont</button>
                </form>
            </div>
            <div class="auth-box">
                <h3>📝 Cont Nou</h3>
                <form method="POST">
                    <input type="hidden" name="actiune" value="register">
                    <div class="form-group"><input type="text" name="nume_nou" placeholder="Nume Complet" required></div>
                    <div class="form-group"><input type="email" name="email_nou" placeholder="Email" required></div>
                    <div class="form-group"><input type="password" name="parola_noua" placeholder="Parolă" required></div>
                    <button type="submit" style="background:#2196F3">Înregistrare</button>
                </form>
            </div>
        </div>
    <?php 
    // AFISARE 2: Daca ESTE logat, aratam interfata de Profil si Istoricul
    else: 
    ?>
        <div class="profile-card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Salut, <?php echo htmlspecialchars($userData['nume']); ?>!</h2>
                <a href="logout.php" style="color:#e74c3c; font-weight:bold; text-decoration:none;">Deconectare ➔</a>
            </div>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><strong>Membru din:</strong> <?php echo date('d.m.Y', strtotime($userData['data_inregistrare'])); ?></p>
        </div>

        <h3>Istoric Rezervări</h3>
        <table style="width:100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background:#4CAF50; color: white;">
                    <th style="padding:12px; text-align:left;">Mașină</th>
                    <th style="padding:12px; text-align:left;">Perioada</th>
                    <th style="padding:12px; text-align:left;">Preț Total</th>
                    <th style="padding:12px; text-align:left;">Status</th>
                    <th style="padding:12px; text-align:center;">Acțiune</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($istoric) > 0): ?>
                    <?php foreach ($istoric as $r): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:12px;"><?php echo htmlspecialchars($r['marca'] . " " . $r['model']); ?></td>
                        <td style="padding:12px; font-size: 0.9em;"><?php echo $r['data_start'] . " - " . $r['data_sfarsit']; ?></td>
                        <td style="padding:12px;"><strong><?php echo $r['pret_total']; ?> RON</strong></td>
                        <td style="padding:12px;">
                            <span class="status-<?php echo $r['status']; ?>" style="font-weight:bold; color: <?php echo ($r['status'] == 'activ') ? 'green' : (($r['status'] == 'anulat') ? 'red' : 'blue'); ?>;">
                                <?php echo ucfirst($r['status']); ?>
                            </span>
                        </td>
                        <td style="padding:12px; text-align:center;">
                            <?php 
                            // Butonul de anulare apare doar daca rezervarea este inca 'activa'
                            if ($r['status'] == 'activ'): 
                            ?>
                                <form method="POST" onsubmit="return confirm('Ești sigur că vrei să anulezi această rezervare?');">
                                    <input type="hidden" name="actiune" value="anuleaza_rezervare">
                                    <input type="hidden" name="id_rezervare" value="<?php echo $r['id_inchiriere']; ?>">
                                    <input type="hidden" name="id_masina" value="<?php echo $r['id_masina']; ?>">
                                    <button type="submit" class="btn-anuleaza">Anulează</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#ccc;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding:20px; text-align:center; color:#777;">Nu ai nicio rezervare efectuată.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
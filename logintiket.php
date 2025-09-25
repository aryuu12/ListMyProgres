<?php
session_start();

// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "ursa_event");
if (!$koneksi) {
    die("Error Koneksi: " . mysqli_connect_errno() . " - " . mysqli_connect_error());
}

$error_message = "";
$user = "";
$pass = "";

if (isset($_POST['login'])) { // perbaikan: cocokkan dengan name="login"
    $user = trim($_POST['username']);  // perbaikan: huruf kecil
    $pass = trim($_POST['password']);  // perbaikan: huruf kecil

    if (empty($user) || empty($pass)) {
        if (empty($user) && empty($pass)) {
            $error_message = "Mohon masukkan username dan password terlebih dahulu";
        } elseif (empty($user)) {
            $error_message = "Mohon masukkan username terlebih dahulu";
        } else {
            $error_message = "Mohon masukkan password terlebih dahulu";
        }
    } else {
        $username = mysqli_real_escape_string($koneksi, $user);
        $password = $pass; // tidak pakai md5, karena DB simpan plaintext

        // Gunakan nama kolom sesuai database (case-sensitive di beberapa server)
        $stmt = $koneksi->prepare("SELECT Id_user, level FROM user WHERE Username=? AND Password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error_message = "Maaf Username dan Password Anda Salah";
        } else {
            $data = $result->fetch_assoc();
            $_SESSION['Id_user'] = $data['Id_user']; // sesuai nama kolom
            $_SESSION['level'] = $data['level'];
            header("Location: Transaksi.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Ticket Buy - Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:100vh;
    }
    .login-container {
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.25);
      width: 360px;
      padding: 40px 30px;
      text-align: center;
      color: #fff;
      box-shadow:0 12px 35px rgba(0,0,0,.25);
      animation: fadeIn .6s ease;
    }
    @keyframes fadeIn {
      from{opacity:0;transform:translateY(20px)}
      to{opacity:1;transform:translateY(0)}
    }
    .login-container h1 {
      font-size: 1.8rem;
      font-weight:600;
      margin-bottom:6px;
    }
    .login-container p {
      font-size: .9rem;
      opacity:.8;
      margin-bottom:25px;
    }
    .error-message {
      background:rgba(239,68,68,.3);
      border:1px solid rgba(239,68,68,.6);
      padding:8px 10px;
      border-radius:8px;
      margin-bottom:16px;
      font-size:.8rem;
      text-align:center;
    }
    .input-group {
      position:relative;
      margin-bottom:18px;
    }
    .input-group input {
      width:100%;
      padding:12px 40px 12px 14px;
      border:none;
      border-radius:10px;
      background:rgba(255,255,255,.2);
      color:#fff;
      font-size:.95rem;
    }
    .input-group input::placeholder {
      color:rgba(255,255,255,.6);
    }
    .input-group input:focus {
      outline:none;
      background:rgba(255,255,255,.3);
    }
    .input-group i {
      position:absolute;
      right:14px;
      top:50%;
      transform:translateY(-50%);
      color:rgba(255,255,255,.7);
    }
    .btn {
      width:100%;
      padding:12px;
      border:none;
      border-radius:10px;
      background:#ff4b2b;
      color:#fff;
      font-weight:600;
      font-size:1rem;
      cursor:pointer;
      transition:all .2s;
      margin-top:4px;
    }
    .btn:hover {
      background:#ff416c;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>Event <span style="font-weight:400;">Ticket Buy</span></h1>
    <p>Masuk untuk melanjutkan</p>

    <?php if (!empty($error_message)): ?>
      <div class="error-message"><?= $error_message; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
      <div class="input-group">
        <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($user); ?>">
        <i class="fa fa-user"></i>
      </div>
      <div class="input-group">
        <input type="password" name="password" placeholder="Password" value="<?= htmlspecialchars($pass); ?>">
        <i class="fa fa-lock"></i>
      </div>
      <button type="submit" name="login" class="btn">Sign In</button>
    </form>
  </div>
</body>
</html>
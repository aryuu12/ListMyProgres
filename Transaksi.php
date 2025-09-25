<?php
session_start();

// Matikan Notice & Warning agar tidak mengganggu output JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "ursa_event");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Handler AJAX (proses pembelian)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json'); // pastikan output JSON

    $id_ticket = $_POST['id_ticket'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $id_user = $_SESSION['Id_user'] ?? 1; // fallback user id = 1

    // Validasi input
    if (empty($id_ticket)) {
        echo json_encode(["status" => "error", "message" => "⚠ Harap pilih tiket terlebih dahulu!"]);
        exit;
    }
    if (empty($jumlah) || $jumlah <= 0) {
        echo json_encode(["status" => "error", "message" => "⚠ Harap masukkan jumlah tiket yang valid!"]);
        exit;
    }

    // Ambil tiket dari database
    $stmt = $conn->prepare("SELECT * FROM tiket WHERE Id_ticket = ?");
    $stmt->bind_param("i", $id_ticket);
    $stmt->execute();
    $tiket = $stmt->get_result()->fetch_assoc();

    if (!$tiket) {
        echo json_encode(["status" => "error", "message" => "Tiket tidak ditemukan."]);
        exit;
    }
    if ($jumlah > $tiket['jumlah']) {
        echo json_encode(["status" => "error", "message" => "Jumlah melebihi stok tersedia!"]);
        exit;
    }

    $harga = $tiket['harga'];
    $subtotal = $jumlah * $harga;

    // Simpan transaksi (Id_transaksi auto_increment)
    $stmtInsert = $conn->prepare("INSERT INTO transaksi (jumlah, subtotal, Id_user, Id_ticket) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("idii", $jumlah, $subtotal, $id_user, $id_ticket);

    if ($stmtInsert->execute()) {
        // Update stok tiket
        $newStok = $tiket['jumlah'] - $jumlah;
        $stmtUpdate = $conn->prepare("UPDATE tiket SET jumlah = ? WHERE Id_ticket = ?");
        $stmtUpdate->bind_param("ii", $newStok, $id_ticket);
        $stmtUpdate->execute();

        echo json_encode([
            "status" => "success",
            "message" => "✅ Tiket berhasil dibeli! E-Ticket akan dikirim ke email Anda.",
            "stok_baru" => $newStok
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Terjadi kesalahan SQL: " . $conn->error]);
    }
    exit;
}

// Ambil daftar tiket untuk form
$tiketResult = $conn->query("SELECT * FROM tiket");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Transaksi Tiket</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: white;
            border: 1px solid #ccc;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select {
            width: 100%; padding: 8px; margin-bottom: 10px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        input[readonly] { background: #f9f9f9; }
        button {
            width: 100%; padding: 10px;
            background: blue; color: white;
            font-size: 16px; border: none;
            border-radius: 5px; cursor: pointer;
        }
        button:hover { background: darkblue; }
        .error-msg {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            display: none;
        }
    </style>
</head>
<body>

<form id="formTiket">
    <h2>Form Pembelian Tiket</h2>
    <div class="error-msg" id="errorMsg"></div>

    <label>Pilih Tiket</label>
    <select name="id_ticket" id="id_ticket">
        <option value="">-- Pilih Tiket --</option>
        <?php while($row = $tiketResult->fetch_assoc()): ?>
            <option value="<?= $row['Id_ticket'] ?>" data-harga="<?= $row['harga'] ?>" data-stok="<?= $row['jumlah'] ?>">
                <?= $row['jenis'] ?> - Rp<?= number_format($row['harga']) ?> (Stok: <?= $row['jumlah'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Jumlah Beli</label>
    <input type="number" name="jumlah" id="jumlah" min="1">

    <label>Subtotal</label>
    <input type="text" id="subtotal" readonly placeholder="Subtotal otomatis">

    <button type="submit">Beli</button>
</form>

<script>
    const ticketSelect = document.getElementById('id_ticket');
    const jumlahInput = document.getElementById('jumlah');
    const subtotalInput = document.getElementById('subtotal');
    const errorMsg = document.getElementById('errorMsg');
    const form = document.getElementById('formTiket');

    function hitungSubtotal() {
        const selected = ticketSelect.options[ticketSelect.selectedIndex];
        const harga = selected.dataset.harga;
        const jumlah = jumlahInput.value;
        if (harga && jumlah > 0) {
            subtotalInput.value = "Rp " + (harga * jumlah).toLocaleString();
        } else {
            subtotalInput.value = "";
        }
    }

    ticketSelect.addEventListener('change', hitungSubtotal);
    jumlahInput.addEventListener('input', hitungSubtotal);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        errorMsg.style.display = "none";

        if (!ticketSelect.value) {
            errorMsg.innerText = "⚠ Tolong pilih tiket yang akan dibeli!";
            errorMsg.style.display = "block";
            return;
        }
        if (!jumlahInput.value || jumlahInput.value <= 0) {
            errorMsg.innerText = "⚠ Tolong masukkan jumlah tiket yang valid!";
            errorMsg.style.display = "block";
            return;
        }

        const formData = new FormData(form);
        formData.append("ajax", "1");

        fetch("", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message);
                    const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
                    selectedOption.dataset.stok = data.stok_baru;
                    selectedOption.text = selectedOption.text.replace(/\(Stok: \d+\)/, `(Stok: ${data.stok_baru})`);
                    form.reset();
                    subtotalInput.value = "";
                } else {
                    errorMsg.innerText = data.message;
                    errorMsg.style.display = "block";
                }
            })
            .catch(err => {
                alert("Terjadi kesalahan koneksi!\n" + err);
            });
    });
</script>

</body>
</html>

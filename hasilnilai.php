<?php
    // Cek apakah form sudah disubmit
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil nilai dari inputan user
        $nilai = $_POST['nilai'];

        // Cek logika nilai
        if ($nilai >= 65) {
            echo "Lulus";
        } else if ($nilai >= 50 && $nilai < 65) {
            echo "Harus Mengulang";
        } else {
            echo "Tidak Lulus";
        }
    }
    ?>
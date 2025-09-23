<?php
// segitiga_siku_siku.php
if (php_sapi_name() == "cli") {
    echo "Masukkan bilangan ganjil (tinggi): ";
    $line = trim(fgets(STDIN));
    $n = intval($line);
    $line_break = PHP_EOL;
    $output_start = '';
    $output_end = '';
} else {
    $n = isset($_GET['n']) ? intval($_GET['n']) : 0;
    $line_break = "<br>";
    $output_start = '<form method="get">Masukkan bilangan ganjil (tinggi): <input type="number" name="n" min="1" step="2" value="' . ($n > 0 ? $n : '') . '"><input type="submit" value="Tampilkan"></form><br><pre>';
    $output_end = '</pre>';
    echo $output_start;
    if ($n > 0) {
        echo "Input n = $n$line_break";
    }
}

if ($n <= 0) {
    echo "Masukkan angka positif.$line_break";
    if (php_sapi_name() != "cli") echo $output_end;
    exit;
}   
if ($n % 2 == 0) {
    echo "Harus bilangan ganjil.$line_break";
    if (php_sapi_name() != "cli") echo $output_end;
    exit;
}
$rows = $n;
for ($i = 1; $i <= $rows; $i++) {
    $stars = 2 * $i - 1;
    $spaces = $rows - $i;
    echo str_repeat(' ', $spaces) . str_repeat('*', $stars) . $line_break;
}
if (php_sapi_name() != "cli") echo $output_end;

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);

    echo "Welcome $name!<br>";
    echo "Your Email address is: $email";
} else {
    echo "Tidak ada data yang dikirim!";
}
?>

<?php
// baglan.example.php - Kurulum yapacak kişiler için güvenli şablon
$host = 'localhost';
$db_name = 'veritabani_adi';
$db_user = 'kullanici_adi';
$db_pass = 'guclu_sifreniz';

try {
    $db = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
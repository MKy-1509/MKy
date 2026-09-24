<?php
require_once 'baglan.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dokumanlar.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM dokumanlar WHERE id = ? AND durum = 1");
$stmt->execute([$id]);
$dosya = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosya || empty($dosya['dosya_yolu'])) {
    die("Dosya bulunamadı veya yayından kaldırılmış.");
}

// İndirme sayısını 1 artır
$db->prepare("UPDATE dokumanlar SET indirme_sayisi = indirme_sayisi + 1 WHERE id = ?")->execute([$id]);

// Eğer harici bir bağlantıysa (Google Drive vb.) yönlendir
if (filter_var($dosya['dosya_yolu'], FILTER_VALIDATE_URL)) {
    header('Location: ' . $dosya['dosya_yolu']);
    exit;
}

// Yerel sunucu dosyasıysa indirmeyi başlat
$tam_yol = __DIR__ . '/' . ltrim($dosya['dosya_yolu'], '/');
if (file_exists($tam_yol)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($tam_yol) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($tam_yol));
    readfile($tam_yol);
    exit;
} else {
    die("Fiziksel dosya sunucuda bulunamadı.");
}
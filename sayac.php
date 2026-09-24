<?php
if (!isset($db)) {
    require_once __DIR__ . '/baglan.php';
}

if (!isset($db)) {
    return;
}

date_default_timezone_set('Europe/Istanbul');

$sayfa =$_SERVER['REQUEST_URI'] ?? '/';

// Admin paneli girişlerini sayaca dahil etme
if (strpos($sayfa, '/admin') !== false) {
    return;
}

$ip =$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip_hash = hash('sha256', $ip . 'MK_SALT_2026');$bugun = date('Y-m-d');

// Ziyaretçi ID'si (Cookie)
if (empty($_COOKIE['mk_visitor_id'])) {
    $ziyaretci_id = md5($ip . microtime(true) . rand());
    @setcookie('mk_visitor_id', $ziyaretci_id, time() + (86400 * 365), "/");
} else {
    $ziyaretci_id =$_COOKIE['mk_visitor_id'];
}

// Birebir eşleşen orijinal sütunlara kayıt
try {
    $stmt =$db->prepare("INSERT INTO `ziyaretler` (`ip_hash`, `sayfa`, `ziyaretci_id`, `tarih`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ip_hash,$sayfa, $ziyaretci_id,$bugun]);
} catch (Exception $e) {
    // Hata oluşursa sessizce geç
}
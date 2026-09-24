<?php
require_once 'baglan.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
    exit;
}

$ad = trim($_POST['ad'] ?? '');
$eposta = trim($_POST['eposta'] ?? '');
$mesaj = trim($_POST['mesaj'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if (empty($ad) || empty($eposta) || empty($mesaj)) {
    echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları eksiksiz doldurun.']);
    exit;
}

if (!filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçerli bir e-posta adresi girin.']);
    exit;
}

// 1. ADIM: Veritabanına kaydet (Admin panelinde görünmesi için)
try {
    $stmt = $db->prepare("INSERT INTO mesajlar (ad, eposta, mesaj, ip_adresi) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ad, $eposta, $mesaj, $ip]);
} catch (Exception $e) {
    // Veritabanı hatası olsa bile devam etsin
}

// 2. ADIM: SMTP ile Spama Düşmeyen Mail Gönderimi Fonksiyonu
function smtp_mail_gonder($host, $port, $kullanici, $sifre, $kime, $konu, $mesaj_metni, $cevap_adresi, $gonderen_adi) {
    $socket = @fsockopen($host, $port, $errno, $errstr, 15);
    if (!$socket) {
        return false;
    }

    $server_response = fgets($socket, 515);

    fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, "AUTH LOGIN\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, base64_encode($kullanici) . "\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, base64_encode($sifre) . "\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, "MAIL FROM: <$kullanici>\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, "RCPT TO: <$kime>\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, "DATA\r\n");
    $server_response = fgets($socket, 515);

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($gonderen_adi) . "?= <$kullanici>\r\n";
    $headers .= "Reply-To: <$cevap_adresi>\r\n";
    $headers .= "To: <$kime>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($konu) . "?=\r\n";
    $headers .= "X-Mailer: PHP-MK-Portal\r\n";

    fputs($socket, $headers . "\r\n" . $mesaj_metni . "\r\n.\r\n");
    $server_response = fgets($socket, 515);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}

// SMTP Ayarları
$smtp_sunucu = '*****';
$smtp_port   = **;
$smtp_posta  = '******';
$smtp_sifre  = '*****';

// Şık HTML Mail Tasarımı
$html_icerik = "
<div style='background-color:#050811; color:#e2e8f0; font-family:Arial,sans-serif; padding:25px; border-radius:12px; border:1px solid #0284c7; max-width:600px; margin:auto;'>
    <div style='border-bottom:1px solid #1e293b; padding-bottom:15px; margin-bottom:20px;'>
        <h2 style='color:#38bdf8; margin:0;'>Yeni İletişim Formu Mesajı</h2>
        <span style='color:#94a3b8; font-size:12px;'>mevlutkaya.com.tr portali üzerinden iletildi.</span>
    </div>
    <table style='width:100%; border-collapse:collapse; margin-bottom:20px; font-size:14px;'>
        <tr>
            <td style='padding:8px 0; color:#94a3b8; width:120px;'><strong>Gönderen:</strong></td>
            <td style='color:#ffffff; font-weight:bold;'>" . htmlspecialchars($ad) . "</td>
        </tr>
        <tr>
            <td style='padding:8px 0; color:#94a3b8;'><strong>E-Posta:</strong></td>
            <td><a href='mailto:" . htmlspecialchars($eposta) . "' style='color:#38bdf8; text-decoration:none;'>" . htmlspecialchars($eposta) . "</a></td>
        </tr>
        <tr>
            <td style='padding:8px 0; color:#94a3b8;'><strong>Tarih:</strong></td>
            <td style='color:#cbd5e1;'>" . date('d.m.Y H:i') . "</td>
        </tr>
        <tr>
            <td style='padding:8px 0; color:#94a3b8;'><strong>IP Adresi:</strong></td>
            <td style='color:#64748b; font-family:monospace;'>" . htmlspecialchars($ip) . "</td>
        </tr>
    </table>
    <div style='background-color:#090f20; padding:15px; border-radius:8px; border-left:4px solid #06b6d4;'>
        <p style='margin:0; font-size:14px; line-height:1.6; color:#f1f5f9; white-space:pre-wrap;'>" . htmlspecialchars($mesaj) . "</p>
    </div>
</div>
";

// Gönderimi başlat
smtp_mail_gonder(
    $smtp_sunucu,
    $smtp_port,
    $smtp_posta,
    $smtp_sifre,
    'Mail Adres',
    'Yeni İletişim Formu: ' . $ad,
    $html_icerik,
    $eposta,
    'Mevlüt Kaya İletişim'
);

echo json_encode([
    'status' => 'success',
    'message' => 'Mesajınız başarıyla iletildi. En kısa sürede geri dönüş yapılacaktır!'
]);

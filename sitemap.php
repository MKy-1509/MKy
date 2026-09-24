<?php
// Olası hataları görmek için (geliştirme aşamasında)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'baglan.php';

header('Content-Type: application/xml; charset=utf-8');

$site_url = 'https://mevlutkaya.com.tr';$bugun = date('Y-m-d');

// Sayfaları güvenli çek (sütun kontrolü ile)
$sayfalar = [];
try {
    $sayfalar =$db->query("SELECT * FROM sayfalar WHERE menu_goster = 1 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Tabloda sorun varsa ana hatayı önlemek için boş array bırakır
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Ana Sayfa -->
  <url>
    <loc><?= $site_url ?>/</loc>
    <lastmod><?= $bugun ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- PDF Araçları Ana Listeleme Sayfası -->
  <url>
    <loc><?= $site_url ?>/pdf-araclar.php</loc>
    <lastmod><?= $bugun ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>

  <!-- Dinamik Sayfalar & PDF Araçları -->
  <?php foreach ($sayfalar as$s): ?>
  <url>
    <loc><?= $site_url ?>/sayfa.php?url=<?= htmlspecialchars($s['seo_url'] ?? '') ?></loc>
    <lastmod><?= !empty($s['guncelleme_tarihi']) ? date('Y-m-d', strtotime($s['guncelleme_tarihi'])) :$bugun ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>
</urlset>
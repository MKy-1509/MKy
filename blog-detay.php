<?php
require_once 'baglan.php';
require_once 'sayac.php';

$seo_url = trim($_GET['url'] ?? '');

if (empty($seo_url)) {
    header("Location: blog.php");
    exit;
}

// Yazıyı çek
$stmt = $db->prepare("SELECT * FROM blog WHERE seo_url = ? AND durum = 1");
$stmt->execute([$seo_url]);
$yazi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$yazi) {
    header("Location: blog.php");
    exit;
}

// Okunma sayısını 1 arttır
$db->prepare("UPDATE blog SET okunma_sayisi = okunma_sayisi + 1 WHERE id = ?")->execute([$yazi['id']]);

// Benzer diğer yazılar (Son 3 yazı)
$diger_yazilar = $db->prepare("SELECT * FROM blog WHERE durum = 1 AND id != ? ORDER BY id DESC LIMIT 3");
$diger_yazilar->execute([$yazi['id']]);
$benzerler = $diger_yazilar->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth" data-theme="cyan">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($yazi['baslik']) ?> | Mevlüt Kaya</title>
  <meta name="description" content="<?= htmlspecialchars($yazi['ozet'] ?: strip_tags(mb_substr($yazi['icerik'], 0, 160))) ?>">
  <link rel="canonical" href="https://mevlutkaya.com.tr/blog-detay.php?url=<?= htmlspecialchars($yazi['seo_url']) ?>">

  <meta property="og:title" content="<?= htmlspecialchars($yazi['baslik']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($yazi['ozet']) ?>">
  <?php if (!empty($yazi['gorsel'])): ?>
    <meta property="og:image" content="https://mevlutkaya.com.tr/<?= htmlspecialchars($yazi['gorsel']) ?>">
  <?php endif; ?>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    :root {
      --theme-primary: #06b6d4;
      --theme-secondary: #3b82f6;
      --theme-glow: rgba(6, 182, 212, 0.4);
      --theme-border: rgba(56, 189, 248, 0.22);
      --theme-dot: #22d3ee;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #050811; }
    .neon-glass-card {
      background: rgba(10, 16, 32, 0.85);
      backdrop-filter: blur(14px);
      border: 1px solid var(--theme-border);
    }
    .article-content {
      font-size: 1.05rem;
      line-height: 1.9;
      color: #e2e8f0;
    }
    .article-content p {
      margin-bottom: 1.5rem;
    }
    .article-content p:last-child {
      margin-bottom: 0;
    }
    .article-content strong, .article-content b {
      color: #ffffff;
      font-weight: 700;
    }
    .article-content h2 {
      font-size: 1.6rem;
      font-weight: 800;
      color: #ffffff;
      margin-top: 2.2rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--theme-primary);
      padding-left: 0.85rem;
    }
    .article-content h3 {
      font-size: 1.3rem;
      font-weight: 700;
      color: #ffffff;
      margin-top: 1.8rem;
      margin-bottom: 0.75rem;
    }
    .article-content ul {
      list-style-type: disc;
      list-style-position: inside;
      margin-bottom: 1.5rem;
      padding-left: 0.5rem;
    }
    .article-content a {
      color: var(--theme-dot);
      text-decoration: underline;
      text-underline-offset: 4px;
    }
    /* YouTube ve Video Entegrasyonunun Sinematik Responsive Tasarımı */
    .article-content iframe {
      width: 100% !important;
      aspect-ratio: 16 / 9;
      height: auto !important;
      border-radius: 1rem;
      border: 1px solid var(--theme-border);
      margin: 2rem 0;
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.7), 0 0 20px -5px var(--theme-glow);
    }
  </style>
</head>
<body class="text-slate-200 min-h-screen flex flex-col justify-between">

  <!-- Nav -->
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-[#050811]/85 border-b border-[var(--theme-border)]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
      <a href="blog.php" class="text-xs font-semibold text-slate-300 hover:text-white transition flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Tüm Blog & Haberler
      </a>
      <a href="index.php" class="text-xs font-semibold text-[var(--theme-dot)] hover:text-white transition">
        Ana Sayfa
      </a>
    </div>
  </nav>

  <!-- Makale Gövdesi -->
  <main class="max-w-4xl mx-auto px-4 sm:px-6 py-12 flex-1 w-full space-y-8">
    
    <header class="space-y-4">
      <div class="flex items-center gap-3 text-xs font-mono">
        <span class="px-2.5 py-1 rounded bg-blue-950/80 border border-[var(--theme-border)] text-[var(--theme-dot)] font-bold">
          <?= htmlspecialchars($yazi['kategori']) ?>
        </span>
        <span class="text-slate-400"><i class="fa-regular fa-calendar-days mr-1 text-[var(--theme-primary)]"></i> <?= date('d.m.Y', strtotime($yazi['olusturma_tarihi'])) ?></span>
        <span class="text-slate-400"><i class="fa-regular fa-eye mr-1 text-[var(--theme-primary)]"></i> <?= $yazi['okunma_sayisi'] ?> okuma</span>
      </div>

      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
        <?= htmlspecialchars($yazi['baslik']) ?>
      </h1>
    </header>

    <!-- Büyük Kapak Görseli -->
    <?php if (!empty($yazi['gorsel']) && file_exists($yazi['gorsel'])): ?>
      <div class="w-full aspect-video rounded-3xl overflow-hidden border border-[var(--theme-border)] shadow-2xl">
        <img src="<?= htmlspecialchars($yazi['gorsel']) ?>" alt="<?= htmlspecialchars($yazi['baslik']) ?>" class="w-full h-full object-cover">
      </div>
    <?php endif; ?>

    <!-- Makale Metni ve Video Alanı -->
    <article class="neon-glass-card rounded-3xl p-6 sm:p-10 article-content">
      <?php 
        if ($yazi['icerik'] === strip_tags($yazi['icerik'])) {
            echo nl2br($yazi['icerik']);
        } else {
            echo $yazi['icerik'];
        }
      ?>
    </article>

    <!-- Benzer Haberler -->
    <?php if (count($benzerler) > 0): ?>
      <div class="pt-8 border-t border-[var(--theme-border)] space-y-4">
        <h3 class="text-lg font-bold text-white">İlginizi Çekebilecek Diğer Yazılar</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <?php foreach ($benzerler as $b): ?>
            <a href="blog-detay.php?url=<?= htmlspecialchars($b['seo_url']) ?>" class="p-4 rounded-xl bg-blue-950/30 border border-[var(--theme-border)] hover:border-[var(--theme-primary)] transition group block">
              <span class="text-[10px] font-mono text-[var(--theme-dot)] block mb-1"><?= htmlspecialchars($b['kategori']) ?></span>
              <h4 class="text-xs font-bold text-white group-hover:text-[var(--theme-dot)] line-clamp-2"><?= htmlspecialchars($b['baslik']) ?></h4>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </main>

  <footer class="border-t border-[var(--theme-border)] py-6 text-center text-xs text-slate-500">
    © <?= date('Y') ?> Mevlüt Kaya • mevlutkaya.com.tr
  </footer>

</body>
</html>
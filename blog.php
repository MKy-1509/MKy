<?php
// Olası hataları yakalamak için
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'baglan.php';
if (file_exists('sayac.php')) {
    require_once 'sayac.php';
}

// Yayındaki blog yazılarını güvenli çek
$yazilar = [];
try {
    $yazilar = $db->query("SELECT * FROM blog WHERE durum = 1 ORDER BY sira ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $yazilar = [];
}
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth" data-theme="cyan">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog & Teknoloji Haberleri | Mevlüt Kaya</title>
  <meta name="description" content="Hastane bilişimi, Windows sunucu yönetimi, yazılım ve güncel bilişim teknolojileri üzerine yazılar ve rehberler.">
  <link rel="canonical" href="https://mevlutkaya.com.tr/blog.php">

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
    .bg-grid-neon {
      background-size: 38px 38px;
      background-image: 
        linear-gradient(to right, rgba(14, 165, 233, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(59, 130, 246, 0.08) 1px, transparent 1px);
    }
    .neon-glass-card {
      background: rgba(10, 16, 32, 0.85);
      backdrop-filter: blur(14px);
      border: 1px solid var(--theme-border);
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .neon-glass-card:hover {
      border-color: var(--theme-primary);
      transform: translateY(-4px);
      box-shadow: 0 0 25px var(--theme-glow), 0 10px 30px -10px rgba(37, 99, 235, 0.4);
    }
  </style>
</head>
<body class="text-slate-200 min-h-screen bg-grid-neon flex flex-col justify-between">

  <!-- Üst Menü -->
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-[#050811]/85 border-b border-[var(--theme-border)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
      <a href="index.php" class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#0a1020] border border-[var(--theme-border)] flex items-center justify-center text-xs text-[var(--theme-dot)] font-extrabold">MK</div>
        <div>
          <span class="text-sm font-extrabold tracking-widest text-white">MEVLÜT KAYA<span class="text-[var(--theme-primary)]">.</span></span>
          <span class="block text-[10px] text-slate-400 font-mono">Bilişim & Yazılım Portalı</span>
        </div>
      </a>
      <div class="flex items-center gap-4">
        <a href="index.php" class="text-xs font-semibold text-slate-300 hover:text-white transition">← Ana Sayfa</a>
        <a href="pdf-araclar.php" class="text-xs font-semibold text-[var(--theme-dot)] hover:text-white transition">PDF Araçları</a>
      </div>
    </div>
  </nav>

  <!-- İçerik Alanı -->
  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-12 flex-1 w-full space-y-10">
    
    <!-- Başlık Bloğu -->
    <div class="text-center max-w-2xl mx-auto space-y-3">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-950/70 border border-[var(--theme-border)] text-xs text-[var(--theme-dot)]">
        <i class="fa-regular fa-newspaper"></i> Güncel Yazılar & Haberler
      </div>
      <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight">Teknoloji & Bilgi Sistemleri</h1>
      <p class="text-slate-400 text-sm">Sunucu mimarisi, hastane bilişim süreçleri, siber güvenlik denetimleri ve yazılım rehberleri.</p>
    </div>

    <!-- Haber / Blog Grid Kartları -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if (!empty($yazilar)): ?>
        <?php foreach ($yazilar as $yazi): ?>
          <?php
            $seo_link = 'blog-detay.php?url=' . htmlspecialchars($yazi['seo_url'] ?? '', ENT_QUOTES, 'UTF-8');
            $ozet_metin = !empty($yazi['ozet']) 
              ? $yazi['ozet'] 
              : mb_substr(strip_tags($yazi['icerik'] ?? ''), 0, 140, 'UTF-8');
          ?>
          <article class="neon-glass-card rounded-2xl overflow-hidden flex flex-col justify-between group">
            <div>
              <!-- Görsel -->
              <a href="<?= $seo_link ?>" class="block relative aspect-video overflow-hidden bg-slate-900 border-b border-[var(--theme-border)]">
                <?php if (!empty($yazi['gorsel'])): ?>
                  <img src="<?= htmlspecialchars($yazi['gorsel'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($yazi['baslik'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div style="display:none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-blue-950/80 to-[#070b16] text-[var(--theme-dot)] text-3xl font-mono">
                    <i class="fa-solid fa-code"></i>
                  </div>
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-950/80 to-[#070b16] text-[var(--theme-dot)] text-3xl font-mono">
                    <i class="fa-solid fa-code"></i>
                  </div>
                <?php endif; ?>
                <span class="absolute top-3 left-3 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[#050811]/80 backdrop-blur-md border border-[var(--theme-border)] text-[var(--theme-dot)]">
                  <?= htmlspecialchars($yazi['kategori'] ?? 'Teknoloji', ENT_QUOTES, 'UTF-8') ?>
                </span>
              </a>

              <!-- Metin Alanı -->
              <div class="p-5 space-y-2.5">
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                  <span><i class="fa-regular fa-calendar-days mr-1 text-[var(--theme-primary)]"></i> <?= !empty($yazi['olusturma_tarihi']) ? date('d.m.Y', strtotime($yazi['olusturma_tarihi'])) : date('d.m.Y') ?></span>
                  <span><i class="fa-regular fa-eye mr-1 text-[var(--theme-primary)]"></i> <?= (int)($yazi['okunma_sayisi'] ?? 0) ?></span>
                </div>
                <h2 class="text-base font-bold text-white group-hover:text-[var(--theme-dot)] transition line-clamp-2">
                  <a href="<?= $seo_link ?>">
                    <?= htmlspecialchars($yazi['baslik'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </h2>
                <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                  <?= htmlspecialchars($ozet_metin, ENT_QUOTES, 'UTF-8') ?>...
                </p>
              </div>
            </div>

            <!-- Alt Link -->
            <div class="px-5 pb-5 pt-2 border-t border-[var(--theme-border)]/40 flex justify-between items-center text-xs font-semibold">
              <a href="<?= $seo_link ?>" class="text-[var(--theme-dot)] group-hover:translate-x-1 transition flex items-center gap-1.5">
                Devamını Oku <i class="fa-solid fa-arrow-right text-[10px]"></i>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full py-16 text-center text-slate-500 italic">
          Henüz yayınlanmış bir haber veya blog yazısı bulunmuyor.
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Footer -->
  <footer class="border-t border-[var(--theme-border)] py-6 text-center text-xs text-slate-500">
    © <?= date('Y') ?> Mevlüt Kaya • mevlutkaya.com.tr
  </footer>

</body>
</html>
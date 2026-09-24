<?php
require_once 'baglan.php';
require_once 'sayac.php';

if (!isset($_GET['url'])) {
    header("Location: index.php");
    exit;
}

$url = strip_tags($_GET['url']);
$sorgu = $db->prepare("SELECT * FROM sayfalar WHERE seo_url = :url");
$sorgu->execute(['url' => $url]);
$sayfa = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$sayfa) {
    header("Location: index.php");
    exit;
}

// Genel ayarları menü vb. için çek
$ayarlar_raw = $db->query("SELECT * FROM ayarlar")->fetchAll(PDO::FETCH_KEY_PAIR);
$a = function($key, $default = '') use ($ayarlar_raw) {
    return htmlspecialchars($ayarlar_raw[$key] ?? $default);
};

// Menüdeki dinamik sayfaları çek
$menu_sayfalar = $db->query("SELECT * FROM sayfalar WHERE menu_goster = 1 ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth" data-theme="cyan">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($sayfa['baslik']) ?> | <?= $a('site_baslik') ?></title>
  
  <!-- Sayfa açılır açılmaz hafızadaki temayı uygula (Renk uyuşmazlığını önler) -->
  <script>
    const savedTheme = localStorage.getItem('site_theme') || 'cyan';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>

  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Dinamik Tema Renk Değişkenleri (index.php ile birebir aynı) */
    :root {
      --theme-primary: #06b6d4;
      --theme-secondary: #3b82f6;
      --theme-glow: rgba(6, 182, 212, 0.4);
      --theme-border: rgba(56, 189, 248, 0.22);
      --theme-dot: #22d3ee;
    }

    [data-theme="green"] {
      --theme-primary: #10b981;
      --theme-secondary: #059669;
      --theme-glow: rgba(16, 185, 129, 0.4);
      --theme-border: rgba(52, 211, 153, 0.22);
      --theme-dot: #34d399;
    }

    [data-theme="amber"] {
      --theme-primary: #f59e0b;
      --theme-secondary: #d97706;
      --theme-glow: rgba(245, 158, 11, 0.4);
      --theme-border: rgba(251, 191, 36, 0.22);
      --theme-dot: #fbbf24;
    }

    body { 
      background-color: #050811; 
      font-family: sans-serif; 
    }
    
    /* Neon Arka Plan Izgarası */
    .bg-grid-neon {
      background-size: 38px 38px;
      background-image: 
        linear-gradient(to right, rgba(14, 165, 233, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(59, 130, 246, 0.08) 1px, transparent 1px);
    }
    
    .neon-glass-card {
      background: rgba(10, 16, 32, 0.82);
      backdrop-filter: blur(14px);
      border: 1px solid var(--theme-border);
      box-shadow: 0 4px 20px -2px rgba(2, 132, 199, 0.12);
    }

    .neon-btn-primary {
      background: linear-gradient(135deg, var(--theme-secondary) 0%, var(--theme-primary) 100%);
      box-shadow: 0 0 20px var(--theme-glow);
      transition: all 0.3s ease;
    }
    .neon-btn-primary:hover {
      box-shadow: 0 0 30px var(--theme-primary), 0 0 10px rgba(255, 255, 255, 0.4);
      transform: translateY(-1px);
    }

    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }
    .no-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>
</head>
<body class="text-slate-200 bg-grid-neon min-h-screen flex flex-col selection:bg-[var(--theme-primary)] selection:text-white">

<!-- Üst Menü -->
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-[#050811]/75 border-b border-[var(--theme-border)] shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between gap-4">
      
      <!-- Logo ve Site Başlığı -->
      <a href="index.php" class="group flex items-center gap-3 text-white font-bold tracking-wider transition shrink-0">
        <div class="relative flex items-center justify-center">
          <div class="absolute -inset-1 bg-gradient-to-r from-[var(--theme-primary)] to-[var(--theme-secondary)] rounded-xl blur-md opacity-75 group-hover:opacity-100 transition duration-300"></div>
          <div class="relative w-10 h-10 rounded-xl bg-[#0a1020] border border-[var(--theme-primary)]/40 flex items-center justify-center text-xs text-[var(--theme-dot)] font-extrabold shadow-inner">
            MK
          </div>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-extrabold tracking-widest text-white group-hover:text-[var(--theme-dot)] transition">MEVLÜT KAYA<span class="text-[var(--theme-primary)]">.</span></span>
          <span class="text-[10px] text-slate-400 font-mono font-medium tracking-tight">IT Specialist</span>
        </div>
      </a>

      <!-- Araç Linkleri -->
      <div class="flex items-center gap-1.5 overflow-x-auto whitespace-nowrap py-2 px-2 max-w-xl lg:max-w-3xl no-scrollbar">
        
        <a href="index.php" class="px-3 py-2 rounded-xl text-xs font-semibold text-[var(--theme-dot)] hover:text-white hover:bg-[var(--theme-primary)]/20 hover:border-[var(--theme-primary)]/30 border border-transparent transition shrink-0">
          ← Ana Sayfa
        </a>

        <?php
        // Tüm PDF araçları sayfalarını veritabanından çekiyoruz
        $pdf_araclari = $db->query("SELECT * FROM sayfalar WHERE menu_goster = 1 AND menu_tipi = 'pdf_araclari' ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($pdf_araclari as $arac): 
            $aktifMi = (isset($_GET['url']) && $_GET['url'] === $arac['seo_url']) ? 'bg-[var(--theme-primary)]/20 border-[var(--theme-primary)]/50 text-[var(--theme-dot)] shadow-[0_0_10px_var(--theme-glow)]' : 'text-slate-300 border-transparent hover:text-white hover:bg-[var(--theme-primary)]/20 hover:border-[var(--theme-primary)]/30';
        ?>
          <a href="sayfa.php?url=<?= $arac['seo_url'] ?>" class="px-3 py-2 rounded-xl text-xs font-semibold border transition shrink-0 <?= $aktifMi ?>">
            <?= htmlspecialchars($arac['baslik']) ?>
          </a>
        <?php endforeach; ?>

      </div>

      <!-- Sağ Kısım: Tema Değiştirici ve CV İndir -->
      <div class="flex items-center gap-3 shrink-0">
        <!-- Tema Seçici Butonları -->
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-blue-950/40 border border-[var(--theme-border)]">
          <button onclick="setTheme('cyan')" class="w-5 h-5 rounded-lg bg-cyan-500 shadow-sm transition transform hover:scale-110" title="Cyber Cyan"></button>
          <button onclick="setTheme('green')" class="w-5 h-5 rounded-lg bg-emerald-500 shadow-sm transition transform hover:scale-110" title="Matrix Green"></button>
          <button onclick="setTheme('amber')" class="w-5 h-5 rounded-lg bg-amber-500 shadow-sm transition transform hover:scale-110" title="Amber Hacker"></button>
        </div>

        <a href="cv.pdf" download class="relative group px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600/20 to-[var(--theme-primary)]/20 border border-[var(--theme-primary)]/40 text-[var(--theme-dot)] text-xs font-bold tracking-wide hover:text-white hover:border-[var(--theme-primary)] transition hidden xl:flex items-center gap-2">
          CV İndir
        </a>
      </div>

    </div>
  </nav>

  <!-- Sayfa İçeriği -->
  <main class="flex-1 max-w-4xl mx-auto px-6 py-16 w-full">
    <div class="flex items-center gap-3 mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight"><?= htmlspecialchars($sayfa['baslik']) ?></h1>
      <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--theme-primary)]/40 via-blue-500/20 to-transparent"></div>
    </div>
    
    <div class="neon-glass-card p-8 md:p-10 rounded-3xl leading-relaxed text-slate-300 space-y-4">
      <!-- HTML taglarına izin vermek için htmlspecialchars KULLANMIYORUZ -->
      <?= $sayfa['icerik'] ?>
    </div>
  </main>

  <footer class="py-6 border-t border-[var(--theme-border)] text-center text-xs text-slate-500">
    <p>© <?= date('Y') ?> Mevlüt Kaya.</p>
  </footer>

  <script>
    // Tema Değiştirme Fonksiyonu (Tüm sayfalarda ortak çalışır)
    function setTheme(themeName) {
      document.documentElement.setAttribute('data-theme', themeName);
      localStorage.setItem('site_theme', themeName);
    }
  </script>

</body>
</html>
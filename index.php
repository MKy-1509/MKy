<?php
require_once 'baglan.php';
require_once 'sayac.php';

// Ayarları çek
$ayarlar_raw = $db->query("SELECT * FROM ayarlar")->fetchAll(PDO::FETCH_KEY_PAIR);

$a = function($key, $default = '') use ($ayarlar_raw) {
    return htmlspecialchars($ayarlar_raw[$key] ?? $default, ENT_QUOTES, 'UTF-8');
};

// Projeleri ve Yetkinlikleri Admin Panelinden Çek
$sorgu = $db->query("SELECT * FROM projeler ORDER BY sira ASC, id DESC");
$projeler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$yetkinlikler_sorgu = $db->query("SELECT * FROM yetkinlikler ORDER BY sira ASC, id ASC");
$yetkinlikler = $yetkinlikler_sorgu->fetchAll(PDO::FETCH_ASSOC);

// Dinamik sayfaları çek
$dinamik_sayfalar = $db->query("SELECT * FROM sayfalar WHERE menu_goster = 1 AND menu_tipi = 'ana_menu' ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);
$pdf_menu_sayfalari = $db->query("SELECT * FROM sayfalar WHERE menu_goster = 1 AND menu_tipi = 'pdf_araclari' ORDER BY sira ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ana sayfa için yayındaki son 3 blog/haberi çek (Özel sıralamaya göre)
$son_bloglar = [];
try {
    $son_bloglar = $db->query("SELECT * FROM blog WHERE durum = 1 ORDER BY sira ASC, id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $son_bloglar = [];
}
// Canlı Mesai Saati Kontrolü (Hafta içi 09:00 - 17:30)
date_default_timezone_set('Europe/Istanbul');
$gun = (int)date('N'); // 1: Pazartesi ... 5: Cuma, 6: Cumartesi, 7: Pazar
$saat_dakika = (int)date('Hi'); // Örn: 0930, 1730

// Hafta içi ve 08:30 (0830) ile 17:30 (1730) arası mı?
$mesai_ici = ($gun >= 1 && $gun <= 5 && $saat_dakika >= 830 && $saat_dakika < 1730);
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth" data-theme="cyan">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $a('site_baslik', 'Mevlüt Kaya | Bilgi Sistemleri & BT Operasyonları') ?></title>
  
  <meta name="robots" content="index, follow">
  <meta name="keywords" content="Mevlüt Kaya, Bilgi Sistemleri Uzmanı, Bilgisayar Programcısı, Hastane Bilgi Sistemleri, PDF Dönüştürücü, PDF Araçları">
  <link rel="canonical" href="https://mevlutkaya.com.tr/">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Mevlüt Kaya",
    "url": "https://mevlutkaya.com.tr",
    "jobTitle": "Bilgi Sistemleri Uzmanı",
    "worksFor": {
      "@type": "Organization",
      "name": "Medicana International Ankara Hastanesi"
    },
    "sameAs": [
      "https://www.linkedin.com"
    ]
  }
  </script>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

    html { scroll-padding-top: 100px; }
    section { scroll-margin-top: 100px; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #050811; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }

    @media (hover: hover) and (pointer: fine) {
      body, a, button, input, textarea { cursor: none !important; }
    }

    .cursor-dot {
      width: 8px; height: 8px; background-color: var(--theme-dot);
      border-radius: 50%; position: fixed; top: 0; left: 0;
      pointer-events: none; z-index: 9999;
      box-shadow: 0 0 10px var(--theme-dot), 0 0 20px var(--theme-primary);
      transition: transform 0.08s ease-out; transform: translate(-50%, -50%);
    }

    .cursor-outline {
      width: 36px; height: 36px; border: 1.5px solid var(--theme-border);
      border-radius: 50%; position: fixed; top: 0; left: 0;
      pointer-events: none; z-index: 9998;
      box-shadow: 0 0 15px var(--theme-glow);
      transition: width 0.25s ease, height 0.25s ease;
      transform: translate(-50%, -50%);
    }

    .cursor-hover {
      width: 54px !important; height: 54px !important;
      border-color: rgba(129, 140, 248, 0.8) !important;
      background: rgba(99, 102, 241, 0.12) !important;
    }

    .mouse-spotlight {
      position: fixed; top: 0; left: 0; width: 600px; height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--theme-glow) 0%, rgba(99, 102, 241, 0.03) 40%, transparent 70%);
      pointer-events: none; z-index: 1; transform: translate(-50%, -50%);
    }

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
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .neon-glass-card:hover {
      border-color: var(--theme-primary);
      transform: translateY(-2px);
      box-shadow: 0 0 25px var(--theme-glow), 0 10px 30px -10px rgba(37, 99, 235, 0.4);
    }

    .system-card-glow {
      position: absolute; inset: -3px;
      background: linear-gradient(180deg, #6366f1 0%, var(--theme-primary) 50%, var(--theme-secondary) 100%);
      border-radius: 28px; filter: blur(22px); opacity: 0.38; z-index: -1;
    }

    .system-inner-panel {
      background: rgba(6, 10, 22, 0.85);
      border: 1px solid var(--theme-border);
    }

    .neon-btn-primary {
      background: linear-gradient(135deg, var(--theme-secondary) 0%, var(--theme-primary) 100%);
      box-shadow: 0 0 20px var(--theme-glow);
      transition: all 0.3s ease;
    }
    .neon-btn-primary:hover {
      box-shadow: 0 0 30px var(--theme-primary);
      transform: translateY(-1px);
    }
  </style>
</head>
<body class="text-slate-200 relative min-h-screen selection:bg-[var(--theme-primary)] selection:text-white bg-grid-neon overflow-x-hidden">

  <div class="cursor-dot hidden md:block"></div>
  <div class="cursor-outline hidden md:block"></div>
  <div class="mouse-spotlight hidden md:block"></div>

  <div class="fixed top-[-60px] left-1/2 -translate-x-1/2 w-[750px] h-[380px] bg-gradient-to-tr from-[var(--theme-primary)]/25 via-blue-600/20 to-indigo-600/15 blur-[130px] pointer-events-none -z-10"></div>
  <div class="fixed bottom-10 right-[-100px] w-[600px] h-[380px] bg-gradient-to-tl from-[var(--theme-primary)]/20 via-blue-600/20 to-transparent blur-[140px] pointer-events-none -z-10"></div>

  <!-- ÜST MENÜ -->
<!-- ÜST MENÜ (Tek Satır, Sıkışmayan Geniş Tasarım) -->
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-[#050811]/85 border-b border-[var(--theme-border)] shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-2 sm:gap-4">
      
      <!-- Logo & Unvan -->
      <a href="#hakkimda" class="group flex items-center gap-3 text-white font-bold tracking-wider transition shrink-0 nav-scroll-link">
        <div class="relative flex items-center justify-center">
          <div class="absolute -inset-1 bg-gradient-to-r from-[var(--theme-primary)] to-[var(--theme-secondary)] rounded-xl blur-md opacity-75 group-hover:opacity-100 transition duration-300"></div>
          <div class="relative w-10 h-10 rounded-xl bg-[#0a1020] border border-[var(--theme-primary)]/40 flex items-center justify-center text-xs text-[var(--theme-dot)] font-extrabold shadow-inner">
            MK
          </div>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-extrabold tracking-widest text-white group-hover:text-[var(--theme-dot)] transition">MEVLÜT KAYA<span class="text-[var(--theme-primary)]">.</span></span>
          <span class="text-[10px] text-slate-400 font-mono font-medium tracking-tight">Bilgi Sistemleri & IT Operasyon</span>
        </div>
      </a>

      <!-- Masaüstü Linkler (Tek Satır - whitespace-nowrap) -->
      <div class="hidden xl:flex items-center gap-1 p-1.5 rounded-2xl bg-blue-950/20 border border-[var(--theme-border)] backdrop-blur-md whitespace-nowrap">
        <a href="#hakkimda" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Hakkımda</a>
        <a href="#yetkinlikler" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Yetkinlikler</a>
        <a href="#deneyim" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Deneyim</a>
        <a href="#blog" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Blog</a>
        <a href="#projeler" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Projeler</a>
        <a href="pdf-araclar.php" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-[var(--theme-dot)] hover:text-white hover:bg-[var(--theme-primary)]/10 transition">PDF Araçları</a>
        <a href="dokumanlar.php" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Dökümanlar</a>
        <a href="#iletisim" class="nav-scroll-link px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">İletişim</a>
      </div>

      <!-- Tema & CV -->
      <div class="flex items-center gap-2.5 shrink-0">
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-blue-950/40 border border-[var(--theme-border)]">
          <button onclick="setTheme('cyan')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-cyan-500 shadow-sm transition transform hover:scale-110" title="Cyber Cyan"></button>
          <button onclick="setTheme('green')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-emerald-500 shadow-sm transition transform hover:scale-110" title="Matrix Green"></button>
          <button onclick="setTheme('amber')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-amber-500 shadow-sm transition transform hover:scale-110" title="Amber Hacker"></button>
        </div>

        <a href="cv.pdf" download class="hidden sm:flex px-3 py-1.5 rounded-xl bg-gradient-to-r from-blue-600/20 to-[var(--theme-primary)]/20 border border-[var(--theme-primary)]/40 text-[var(--theme-dot)] text-xs font-bold hover:text-white transition items-center gap-1.5">
          <i class="fa-solid fa-download text-[11px]"></i> CV İndir
        </a>

        <!-- Hamburger Menü Butonu (Orta ekranlarda kırılmayı önler) -->
        <button id="mobile-menu-btn" class="xl:hidden p-2 rounded-xl bg-blue-950/40 border border-[var(--theme-border)] text-[var(--theme-dot)] hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
      </div>

    </div>

    <!-- Mobil & Tablet Açılır Menüsü -->
    <div id="mobile-menu" class="hidden xl:hidden px-4 pt-2 pb-6 space-y-2 border-t border-[var(--theme-border)] bg-[#050811]/95 backdrop-blur-2xl">
      <a href="#hakkimda" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Hakkımda</a>
      <a href="#yetkinlikler" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Yetkinlikler</a>
      <a href="#deneyim" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Deneyim</a>
      <a href="#blog" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Blog</a>
      <a href="#projeler" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Projeler</a>
      <a href="pdf-araclar.php" class="block px-4 py-2 rounded-xl text-sm font-semibold text-[var(--theme-dot)] hover:bg-[var(--theme-primary)]/20">PDF Araçları</a>
      <a href="dokumanlar.php" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Dökümanlar</a>
      <a href="#iletisim" class="nav-scroll-link block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">İletişim</a>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 md:py-16 space-y-24 md:space-y-36 relative z-10">

    <!-- HERO BÖLÜMÜ -->
    <section id="hakkimda" class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center min-h-[70vh]">
      
      <!-- SOL ALAN -->
      <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
        
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-950/70 border border-[var(--theme-border)] text-xs font-medium text-[var(--theme-dot)] shadow-[0_0_15px_var(--theme-glow)]">
          <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse"></span>
          <span><?= $a('hero_badge', 'Medicana International Ankara • Bilgi Sistemleri Uzmanı') ?></span>
        </div>
        
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
          Merhaba, ben <br/>
          <span class="bg-gradient-to-r from-[var(--theme-dot)] via-sky-300 to-blue-500 bg-clip-text text-transparent">
            Mevlüt Kaya
          </span>
        </h1>
        
        <p class="text-lg sm:text-xl font-semibold text-slate-300 flex items-center justify-center lg:justify-start gap-2">
          <span class="text-[var(--theme-primary)] font-mono font-bold">&gt;_</span>
          Bilgi Sistemleri & Bilgisayar Programcısı
        </p>
        
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
          <?= nl2br($a('hero_aciklama', 'Medicana Hastanesi Bilgi Sistemleri biriminde çalışan, network, donanım, HBYS süreçleri ve kullanıcı destek operasyonlarında deneyimli bir bilgi işlem uzmanıyım. Sistem sürekliliğini sağlama, arıza tespiti ve çözüm üretme, veri güvenliği ve altyapı yönetimi konularında aktif rol alıyorum. Teknolojik çözümleri iş süreçlerine entegre ederek verimliliği artırmayı hedefliyorum.')) ?>
        </p>

        <!-- Teknoloji Rozetleri -->
        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
          <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-blue-950/50 border border-[var(--theme-border)] text-[var(--theme-dot)]">#PHP</span>
          <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-blue-950/50 border border-[var(--theme-border)] text-[var(--theme-dot)]">#MySQL</span>
          <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-blue-950/50 border border-[var(--theme-border)] text-[var(--theme-dot)]">#WindowsServer</span>
          <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-blue-950/50 border border-[var(--theme-border)] text-[var(--theme-dot)]">#HBYS</span>
          <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-blue-950/50 border border-[var(--theme-border)] text-[var(--theme-dot)]">#Network</span>
        </div>

        <!-- Butonlar -->
        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
          <a href="pdf-araclar.php" class="relative group px-5 py-3 rounded-xl font-bold text-sm text-white overflow-hidden transition transform hover:scale-105 shadow-[0_0_20px_rgba(6,182,212,0.5)] flex items-center gap-2">
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 via-blue-600 to-indigo-600 animate-pulse"></div>
            <div class="absolute -inset-1 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-xl blur opacity-60 group-hover:opacity-100 transition duration-300"></div>
            <span class="relative z-10 text-base">📄</span>
            <span class="relative z-10 tracking-wide">PDF Araçları</span>
          </a>

          <a href="#blog" class="nav-scroll-link px-5 py-3 neon-glass-card hover:text-white font-medium text-sm rounded-xl transition text-slate-200 flex items-center gap-2 border-[var(--theme-border)]">
            <i class="fa-regular fa-newspaper text-[var(--theme-primary)]"></i> Blog
          </a>

          <a href="#projeler" class="nav-scroll-link px-5 py-3 neon-glass-card hover:text-white font-medium text-sm rounded-xl transition text-slate-200 flex items-center gap-2 border-[var(--theme-border)]">
            <i class="fa-solid fa-laptop-code text-[var(--theme-primary)]"></i> Projeler
          </a>

          <a href="#yetkinlikler" class="nav-scroll-link px-5 py-3 neon-glass-card hover:text-white font-medium text-sm rounded-xl transition text-slate-200 flex items-center gap-2 border-[var(--theme-border)]">
            <i class="fa-solid fa-sliders text-[var(--theme-primary)]"></i> Yetkinlikler
          </a>
          <a href="dokumanlar.php" class="neon-glass-card hover:text-white font-medium text-sm rounded-xl px-5 py-3 transition text-slate-200 flex items-center gap-2 border-[var(--theme-border)]">
            <i class="fa-solid fa-folder-open text-[var(--theme-primary)]"></i> Dökümanlar
          </a>
          <a href="#iletisim" class="nav-scroll-link px-5 py-3 neon-glass-card hover:text-white font-medium text-sm rounded-xl transition text-slate-200 flex items-center gap-2 border-[var(--theme-border)]">
            <i class="fa-regular fa-paper-plane text-[var(--theme-primary)]"></i> İletişim
          </a>
        </div>

        <!-- 3'lü Hızlı İstatistik Metrikleri -->
        <div class="grid grid-cols-3 gap-3 pt-4 border-t border-[var(--theme-border)] max-w-lg mx-auto lg:mx-0">
          <div class="p-3 rounded-xl bg-blue-950/30 border border-[var(--theme-border)] text-center lg:text-left">
            <div class="text-xl font-extrabold text-[var(--theme-dot)]">3+ Yıl</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Sistem & BT Desteği</div>
          </div>
          <div class="p-3 rounded-xl bg-blue-950/30 border border-[var(--theme-border)] text-center lg:text-left">
            <div class="text-xl font-extrabold text-white">10+</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Web & PDF Aracı</div>
          </div>
          <div class="p-3 rounded-xl bg-blue-950/30 border border-[var(--theme-border)] text-center lg:text-left">
            <div class="text-xl font-extrabold text-emerald-400">7/24</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Operasyonel Destek</div>
          </div>
        </div>

      </div>

      <!-- SAĞ ALAN (Büyük Fotoğraf + macOS Butonlu Terminal Paneli) -->
      <div class="lg:col-span-5 relative mt-6 lg:mt-0 flex flex-col justify-between space-y-4">
        <div class="system-card-glow"></div>
        <div class="neon-glass-card rounded-3xl p-6 md:p-8 shadow-2xl relative space-y-6 border-[var(--theme-border)] bg-gradient-to-b from-blue-950/40 via-[#0a1020]/90 to-[#050811] flex-1 flex flex-col justify-between">
          
          <!-- Üst Profil Alanı (Büyük Fotoğraf & Bilgiler) -->
          <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-5 border-b border-[var(--theme-border)] text-center sm:text-left">
            
            <!-- BÜYÜK FOTOĞRAF ALANI (w-24 h-24) -->
            <div class="relative shrink-0">
              <div class="absolute -inset-1 bg-gradient-to-tr from-[var(--theme-primary)] via-blue-600 to-indigo-600 rounded-3xl blur opacity-70 group-hover:opacity-100 transition duration-300"></div>
              <?php if (!empty($a('profil_foto'))): ?>
                <div class="relative w-24 h-24 rounded-2xl overflow-hidden border-2 border-[var(--theme-primary)] bg-cyan-950/80 shadow-[0_0_20px_var(--theme-glow)]">
                  <img src="<?= $a('profil_foto') ?>" alt="Mevlüt Kaya" class="w-full h-full object-cover">
                </div>
              <?php else: ?>
                <div class="relative w-24 h-24 rounded-2xl bg-gradient-to-tr from-indigo-600 via-[var(--theme-secondary)] to-[var(--theme-primary)] flex items-center justify-center text-white font-black text-2xl shadow-[0_0_25px_var(--theme-glow)]">
                  MK
                </div>
              <?php endif; ?>
              <span class="absolute -bottom-1.5 -right-1.5 w-5 h-5 rounded-full bg-[#050811] border-2 border-[var(--theme-border)] flex items-center justify-center">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
              </span>
            </div>

            <!-- İsim, Unvan ve Konum -->
            <div class="flex-1 space-y-1.5">
              <div class="flex flex-wrap items-center justify-center sm:justify-between gap-2">
                <h3 class="text-white font-extrabold text-xl tracking-tight flex items-center gap-1.5">
                  Mevlüt Kaya <span class="text-[var(--theme-dot)] text-sm">⚡</span>
                </h3>
                <span class="px-2.5 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-[10px] font-bold text-emerald-400 flex items-center gap-1.5 shadow-[0_0_10px_rgba(16,185,129,0.3)]">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> ONLINE
                </span>
              </div>
              <div class="flex items-center justify-center sm:justify-start gap-1.5 text-xs text-[var(--theme-dot)] font-semibold">
                <i class="fa-solid fa-laptop-code text-[11px]"></i> Bilgi Sistemleri Uzmanı
              </div>
              <p class="text-xs text-slate-400 flex items-center justify-center sm:justify-start gap-1.5">
                <i class="fa-solid fa-hospital text-slate-500 text-[11px]"></i> Medicana International Ankara
              </p>
            </div>

          </div>

          <!-- Konsol / macOS Tarzı Terminal Paneli -->
          <div class="system-inner-panel rounded-2xl p-4 md:p-5 space-y-3.5 border border-[var(--theme-border)] flex-1 flex flex-col justify-between">
            
            <!-- macOS Üst Bar (Kırmızı - Sarı - Yeşil Butonlar + Dosya Adı + ACTIVE Rozeti) -->
            <div class="flex items-center justify-between border-b border-[var(--theme-border)] pb-2.5">
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 pr-2 border-r border-slate-700/50">
                  <span class="w-2.5 h-2.5 rounded-full bg-rose-500/90 shadow-[0_0_6px_rgba(244,63,94,0.6)]"></span>
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-500/90 shadow-[0_0_6px_rgba(245,158,11,0.6)]"></span>
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/90 shadow-[0_0_6px_rgba(16,185,129,0.6)]"></span>
                </div>
                <span class="font-mono text-xs text-[var(--theme-dot)] flex items-center gap-1.5">
                  <span class="text-slate-500">&gt;_</span> core_profile.json
                </span>
              </div>
              <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-950/80 text-[var(--theme-dot)] border border-[var(--theme-border)]">ACTIVE</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2.5 text-xs">
              <div class="p-3 rounded-xl bg-blue-950/40 border border-[var(--theme-border)] space-y-0.5">
                <span class="text-[10px] text-cyan-400 uppercase font-bold block tracking-wider">Uzmanlık</span>
                <span class="font-bold text-white text-sm">Hastane Bilişimi</span>
                <span class="text-[10px] text-slate-400 block">HIS / HBYS & Network</span>
              </div>
              <div class="p-3 rounded-xl bg-blue-950/40 border border-[var(--theme-border)] space-y-0.5">
                <span class="text-[10px] text-[var(--theme-dot)] uppercase font-bold block tracking-wider">Yazılım</span>
                <span class="font-bold text-white text-sm">PHP & MySQL</span>
                <span class="text-[10px] text-slate-400 block">Web Mimarisi & API</span>
              </div>
            </div>

            <div class="p-3 rounded-xl bg-blue-950/40 border border-[var(--theme-border)] text-xs flex justify-between items-center">
              <div>
                <span class="text-white font-bold block">Bartın Üniversitesi</span>
                <span class="text-[11px] text-slate-400">Bilgisayar Programcılığı</span>
              </div>
              <span class="text-emerald-400 font-mono font-extrabold text-xs bg-emerald-950/90 px-2.5 py-1 rounded border border-emerald-500/40 shadow-sm">GNO: 3.33</span>
            </div>
          </div>

        </div>

        <!-- Alt Sistem Durumu Çubuğu -->
        <div class="p-4 rounded-2xl bg-blue-950/30 border border-[var(--theme-border)] backdrop-blur-md flex items-center justify-between text-xs">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-950/80 border border-[var(--theme-border)] flex items-center justify-center text-[var(--theme-dot)] shadow-inner">
              <i class="fa-solid fa-server text-sm"></i>
            </div>
            <div>
              <div class="text-slate-200 font-semibold text-xs">Sistem Durumu & Altyapı</div>
              <div class="text-slate-500 text-[10px]">Active Directory • File Server • HBYS</div>
            </div>
          </div>
          <span class="inline-flex items-center gap-1.5 text-[11px] font-mono text-emerald-400 font-semibold bg-emerald-950/50 px-2.5 py-1 rounded-lg border border-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399]"></span> Çalışıyor
          </span>
        </div>

      </div>
    </section>

    <!-- TEK VE NET YETKİNLİKLER BÖLÜMÜ -->
    <section id="yetkinlikler" class="space-y-6">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-xs font-mono uppercase tracking-wider text-[var(--theme-primary)]">Yetkinlikler & Saha Uzmanlıkları</span>
          <h2 class="text-2xl font-bold text-white tracking-tight">Teknik Yetkinlikler</h2>
        </div>
        <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--theme-primary)]/40 via-blue-500/20 to-transparent"></div>
      </div>

      <div class="neon-glass-card rounded-2xl p-6 sm:p-8 border-[var(--theme-border)] space-y-6">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <div class="space-y-2.5">
            <div class="flex items-center gap-2.5 text-cyan-400 font-bold text-sm">
              <i class="fa-solid fa-hospital-user text-base"></i>
              <span>Hastane Bilişimi (HIS)</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
              HIS/HBYS operasyonları, medikal birim entegrasyonları, kullanıcı rol ve yetki matrisi yönetimi.
            </p>
            <div class="flex flex-wrap gap-1.5 pt-1">
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">HBYS</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">HIS</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Kullanıcı Desteği</span>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-center gap-2.5 text-blue-400 font-bold text-sm">
              <i class="fa-solid fa-server text-base"></i>
              <span>Sunucu & Denetim</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
              Windows Server, Active Directory, paylaşımlı klasör SACD nesne erişim denetimi ve silinme takibi.
            </p>
            <div class="flex flex-wrap gap-1.5 pt-1">
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Windows Server</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">secpol.msc</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Event ID</span>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-center gap-2.5 text-indigo-400 font-bold text-sm">
              <i class="fa-solid fa-code text-base"></i>
              <span>Web & Veritabanı</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
              Kurum içi yönetim ekranları, dinamik web sayfaları, veri listeleme ve MySQL mimarisi.
            </p>
            <div class="flex flex-wrap gap-1.5 pt-1">
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">PHP</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">MySQL</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">JavaScript</span>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-center gap-2.5 text-emerald-400 font-bold text-sm">
              <i class="fa-solid fa-screwdriver-wrench text-base"></i>
              <span>ITSM & Donanım</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
              Arıza ve çağrı süreç takibi (Lighthouse vb.), ağ yazıcıları, çevre birimleri ve donanım desteği.
            </p>
            <div class="flex flex-wrap gap-1.5 pt-1">
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Lighthouse</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Ağ / Yazıcı</span>
              <span class="text-[11px] px-2 py-0.5 rounded bg-blue-950/60 border border-[var(--theme-border)] text-[var(--theme-dot)]">Envanter</span>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- DENEYİM BÖLÜMÜ -->
    <section id="deneyim" class="space-y-6">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-xs font-mono uppercase tracking-wider text-[var(--theme-primary)]">Kariyer & Geçmiş</span>
          <h2 class="text-2xl font-bold text-white tracking-tight">İş Deneyimi</h2>
        </div>
        <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--theme-primary)]/40 via-blue-500/20 to-transparent"></div>
      </div>

      <div class="neon-glass-card p-6 sm:p-8 rounded-2xl border-l-4 border-l-[var(--theme-primary)] space-y-4">
        <div class="flex flex-wrap justify-between items-start gap-2">
          <div>
            <h3 class="text-xl font-bold text-white">Bilgi Sistemleri Uzmanı</h3>
            <p class="text-sm text-[var(--theme-dot)] font-medium mt-0.5">Medicana International Ankara Hastanesi</p>
          </div>
          <span class="px-3 py-1 rounded-full bg-blue-950/80 border border-[var(--theme-border)] text-xs font-semibold text-slate-300">
            2024 - Günümüz
          </span>
        </div>

        <ul class="text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed pt-1">
          <li><strong>Sağlık Bilgi Sistemleri (HIS/HBYS):</strong> Klinik ve poliklinik süreçlerinin kesintisiz çalışması, kullanıcı yetkilendirme ve sistem sürekliliği desteği.</li>
          <li><strong>Windows Dosya Sunucusu ve Güvenlik Denetimi:</strong> Paylaşılan dizinlerde nesne erişim denetimi (secpol.msc / SACD) kurgulanması, dosya silinme/değişiklik olay günlüklerinin (Event ID) izlenmesi.</li>
          <li><strong>ITSM & Arıza Süreçleri:</strong> Lighthouse arıza kayıt ve çağrı sisteminin operasyonel yönetimi, terminal, yazıcı ve donanım problemlerine hızlı müdahale.</li>
        </ul>

        <div class="pt-4 border-t border-[var(--theme-border)] flex flex-wrap justify-between items-center text-xs text-slate-400 gap-2">
          <span>🎓 <strong>Eğitim:</strong> Bartın Üniversitesi • Bilgisayar Programcılığı (GNO: 3.33)</span>
          <span class="text-[var(--theme-dot)] font-mono">Bilgisayar Programcısı</span>
        </div>
      </div>
    </section>

    <!-- BLOG & HABERLER VİTRİNİ -->
    <section id="blog" class="space-y-6">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div>
            <span class="text-xs font-mono uppercase tracking-wider text-[var(--theme-primary)]">Güncel Yazılar</span>
            <h2 class="text-2xl font-bold text-white tracking-tight">Blog & Haberler</h2>
          </div>
          <div class="hidden sm:block h-[1px] w-32 bg-gradient-to-r from-[var(--theme-primary)]/40 to-transparent"></div>
        </div>
        <a href="blog.php" class="px-3.5 py-1.5 rounded-xl bg-blue-950/60 hover:bg-blue-900/60 border border-[var(--theme-border)] text-xs font-semibold text-[var(--theme-dot)] hover:text-white transition flex items-center gap-1.5 shrink-0">
          Tümünü Gör <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <?php if (!empty($son_bloglar)): ?>
          <?php foreach ($son_bloglar as $b): ?>
            <article class="neon-glass-card rounded-2xl overflow-hidden flex flex-col justify-between group">
              <div>
                <a href="blog-detay.php?url=<?= htmlspecialchars($b['seo_url']) ?>" class="block relative aspect-video overflow-hidden bg-slate-900 border-b border-[var(--theme-border)]">
                  <?php if (!empty($b['gorsel'])): ?>
                    <img src="<?= htmlspecialchars($b['gorsel']) ?>" alt="<?= htmlspecialchars($b['baslik']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                  <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-950/80 to-[#070b16] text-[var(--theme-dot)] text-2xl font-mono">
                      <i class="fa-solid fa-newspaper"></i>
                    </div>
                  <?php endif; ?>
                  <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-bold bg-[#050811]/85 backdrop-blur-md border border-[var(--theme-border)] text-[var(--theme-dot)]">
                    <?= htmlspecialchars($b['kategori'] ?? 'Teknoloji') ?>
                  </span>
                </a>

                <div class="p-5 space-y-2">
                  <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                    <span><i class="fa-regular fa-calendar mr-1 text-[var(--theme-primary)]"></i> <?= date('d.m.Y', strtotime($b['olusturma_tarihi'])) ?></span>
                    <span><i class="fa-regular fa-eye mr-1 text-[var(--theme-primary)]"></i> <?= $b['okunma_sayisi'] ?></span>
                  </div>
                  <h3 class="text-sm font-bold text-white group-hover:text-[var(--theme-dot)] transition line-clamp-2">
                    <a href="blog-detay.php?url=<?= htmlspecialchars($b['seo_url']) ?>">
                      <?= htmlspecialchars($b['baslik']) ?>
                    </a>
                  </h3>
                  <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                    <?= htmlspecialchars($b['ozet'] ?: strip_tags(mb_substr($b['icerik'], 0, 120))) ?>
                  </p>
                </div>
              </div>

              <div class="px-5 pb-4 pt-1 flex items-center justify-between text-xs font-semibold">
                <a href="blog-detay.php?url=<?= htmlspecialchars($b['seo_url']) ?>" class="text-[var(--theme-dot)] group-hover:translate-x-1 transition flex items-center gap-1">
                  İncele <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-slate-500 text-sm italic col-span-3">Henüz yayınlanmış bir haber veya blog yazısı bulunmuyor.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- PROJELER BÖLÜMÜ -->
    <section id="projeler" class="space-y-6">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-xs font-mono uppercase tracking-wider text-[var(--theme-primary)]">Portföy</span>
          <h2 class="text-2xl font-bold text-white tracking-tight">Projeler</h2>
        </div>
        <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--theme-primary)]/40 via-blue-500/20 to-transparent"></div>
      </div>
      
      <div class="grid md:grid-cols-2 gap-6">
        <?php if (!empty($projeler)): ?>
          <?php foreach ($projeler as $proje): ?>
            <div class="neon-glass-card p-6 rounded-2xl flex flex-col justify-between space-y-4 group">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-semibold text-[var(--theme-dot)] uppercase tracking-wider bg-blue-950/60 px-2.5 py-0.5 rounded border border-[var(--theme-border)]">
                    <?= htmlspecialchars($proje['kategori'] ?? 'Genel', ENT_QUOTES, 'UTF-8') ?>
                  </span>
                  <div class="flex gap-3">
                    <?php if (!empty($proje['demo_link']) && $proje['demo_link'] !== '#'): ?>
                      <a href="<?= htmlspecialchars($proje['demo_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-[var(--theme-dot)] text-xs transition">Demo ↗</a>
                    <?php endif; ?>
                    <?php if (!empty($proje['github_link']) && $proje['github_link'] !== '#'): ?>
                      <a href="<?= htmlspecialchars($proje['github_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-[var(--theme-dot)] text-xs transition">Kod ↗</a>
                    <?php endif; ?>
                  </div>
                </div>
                <h3 class="text-xl font-bold text-white group-hover:text-[var(--theme-dot)] transition duration-200"><?= htmlspecialchars($proje['baslik'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                  <?= nl2br(htmlspecialchars($proje['aciklama'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                </p>
              </div>
              <div class="flex flex-wrap gap-2 pt-3 border-t border-[var(--theme-border)]">
                <?php 
                  $tags = explode(',', $proje['teknolojiler'] ?? '');
                  foreach ($tags as $tag):
                    $trimmedTag = trim($tag);
                    if ($trimmedTag === '') continue;
                ?>
                  <span class="text-xs px-2.5 py-1 rounded bg-blue-950/60 text-[var(--theme-dot)] border border-[var(--theme-border)]"><?= htmlspecialchars($trimmedTag, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-slate-500 text-sm italic col-span-2">Henüz proje eklenmemiş.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- İLETİŞİM BÖLÜMÜ (Yeni mesaj-gonder.php ve info@mevlutkaya.com.tr Entegrasyonu) -->
<!-- İLETİŞİM BÖLÜMÜ -->
    <section id="iletisim" class="space-y-6">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-xs font-mono uppercase tracking-wider text-[var(--theme-primary)]">İletişim • İşbirliği • Danışmanlık</span>
          <h2 class="text-2xl font-bold text-white tracking-tight">İletişime Geçin</h2>
        </div>
        <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--theme-primary)]/40 via-blue-500/20 to-transparent"></div>
      </div>

      <div class="neon-glass-card p-6 sm:p-8 rounded-2xl grid md:grid-cols-2 gap-8">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-white">Hastane Bilgi Sistemleri & Web Projeleri İçin İletişim</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Proje, entegrasyon, BT operasyonu, sunucu denetimi ve süreç iyileştirme konularında birlikte çalışabiliriz.
          </p>

          <!-- CANLI MESAİ DURUMU ROZETİ (Görseldeki Tasarım) -->
          <div class="flex flex-wrap items-center gap-3 pt-1">
            <?php if ($mesai_ici): ?>
              <!-- Mesai İçi: Yeşil Müsait -->
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 text-xs font-bold shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Müsait
              </span>
            <?php else: ?>
              <!-- Mesai Dışı: Kırmızı -->
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-950/80 border border-rose-500/40 text-rose-400 text-xs font-bold shadow-[0_0_15px_rgba(244,63,94,0.3)]">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Mesai Dışı
              </span>
            <?php endif; ?>

            <div class="flex items-center gap-1.5 text-xs text-slate-300 font-medium bg-blue-950/40 px-3 py-1.5 rounded-xl border border-[var(--theme-border)]">
              <i class="fa-regular fa-clock text-[var(--theme-dot)]"></i>
              <span>Hafta içi 8:30 - 17:30</span>
            </div>
          </div>

          <div class="pt-2 space-y-2.5 text-sm border-t border-[var(--theme-border)]">
            <div class="flex items-center gap-3 text-slate-300">
              <span class="text-[var(--theme-primary)] text-base"><i class="fa-solid fa-location-dot"></i></span> 
              <?= $a('iletisim_sehir', 'Ankara, Türkiye') ?>
            </div>
            <div class="flex items-center gap-3 text-slate-300">
              <span class="text-[var(--theme-primary)] text-base"><i class="fa-solid fa-envelope"></i></span> 
              <a href="mailto:info@mevlutkaya.com.tr" class="hover:text-[var(--theme-dot)] underline underline-offset-4 decoration-[var(--theme-primary)]/50 break-all">info@mevlutkaya.com.tr</a>
            </div>
          </div>
        </div>

        <!-- Sağ Taraf: Mesaj Formu (Aynı Kalıyor) -->
        <form action="mesaj-gonder.php" method="POST" class="space-y-4">
          <!-- Form inputlarınız aynen korunuyor -->

        <!-- DOĞRUDAN YEREL MESAJ-GONDER.PHP İLE ÇALIŞAN FORM -->
        <form action="mesaj-gonder.php" method="POST" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Adınız / Kurum</label>
            <input type="text" name="ad" placeholder="Adınız Soyadınız" required class="w-full px-4 py-2.5 rounded-lg bg-blue-950/40 border border-[var(--theme-border)] focus:outline-none focus:border-[var(--theme-primary)] text-sm text-white placeholder-slate-500 shadow-inner">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">E-posta Adresiniz</label>
            <input type="email" name="eposta" placeholder="ornek@sirket.com" required class="w-full px-4 py-2.5 rounded-lg bg-blue-950/40 border border-[var(--theme-border)] focus:outline-none focus:border-[var(--theme-primary)] text-sm text-white placeholder-slate-500 shadow-inner">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Mesajınız</label>
            <textarea name="mesaj" rows="3" placeholder="Mesajınız veya proje detayı..." required class="w-full px-4 py-2.5 rounded-lg bg-blue-950/40 border border-[var(--theme-border)] focus:outline-none focus:border-[var(--theme-primary)] text-sm text-white placeholder-slate-500 shadow-inner"></textarea>
          </div>
          <button type="submit" class="w-full py-3 neon-btn-primary text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
            <i class="fa-regular fa-paper-plane"></i> Mesajı Gönder
          </button>
        </form>
      </div>
    </section>

    <!-- Footer -->
    <footer class="pt-8 border-t border-[var(--theme-border)] flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 text-center sm:text-left">
      <p>© <?= date('Y') ?> Mevlüt Kaya. Tüm Hakları Saklıdır. | mevlutkaya.com.tr</p>
      <div class="flex gap-6">
        <a href="<?= $a('link_linkedin', '#') ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--theme-dot)] transition">LinkedIn</a>
        <a href="<?= $a('link_github', '#') ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--theme-dot)] transition">GitHub</a>
        <a href="admin/login.php" class="hover:text-slate-400 text-slate-700 transition">Yönetici Girişi</a>
      </div>
    </footer>

  </main>

  <script>
    function setTheme(themeName) {
      document.documentElement.setAttribute('data-theme', themeName);
      localStorage.setItem('site_theme', themeName);
    }
    const savedTheme = localStorage.getItem('site_theme') || 'cyan';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Mobil Menü
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
      mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
      });
    }

    // Yumuşak Kaydırma
    document.querySelectorAll('.nav-scroll-link, a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (targetId && targetId.startsWith('#') && targetId.length > 1) {
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            e.preventDefault();
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
              mobileMenu.classList.add('hidden');
            }
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.pushState(null, null, targetId);
          }
        }
      });
    });

    // Özel Cursor ve Spotlight
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || window.matchMedia('(pointer: coarse)').matches;
    if (!isTouchDevice) {
      const dot = document.querySelector('.cursor-dot');
      const outline = document.querySelector('.cursor-outline');
      const spotlight = document.querySelector('.mouse-spotlight');

      if (dot && outline && spotlight) {
        let mouseX = window.innerWidth / 2;
        let mouseY = window.innerHeight / 2;
        let outlineX = mouseX;
        let outlineY = mouseY;

        window.addEventListener('mousemove', (e) => {
          mouseX = e.clientX;
          mouseY = e.clientY;
          dot.style.left = `${mouseX}px`;
          dot.style.top = `${mouseY}px`;
          spotlight.style.left = `${mouseX}px`;
          spotlight.style.top = `${mouseY}px`;
        });

        function animateOutline() {
          outlineX += (mouseX - outlineX) * 0.15;
          outlineY += (mouseY - outlineY) * 0.15;
          outline.style.left = `${outlineX}px`;
          outline.style.top = `${outlineY}px`;
          requestAnimationFrame(animateOutline);
        }
        animateOutline();

        const interactiveElements = document.querySelectorAll('a, button, input, textarea, .neon-glass-card, [onclick]');
        interactiveElements.forEach((el) => {
          el.addEventListener('mouseenter', () => {
            outline.classList.add('cursor-hover');
            dot.style.transform = 'translate(-50%, -50%) scale(1.4)';
            dot.style.backgroundColor = '#818cf8';
          });
          el.addEventListener('mouseleave', () => {
            outline.classList.remove('cursor-hover');
            dot.style.transform = 'translate(-50%, -50%) scale(1)';
            dot.style.backgroundColor = 'var(--theme-dot)';
          });
        });

        window.addEventListener('mousedown', () => {
          outline.style.transform = 'translate(-50%, -50%) scale(0.85)';
        });
        window.addEventListener('mouseup', () => {
          outline.style.transform = 'translate(-50%, -50%) scale(1)';
        });
      }
    }

    // Yerel mesaj-gonder.php AJAX İletişim Formu Kontrolü
    const form = document.querySelector('#iletisim form');
    if (form) {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        const orijinalYazi = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gönderiliyor...';
        btn.disabled = true;

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            body: formData
          });
          const res = await response.json();

          if (res.status === 'success') {
            alert(res.message);
            form.reset();
          } else {
            alert(res.message || 'Bir hata oluştu, lütfen tekrar deneyin.');
          }
        } catch (error) {
          alert('Bağlantı hatası oluştu, lütfen internet bağlantınızı kontrol edin.');
        } finally {
          btn.innerHTML = orijinalYazi;
          btn.disabled = false;
        }
      });
    }
  </script>
</body>
</html>

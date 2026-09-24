<?php
require_once 'baglan.php';
require_once 'sayac.php';

// Ayarları ve dinamik menüleri çek
$ayarlar_raw =$db->query("SELECT * FROM ayarlar")->fetchAll(PDO::FETCH_KEY_PAIR);
$a = function($key, $default = '') use ($ayarlar_raw) {
    return htmlspecialchars($ayarlar_raw[$key] ?? $default);
};

// İndirme Sayacını Artırma ve Orijinal İsimle İndirme Endpoint'i
if (isset($_GET['indir'])) {
    $indir_id = (int)$_GET['indir'];
    $stmt = $db->prepare("SELECT * FROM dokumanlar WHERE id = ?");
    $stmt->execute([$indir_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc) {
        // İndirme sayısını 1 artır
        $db->prepare("UPDATE dokumanlar SET indirme_sayisi = indirme_sayisi + 1 WHERE id = ?")->execute([$indir_id]);
        
        $dosya_yolu = $doc['dosya_yolu'];

        // Harici link (Google Drive, harici URL) ise doğrudan yönlendir
        if (strpos($dosya_yolu, 'http://') === 0 || strpos($dosya_yolu, 'https://') === 0) {
            header("Location: " . $dosya_yolu);
            exit;
        }

        // Sunucudaki yerel dosya ise
        if (file_exists($dosya_yolu)) {
            // Dosyanın uzantısını al (.exe, .zip, .pdf vs.)
            $uzanti = pathinfo($dosya_yolu, PATHINFO_EXTENSION);
            
            // Başlıktan temiz ve güvenli dosya adı üret (örn: "Anydesk" -> "Anydesk.exe")
            $guvenli_baslik = preg_replace('/[^a-zA-Z0-9_\-\s]/u', '', $doc['baslik']);
            $guvenli_baslik = trim(str_replace(' ', '_', $guvenli_baslik));
            if (empty($guvenli_baslik)) {
                $guvenli_baslik = 'dosya';
            }
            $indirme_adi = $guvenli_baslik . '.' . $uzanti;

            // İndirme başlıklarını gönder
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $indirme_adi . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($dosya_yolu));
            
            // Çıktı tamponunu temizleyip dosyayı bas
            if (ob_get_level()) {
                ob_end_clean();
            }
            readfile($dosya_yolu);
            exit;
        } else {
            header("Location: " . $dosya_yolu);
            exit;
        }
    }
}

// Dökümanları Listele
$dokumanlar =$db->query("SELECT * FROM dokumanlar ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth" data-theme="cyan">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dökümanlar & Setuplar | <?= $a('site_baslik', 'Mevlüt Kaya') ?></title>
  
  <meta name="robots" content="index, follow">
  <meta name="keywords" content="Mevlüt Kaya, Bilgi Sistemleri Uzmanı, IT Operasyon, Hastane Bilgi Sistemleri, Dökümanlar, Sürücüler, Kurulum Setupları">
  <link rel="canonical" href="https://mevlutkaya.com.tr/dokumanlar.php">

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
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #050811; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
    
    /* Özel İmleç Stilleri */
    @media (hover: hover) and (pointer: fine) {
      body, a, button, input, textarea {
        cursor: none !important;
      }
    }

    .cursor-dot {
      width: 8px;
      height: 8px;
      background-color: var(--theme-dot);
      border-radius: 50%;
      position: fixed;
      top: 0;
      left: 0;
      pointer-events: none;
      z-index: 9999;
      box-shadow: 0 0 10px var(--theme-dot), 0 0 20px var(--theme-primary);
      transition: transform 0.08s ease-out, background-color 0.2s ease;
      transform: translate(-50%, -50%);
    }

    .cursor-outline {
      width: 36px;
      height: 36px;
      border: 1.5px solid var(--theme-border);
      border-radius: 50%;
      position: fixed;
      top: 0;
      left: 0;
      pointer-events: none;
      z-index: 9998;
      box-shadow: 0 0 15px var(--theme-glow);
      transition: width 0.25s ease, height 0.25s ease, border-color 0.25s ease, background-color 0.25s ease;
      transform: translate(-50%, -50%);
    }

    .cursor-hover {
      width: 54px !important;
      height: 54px !important;
      border-color: rgba(129, 140, 248, 0.8) !important;
      background: rgba(99, 102, 241, 0.12) !important;
      box-shadow: 0 0 25px rgba(99, 102, 241, 0.4) !important;
    }

    .mouse-spotlight {
      position: fixed;
      top: 0;
      left: 0;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--theme-glow) 0%, rgba(99, 102, 241, 0.03) 40%, transparent 70%);
      pointer-events: none;
      z-index: 1;
      transform: translate(-50%, -50%);
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
      transform: translateY(-3px);
      box-shadow: 0 0 25px var(--theme-glow), 0 10px 30px -10px rgba(37, 99, 235, 0.4);
    }
  </style>
</head>
<body class="text-slate-200 relative min-h-screen selection:bg-[var(--theme-primary)] selection:text-white bg-grid-neon overflow-x-hidden flex flex-col justify-between">

  <!-- Özel İmleç Elementleri -->
  <div class="cursor-dot hidden md:block"></div>
  <div class="cursor-outline hidden md:block"></div>
  <div class="mouse-spotlight hidden md:block"></div>

  <!-- Arka Plan Işık Efektleri -->
  <div class="fixed top-[-60px] left-1/2 -translate-x-1/2 w-[750px] h-[380px] bg-gradient-to-tr from-[var(--theme-primary)]/25 via-blue-600/20 to-indigo-600/15 blur-[130px] pointer-events-none -z-10"></div>
  <div class="fixed bottom-10 right-[-100px] w-[600px] h-[380px] bg-gradient-to-tl from-[var(--theme-primary)]/20 via-blue-600/20 to-transparent blur-[140px] pointer-events-none -z-10"></div>

  <!-- ÜST MENÜ (Birebir Uyumlu, Logo ve Linkler Tam) -->
  <nav class="sticky top-0 z-50 backdrop-blur-xl bg-[#050811]/85 border-b border-[var(--theme-border)] shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between gap-2 sm:gap-4">
      
      <!-- Logo & Profil Başlığı -->
      <a href="index.php" class="group flex items-center gap-3 text-white font-bold tracking-wider transition shrink-0">
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

      <!-- Masaüstü Menü Linkleri (Dökümanlar Aktif) -->
      <div class="hidden xl:flex items-center gap-1 p-1.5 rounded-2xl bg-blue-950/20 border border-[var(--theme-border)] backdrop-blur-md whitespace-nowrap">
        <a href="index.php#hakkimda" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Hakkımda</a>
        <a href="index.php#yetkinlikler" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Yetkinlikler</a>
        <a href="index.php#deneyim" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Deneyim</a>
        <a href="blog.php" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Blog</a>
        <a href="index.php#projeler" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">Projeler</a>
        <a href="pdf-araclari.php" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">PDF Araçları</a>
        <!-- Aktif Sayfa Rozeti -->
        <a href="dokumanlar.php" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-[var(--theme-primary)]/20 border border-[var(--theme-primary)]/40 shadow-sm transition">Dökümanlar</a>
        <a href="index.php#iletisim" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/10 transition">İletişim</a>
      </div>

      <!-- Tema & CV & Mobil Buton -->
      <div class="flex items-center gap-2.5 shrink-0">
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-blue-950/40 border border-[var(--theme-border)]">
          <button onclick="setTheme('cyan')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-cyan-500 shadow-sm transition transform hover:scale-110" title="Cyber Cyan"></button>
          <button onclick="setTheme('green')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-emerald-500 shadow-sm transition transform hover:scale-110" title="Matrix Green"></button>
          <button onclick="setTheme('amber')" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg bg-amber-500 shadow-sm transition transform hover:scale-110" title="Amber Hacker"></button>
        </div>

        <a href="cv.pdf" download class="hidden sm:flex px-3 py-1.5 rounded-xl bg-gradient-to-r from-blue-600/20 to-[var(--theme-primary)]/20 border border-[var(--theme-primary)]/40 text-[var(--theme-dot)] text-xs font-bold hover:text-white transition items-center gap-1.5">
          <i class="fa-solid fa-download text-[11px]"></i> CV İndir
        </a>

        <!-- Hamburger Menü Butonu -->
        <button id="mobile-menu-btn" class="xl:hidden p-2 rounded-xl bg-blue-950/40 border border-[var(--theme-border)] text-[var(--theme-dot)] hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
      </div>

    </div>

    <!-- Mobil Menü Açılır Alanı -->
    <div id="mobile-menu" class="hidden xl:hidden px-4 pt-2 pb-6 space-y-2 border-t border-[var(--theme-border)] bg-[#050811]/95 backdrop-blur-2xl">
      <a href="index.php#hakkimda" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Hakkımda</a>
      <a href="index.php#yetkinlikler" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Yetkinlikler</a>
      <a href="index.php#deneyim" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Deneyim</a>
      <a href="blog.php" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Blog</a>
      <a href="index.php#projeler" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">Projeler</a>
      <a href="pdf-araclari.php" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">PDF Araçları</a>
      <a href="dokumanlar.php" class="block px-4 py-2 rounded-xl text-sm font-bold text-[var(--theme-dot)] bg-[var(--theme-primary)]/20">Dökümanlar</a>
      <a href="index.php#iletisim" class="block px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-[var(--theme-primary)]/20">İletişim</a>
    </div>
  </nav>

  <!-- ANA İÇERİK ALANI -->
  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-12 space-y-8 relative z-10 w-full flex-1">
    <div>
      <h1 class="text-3xl font-bold text-slate-100 tracking-tight">Dökümanlar & Setuplar</h1>
      <p class="text-sm text-slate-400 mt-1">Sürücüler, kurulum setupları ve videolu rehberler. Yeni araçlar düzenli olarak eklenir.</p>
    </div>

    <!-- 3'lü Grid Kart Düzeni (PDF Araçları ile Birebir Aynı) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if (!empty($dokumanlar)): ?>
        <?php foreach ($dokumanlar as$doc): ?>
          <div class="neon-glass-card rounded-2xl p-6 flex flex-col justify-between group cursor-pointer" onclick='detayAc(<?= json_encode($doc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
            
            <div>
              <!-- Üst Rozetler ve İndirme Sayısı -->
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                  <span class="text-[10px] bg-blue-950 text-blue-400 px-2 py-0.5 rounded-full border border-blue-800 font-semibold">
                    <?= htmlspecialchars($doc['kategori'] ?? 'Yazılım') ?>
                  </span>
                  <span class="text-[10px] bg-cyan-950 text-cyan-400 px-2 py-0.5 rounded-full border border-cyan-800 font-semibold font-mono">
                    <?= htmlspecialchars($doc['dosya_turu'] ?? 'EXE') ?> <?= !empty($doc['dosya_boyut']) && $doc['dosya_boyut'] !== '-' ? '• ' . htmlspecialchars($doc['dosya_boyut']) : '' ?>
                  </span>
                </div>
                <span class="text-[11px] text-slate-500 font-mono flex items-center gap-1">
                  <i class="fa-solid fa-download text-[10px] text-cyan-400"></i> <?= $doc['indirme_sayisi'] ?? 0 ?>
                </span>
              </div>

              <!-- Başlık ve Varsa Küçük Kapak Logosu -->
              <div class="flex items-center gap-3 mb-1">
                <?php if (!empty($doc['gorsel']) && file_exists($doc['gorsel'])): ?>
                  <img src="<?= htmlspecialchars($doc['gorsel']) ?>" alt="Logo" class="w-8 h-8 rounded-lg object-contain bg-slate-900 border border-blue-900/40 p-1 shrink-0">
                <?php endif; ?>
                <h3 class="text-lg font-bold text-slate-100 group-hover:text-cyan-400 transition truncate">
                  <?= htmlspecialchars($doc['baslik']) ?>
                </h3>
              </div>

              <!-- Kısa Açıklama -->
              <p class="text-xs text-slate-400 mt-2 leading-relaxed line-clamp-2">
                <?= !empty($doc['aciklama']) ? htmlspecialchars($doc['aciklama']) : 'Rehber ve kurulum adımları için tıklayın...' ?>
              </p>
            </div>

            <!-- Kart Altı "Kılavuzu İncele →" ve İndir Butonu -->
            <div class="mt-6 pt-4 border-t border-blue-900/20 flex items-center justify-between">
              <span class="text-xs font-bold text-cyan-400 group-hover:text-cyan-300 inline-flex items-center space-x-1">
                <span>Kılavuzu İncele</span><span>→</span>
              </span>

              <a href="dokumanlar.php?indir=<?= $doc['id'] ?>" onclick="event.stopPropagation();" class="px-3 py-1 rounded-lg bg-cyan-950/70 border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500 hover:text-black font-semibold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-download text-[10px]"></i> İndir
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full neon-glass-card p-8 rounded-2xl text-center text-slate-400 text-sm">
          Henüz döküman veya setup eklenmedi. Yönetim panelinden yeni döküman ekleyebilirsiniz.
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- İÇERİK DETAY VE REHBER MODALI (TIKLAYINCA AÇILAN POPUP) -->
  <div id="rehberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden opacity-0 transition-opacity duration-200">
    <div class="neon-glass-card rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col border border-cyan-500/40 overflow-hidden shadow-2xl">
      
      <!-- Modal Başlık -->
      <div class="px-6 py-4 border-b border-blue-900/30 flex items-center justify-between bg-slate-950/70">
        <div class="flex items-center gap-3">
          <img id="mGorsel" src="" alt="" class="w-9 h-9 rounded-lg object-contain bg-slate-900 border border-cyan-500/30 p-1 hidden">
          <div id="mIkon" class="w-9 h-9 rounded-lg bg-slate-900 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
            <i class="fa-solid fa-cube"></i>
          </div>
          <div>
            <h3 id="mBaslik" class="text-base font-bold text-white"></h3>
            <span id="mKategori" class="text-[11px] text-cyan-400 font-mono"></span>
          </div>
        </div>
        <button onclick="detayKapat()" class="w-8 h-8 rounded-lg bg-slate-900/80 border border-blue-900/40 text-slate-400 hover:text-white flex items-center justify-center">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Modal Gövde (Kaydırılabilir) -->
      <div class="p-6 overflow-y-auto space-y-5 text-xs sm:text-sm">
        
        <!-- Video Alanı -->
        <div id="mVideoAlani" class="hidden">
          <h4 class="text-slate-300 font-bold mb-2 flex items-center gap-2 text-rose-400">
            <i class="fa-brands fa-youtube text-base"></i> Video Kurulum Rehberi
          </h4>
          <div class="aspect-video w-full rounded-xl overflow-hidden border border-rose-900/40 bg-black shadow-lg">
            <iframe id="mVideoFrame" class="w-full h-full" src="" frameborder="0" allowfullscreen></iframe>
          </div>
        </div>

        <!-- Kurulum Adımları & Açıklama -->
        <div>
          <h4 class="text-slate-300 font-bold mb-2 flex items-center gap-2 text-cyan-300">
            <i class="fa-solid fa-list-check text-base"></i> Kurulum Adımları & Bilgiler
          </h4>
          <div id="mAciklama" class="p-4 rounded-xl bg-slate-950/70 border border-blue-900/30 text-slate-300 whitespace-pre-line leading-relaxed text-xs sm:text-sm font-sans"></div>
        </div>

      </div>

      <!-- Modal Altı -->
      <div class="px-6 py-4 border-t border-blue-900/30 bg-slate-950/80 flex items-center justify-between">
        <span id="mBilgi" class="text-xs text-slate-400 font-mono"></span>
        <a id="mIndirLink" href="#" class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-bold flex items-center gap-2 shadow-lg transition">
          <i class="fa-solid fa-download"></i> Dosyayı İndir
        </a>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <footer class="max-w-6xl mx-auto px-4 sm:px-6 pt-12 pb-8 border-t border-[var(--theme-border)] flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 text-center sm:text-left relative z-10 w-full">
    <p>© <?= date('Y') ?> Mevlüt Kaya. Tüm Hakları Saklıdır. | mevlutkaya.com.tr</p>
    <div class="flex gap-6">
      <a href="<?= $a('link_linkedin', '#') ?>" target="_blank" class="hover:text-[var(--theme-dot)] transition">LinkedIn</a>
      <a href="<?= $a('link_github', '#') ?>" target="_blank" class="hover:text-[var(--theme-dot)] transition">GitHub</a>
      <a href="admin/login.php" class="hover:text-slate-400 text-slate-700 transition">Yönetici Girişi</a>
    </div>
  </footer>

  <!-- Tema ve Özel İmleç Takip Scriptleri -->
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

    // Fare İmleci & Spotlight Takip Mantığı
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
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

    // Modal Fonksiyonları
    function youtubeIdBul(url) {
      if (!url) return null;
      var match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
      return match ? match[1] : null;
    }

    function detayAc(doc) {
      document.getElementById('mBaslik').innerText = doc.baslik || '';
      document.getElementById('mKategori').innerText = (doc.kategori || 'Genel') + ' • ' + (doc.dosya_turu || 'DOSYA');
      document.getElementById('mBilgi').innerText = 'Boyut: ' + (doc.dosya_boyut || '-') + ' | ' + (doc.indirme_sayisi || 0) + ' indirme';
      document.getElementById('mAciklama').innerText = doc.aciklama || 'Bu döküman için ek bir açıklama girilmemiştir.';
      document.getElementById('mIndirLink').href = 'dokumanlar.php?indir=' + doc.id;

      var gorselEl = document.getElementById('mGorsel');
      var ikonEl = document.getElementById('mIkon');
      if (doc.gorsel) {
        gorselEl.src = doc.gorsel;
        gorselEl.classList.remove('hidden');
        ikonEl.classList.add('hidden');
      } else {
        gorselEl.classList.add('hidden');
        ikonEl.classList.remove('hidden');
      }

      var videoAlani = document.getElementById('mVideoAlani');
      var videoFrame = document.getElementById('mVideoFrame');
      var ytid = youtubeIdBul(doc.video_url);
      if (ytid) {
        videoFrame.src = 'https://www.youtube.com/embed/' + ytid;
        videoAlani.classList.remove('hidden');
      } else {
        videoFrame.src = '';
        videoAlani.classList.add('hidden');
      }

      var modal = document.getElementById('rehberModal');
      modal.classList.remove('hidden');
      setTimeout(function() {
        modal.classList.remove('opacity-0');
      }, 10);
    }

    function detayKapat() {
      var modal = document.getElementById('rehberModal');
      modal.classList.add('opacity-0');
      setTimeout(function() {
        modal.classList.add('hidden');
        document.getElementById('mVideoFrame').src = '';
      }, 200);
    }

    document.getElementById('rehberModal').addEventListener('click', function(e) {
      if (e.target === this) {
        detayKapat();
      }
    });
  </script>
</body>
</html>
CREATE TABLE IF NOT EXISTS `ayarlar` (
  `anahtar` varchar(100) NOT NULL,
  `deger` text DEFAULT NULL,
  PRIMARY KEY (`anahtar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sayfalar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `seo_url` varchar(255) NOT NULL,
  `icerik` longtext DEFAULT NULL,
  `menu_tipi` varchar(50) DEFAULT 'ana_menu',
  `menu_goster` tinyint(1) DEFAULT 1,
  `sira` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `seo_url` varchar(255) NOT NULL,
  `ozet` text DEFAULT NULL,
  `icerik` longtext DEFAULT NULL,
  `gorsel` varchar(255) DEFAULT NULL,
  `okunma_sayisi` int(11) DEFAULT 0,
  `sira` int(11) DEFAULT 0,
  `tarih` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `dokumanlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT 'Genel',
  `dosya_yolu` varchar(255) NOT NULL,
  `dosya_turu` varchar(20) DEFAULT 'LINK',
  `dosya_boyut` varchar(50) DEFAULT '-',
  `gorsel` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `indirme_sayisi` int(11) DEFAULT 0,
  `olusturuldu` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ziyaretler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_hash` varchar(64) DEFAULT NULL,
  `sayfa` varchar(255) DEFAULT NULL,
  `ziyaretci_id` varchar(64) DEFAULT NULL,
  `tarih` date DEFAULT NULL,
  `olusturuldu` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mesajlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_soyad` varchar(150) NOT NULL,
  `eposta` varchar(150) NOT NULL,
  `mesaj` text NOT NULL,
  `tarih` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kullanicilar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
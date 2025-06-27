# Esistenze Tool-Kit

WordPress için kapsamlı araç seti. Hızlı menü kartları, fiyat düzenleyici, akıllı ürün butonları, kategori stilleyici ve özel üst çubuk özelliklerini içerir.

## Özellikler

### 🎴 Hızlı Menü Kartları
- Görsel, başlık ve açıklama içeren modern buton grupları
- Grid ve banner görünüm seçenekleri
- Kısa kodlar: `[hizli_menu id=0]` ve `[hizli_menu_banner id=0]`

### 💰 Fiyat Düzenleyici (WooCommerce)
- Ürün fiyatlarına özel notlar ekleme
- Modern gradient tasarım
- Responsive tasarım

### 🔘 Akıllı Ürün Butonları (WooCommerce)
- Telefon, e-posta, WhatsApp butonları
- Contact Form 7 entegrasyonu
- Özelleştirilebilir renkler ve ikonlar

### 🎨 Kategori Stilleyici (WooCommerce)
- Modern kategori grid tasarımı
- Sidebar menü stilleri
- Sayfa başlığı düzenlemeleri
- Kısa kod: `[display_categories]`

### 📊 Özel Üst Çubuk
- Menü ve iletişim bilgileri
- Özelleştirilebilir renkler
- Responsive tasarım

## Kurulum

1. Eklenti dosyalarını `/wp-content/plugins/esistenze-tool-kit/` klasörüne yükleyin
2. WordPress admin panelinden eklentiyi aktifleştirin
3. "Esistenze Tools" menüsünden modülleri yönetin

## Gereksinimler

- WordPress 5.0+
- PHP 7.4+
- WooCommerce (bazı modüller için)

## Kullanım

### Hızlı Menü Kartları
1. **Esistenze Tools > Menü Kartları**'na gidin
2. "Yeni Grup Oluştur" butonuna tıklayın
3. Kartlarınızı ekleyin (görsel, başlık, açıklama, bağlantı)
4. Sayfalarınızda kısa kod kullanın:
   - Grid görünüm: `[hizli_menu id=0]`
   - Banner görünüm: `[hizli_menu_banner id=0]`

### Fiyat Düzenleyici
1. **Esistenze Tools > Fiyat Düzenleyici**'ye gidin
2. Fiyat notunuzu yazın
3. Ayarları kaydedin
4. WooCommerce ürün sayfalarında otomatik görünür

### Akıllı Ürün Butonları
1. **Esistenze Tools > Ürün Butonları**'na gidin
2. "Yeni Buton Ekle" butonuna tıklayın
3. Buton türü, renk ve bilgileri ayarlayın
4. WooCommerce ürün sayfalarında otomatik görünür

### Kategori Stilleyici
1. **Esistenze Tools > Kategori Stilleyici**'ye gidin
2. Ayarlar otomatik aktiftir
3. Kategori sayfalarında `[display_categories]` kısa kodunu kullanın

### Özel Üst Çubuk
1. **Esistenze Tools > Üst Çubuk**'a gidin
2. Menü, telefon ve e-posta bilgilerini girin
3. Renk ayarlarını yapın
4. Sitenizin üst kısmında otomatik görünür

## Kısa Kodlar

- `[hizli_menu id=0]` - Hızlı menü kartları (grid)
- `[hizli_menu_banner id=0]` - Hızlı menü kartları (banner)
- `[display_categories]` - Stil kategoriler

## Teknik Detaylar

### Dosya Yapısı
```
esistenze-tool-kit/
├── esistenze-tool-kit.php          # Ana plugin dosyası
├── modules/                         # Modül dosyaları
│   ├── meta-cards/
│   ├── price-modifier/
│   ├── smart-product-buttons/
│   ├── category-styler/
│   └── custom-topbar/
├── assets/                          # CSS ve JS dosyaları
│   ├── css/
│   └── js/
└── README.md
```

### Hooks ve Filtreler
- `esistenze_toolkit_modules` - Modül listesini filtrelemek için
- `esistenze_toolkit_assets` - Asset listesini filtrelemek için

## Güncelleme Notları

### v1.5.1 - Yetki Sorunu Düzeltmesi
- **Hook Sıralaması Düzeltmesi:** Ana menü öncelikli olarak kayıt edilir (priority 5)
- **Modül Yükleme Sıralaması:** Modüller ana menüden sonra yüklenir (priority 10)
- **Gelişmiş Debug Panel:** Her zaman görünür detaylı yetki kontrolü
- **Debug CSS:** Özel debug panel stilleri
- **Yetki Tanılaması:** Super admin kontrolü ve detaylı kullanıcı bilgileri
- **Kapsamlı Hata Tespiti:** "Ne yazık ki, bu sayfaya erişmenize izin verilmiyor" sorunu için çözüm

### v1.5.0
- **Dosya İsimlendirme:** CSS/JS dosyaları modüllerine göre yeniden isimlendirildi
- **Tam Ayrıştırma:** Smart Product Buttons ve Custom Topbar modüllerinde kalan inline kodlar temizlendi
- **Dosya Organizasyonu:** 
  - `admin.css` → `dashboard-admin.css`
  - `public.css` → `toolkit-public.css`
  - `admin.js` → `dashboard-admin.js`
  - `public.js` → `toolkit-public.js`
- **Yeni CSS Dosyaları:** `smart-product-buttons.css`, `custom-topbar.css`
- **Yeni JS Dosyaları:** `smart-product-buttons.js`
- **Modern CSS:** CSS variables kullanarak dinamik renk sistemi
- **Gelişmiş Modal:** Smart buttons için ESC key desteği

### v1.4.0
- **Kod Ayrıştırması:** Tüm inline CSS ve JavaScript kodları ayrı dosyalara çıkarıldı
- **Performans:** Modüler CSS/JS dosya yükleme sistemi
- **Temizlik:** PHP dosyalarından inline kodlar kaldırıldı
- **Organizasyon:** CSS ve JS dosyaları assets klasöründe düzenlendi
- **Optimize:** Sayfa yükleme hızı artırıldı
- **Bakım:** Kod bakımı ve güncellemesi kolaylaştırıldı

### v1.3.0
- Modüler yapı yeniden düzenlendi
- Tüm modül dosyları tek klasörde toplandı
- Gereksiz alt klasörler kaldırıldı
- Daha temiz ve yönetilebilir dosya yapısı
- CSS dosyaları assets klasöründe birleştirildi
- Kod organizasyonu optimize edildi

### v1.2.0
- Kod temizliği ve optimizasyon
- Gereksiz dosyalar silindi
- Temiz dosya yapısı
- Performans iyileştirmeleri
- Yetki sistemi stabilizasyonu

### v1.1.0
- Modern ve sade dashboard tasarımı
- Geliştirilmiş kullanıcı deneyimi
- Optimize edilmiş yetki sistemi
- Daha temiz UI/UX

### v1.0.0
- İlk sürüm
- 5 ana modül
- Modern admin paneli
- Responsive tasarım

## Destek

Teknik destek için: [esistenze.com](https://esistenze.com)

## Lisans

GPL v2 veya üzeri

## Geliştirici

**Cem Karabulut - Esistenze**
- Website: [esistenze.com](https://esistenze.com)
- WordPress eklenti geliştirme uzmanı

---

*Bu eklenti WordPress standartlarına uygun olarak geliştirilmiştir ve sürekli güncellenmektedir.* 
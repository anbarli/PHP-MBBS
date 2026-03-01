# TODO

## ✅ Tamamlanan İyileştirmeler (Mart 2026)

- [x] **Mojibake (karakter bozulması) düzeltildi** - Tüm PHP dosyalarında UTF-8 encoding sorunları giderildi
- [x] **MD5 şifre desteği kaldırıldı** - Güvenlik: Artık sadece `password_hash()` kullanılıyor
- [x] **CDN için SRI hash'leri eklendi** - Bootstrap, Bootstrap Icons, EasyMDE, Font Awesome
- [x] **Bootstrap Icons SRI hash'i düzeltildi** - Doğru integrity değeri: `sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+`
- [x] **Google Analytics CSP güncellendi** - `analytics.google.com` connect-src'ye eklendi
- [x] **Deprecated meta tag güncellendi** - `mobile-web-app-capable` eklendi
- [x] **Service Worker 404 hatası düzeltildi** - `sw.js` dosyası oluşturuldu (offline support & caching)
- [x] **Dinamik CSP implementasyonu** - Google Analytics kullanıldığında otomatik olarak domain'ler ekleniyor
- [x] **500 Internal Server Error düzeltildi** - config.php syntax hatası giderildi
- [x] **Anasayfa sayfalama iyileştirildi** - 10 yazı/sayfa, prev/next butonları, ellipsis, sayfa bilgisi, modern tasarım

> **Not:** CSP artık `config.php`'den dinamik olarak gönderiliyor. `GA_TRACKING_ID` tanımlıysa Google Analytics domain'leri otomatik eklenir.

---

## Güvenlik İyileştirmeleri (Security)

### Yüksek Öncelik
- [ ] Rate limiting implementasyonu ekle
  - Login girişimleri için mevcut
  - Admin panel tüm aksiyonları için genişlet
  - API endpoint'leri için rate limiting ekle

- [ ] Session güvenliği geliştir
  - Login sonrası `session_regenerate_id(true)` ekle
  - Session fixation korumasını güçlendir

- [ ] CSP (Content Security Policy) iyileştir
  - `unsafe-inline` kullanımını kaldır
  - Nonce veya hash-based CSP kullan
  - Inline scriptleri external dosyalara taşı

### Orta Öncelik
- [ ] Dosya upload güvenliği geliştir
  - Mime type kontrolü ekle (sadece extension değil)
  - Magic byte validation ekle
  - Upload edilmiş dosyaları webroot dışında sakla

- [ ] Path traversal koruması güçlendir
  - Double-encoding attack kontrolü ekle
  - Tüm file path parametrelerini validate et

## Performans İyileştirmeleri (Performance)

### Yüksek Öncelik
- [ ] Asset minification sistemi kur
  - CSS ve JS dosyalarını minify et
  - Build step ekle (Gulp, Webpack, vb.)
  - Production/Development ortam ayrımı yap

- [ ] Image optimization sistemi ekle
  - Otomatik image compression/resize
  - WebP formatı desteği
  - Responsive image generation (srcset)
  - Intervention Image veya GD kullan

- [ ] OPcache ayarlarını optimize et
  - `opcache.enable=1` kontrol et
  - `opcache.memory_consumption` ayarla
  - `opcache.max_accelerated_files` optimize et

### Orta Öncelik
- [ ] HTTP/2 Server Push kullan
  - Kritik CSS ve JS için server push
  - .htaccess'e Link header ekle

- [ ] Critical CSS implementasyonu
  - Above-the-fold CSS'i inline ekle
  - Geri kalan CSS'i async yükle
  - Critical CSS extraction tool kullan

- [ ] Service Worker tam implementasyonu
  - sw.js dosyasını oluştur
  - Offline-first stratejisi
  - Cache stratejileri (cache-first, network-first)
  - Background sync desteği

- [ ] Lazy loading geliştir
  - Native lazy loading attribute'ları ekle
  - Intersection Observer optimizasyonu
  - Progressive image loading

## SEO İyileştirmeleri

### Yüksek Öncelik
- [ ] Schema.org Structured Data geliştir
  - BreadcrumbList schema ekle
  - FAQPage schema ekle (eğer FAQ içerik varsa)
  - Author için sameAs property'si ekle (social media)
  - Organization schema ekle

- [ ] XML Sitemap iyileştir
  - lastmod gerçek dosya değişim zamanını kullan
  - Image sitemap oluştur
  - Video sitemap ekle (eğer video varsa)
  - Priority ve changefreq değerlerini optimize et

- [ ] Internal linking sistemi
  - İlgili yazılar (related posts) özelliği
  - Tag/category bazlı otomatik linking
  - Breadcrumb navigation geliştir

- [ ] Meta description optimizasyonu
  - Dinamik olarak önemli kelimeleri seç
  - İlk 160 karakter yerine akıllı kesme
  - Keyword density analizi

### Orta Öncelik
- [ ] Alt text validation geliştir
  - Admin panelinde warning göster
  - Yayınlanmadan önce zorunlu kıl
  - AI ile otomatik alt text öneri (opsiyonel)

- [ ] Robots.txt geliştir
  - Dynamic robots.txt
  - Sitemap URL'i otomatik ekle
  - Crawl delay ayarlarını değişken yap

- [ ] Pagination SEO
  - rel="prev" ve rel="next" ekle
  - Canonical URL'leri doğru ayarla
  - Load more/infinite scroll için SEO düzenlemesi

- [ ] hreflang tags hazırlığı
  - Çoklu dil desteği altyapısı
  - Dil bazlı URL yapısı

### Nice to Have
- [ ] AMP support
  - Mobile-first için AMP versiyonları
  - AMP validator entegrasyonu

- [ ] Rich snippets geliştir
  - Review snippets
  - Article snippets
  - HowTo snippets

## Genel Kod Kalitesi ve Altyapı

### Must Have
- [ ] Hata yönetimi sistemi
  - Try-catch blokları ekle
  - Custom exception handler
  - Graceful error handling

- [ ] Logging mekanizması geliştir
  - Structured logging
  - Log seviyeleri (DEBUG, INFO, WARNING, ERROR)
  - Log rotation

- [ ] Environment-based configuration
  - .env file desteği
  - Development/staging/production ortamları
  - Hassas bilgileri .env'de sakla

- [ ] Composer ile dependency management
  - Parsedown'ı composer ile yönet
  - Diğer kütüphaneleri ekle
  - Autoloading kullan

### Nice to Have
- [ ] Unit testler
  - PHPUnit kurulumu
  - Core fonksiyonlar için testler
  - Test coverage hedefi: %70+

- [ ] Monitoring ve Analytics
  - Error tracking (Sentry, Rollbar)
  - Performance monitoring
  - Uptime monitoring

- [ ] Backup ve restore sistemi geliştir
  - Otomatik scheduled backup
  - Cloud storage entegrasyonu (S3, Dropbox)
  - Incremental backup desteği

## Özellik Geliştirmeleri (Feature Development)

- [ ] Çoklu dil desteği (i18n)
  - Arayüz çevirisi sistemi
  - İçerik lokalizasyonu
  - URL yapısı (tr/, en/)

- [ ] Yorum sistemi veya geri bildirim formu
  - Spam korumalı
  - Email notification
  - Moderasyon paneli

- [ ] Newsletter sistemi
  - Email listesi yönetimi
  - RSS-to-Email
  - GDPR uyumlu

- [ ] API endpoint'leri
  - REST API
  - JSON response
  - API authentication

## SEO Iyilestirmeleri

- [x] Robots meta etiketini sayfa tipine gore dinamik yap.
  - Icerik sayfalari: `index, follow`
  - Arama sayfasi: `noindex, follow`
  - 404 sayfasi: `noindex, follow`

- [x] Canonical URL olusturmayi normalize et.
  - Query parametreli URL'lerde gereksiz canonical varyasyonlarini engelle.
  - `search` icin canonical stratejisini netlestir (`/search` veya `noindex` yaklasimi).

- [x] Public sayfalarda tek ve anlamsal bir `h1` hiyerarsisi sagla.
  - Ana sayfada `h1` kullan.
  - Arama sayfasinda `h1` kullan.
  - Sayfa basliklarinda `h1 -> h2 -> ...` akisini koru.

- [x] Layout semantiÄŸini guclendir.
  - Ana icerik kapsayicisini `main` ile tanimla.
  - Gereken yerlerde `section` ve `article` kullanimini arttir.

- [x] JSON-LD tipini sayfa turune gore ayarla.
  - Sadece post detayinda `BlogPosting` kullan.
  - Liste/arama gibi sayfalarda uygun schema tipine gec.

- [x] Gorsel alt text kontrolu ekle.
  - Markdown gorsellerinde bos/eksik `alt` metinlerini engelleyecek kontrol/uyari ekle.

## Core Web Vitals ve Lighthouse Iyilestirmeleri

- [x] Markdown CSS yuklemesini sayfa bazli yap.
  - `github-markdown-css` sadece post detay sayfalarinda yÃ¼klensin.
  - Liste, arama ve diger sayfalarda gereksiz render-blocking CSS'i kaldir.

- [x] 3rd-party baglanti hazirliklarini kosullu hale getir.
  - `googletagmanager` icin `preconnect/dns-prefetch` sadece `GA_TRACKING_ID` doluysa eklensin.

- [x] Footer JavaScript yukunu azalt ve modulerlestir.
  - Gereksiz `console.log` satirlarini temizle.
  - Tek buyuk inline script yerine kucuk ve `defer` script dosyalarina ayir.

- [x] Markdown gorselleri icin lazy/priority stratejisi uygula.
  - Ilk (LCP adayi) gorselde `loading="eager"` ve `fetchpriority="high"` kullan.
  - Diger gorsellerde `loading="lazy"` ve `fetchpriority="low"` kullan.
  - Tum gorsellerde `decoding="async"` uygula.

- [x] CLS riskini azaltmak icin gorsel alanini stabilize et.
  - Mumkun oldugunda `width/height` attribute uret.
  - En azindan CSS ile `max-width: 100%` ve `height: auto` davranisini garanti et.

- [x] Preload kullanimini optimize et.
  - Sadece kritik kaynaklari preload et.
  - Kullanilmayan veya etkisiz preload kayitlarini kaldir.

## Urun Gelistirme (Kisisel ve Hafif Blog Odakli)

### Must Have

- [x] Draft/Publish akisi ekle.
  - Yazilar taslak olarak kaydedilebilsin.
  - Yalnizca yayinlanan yazilar public tarafta gorunsun.

- [x] Otomatik slug uretimi ve cakisma kontrolu ekle.
  - Basliktan slug uret.
  - Ayni slug varsa benzersizlestir (`-2`, `-3` vb.).

- [x] Basit backup/restore araci ekle.
  - `posts/` ve kritik config dosyalari tek arsivde yedeklensin.
  - Ihtiyac halinde hizli geri yukleme akisi olsun.

- [x] Yayin oncesi SEO kontrol listesi ekle.
  - Eksik `title`, `description`, `h1`, `alt` icin admin tarafta uyari ver.

### Nice to Have

- [ ] Gorsel yukleme ve optimizasyon katmani ekle.
  - Boyut limiti, format donusumu (`webp`) ve temel sikistirma uygula.
  - `alt` metni zorunlu/onerili alan olarak destekle.

- [x] Arama deneyimini iyilestir.
  - Baslik ve etiket eslesmelerine daha yuksek agirlik ver.
  - Basit typo toleransi (hafif fuzzy) ekle.

- [x] Tema (dark/light) kodunu sadeleÅŸtir.
  - Mevcut scripti modulerlestir ve gereksiz isleri kaldir.
  - INP acisindan daha az ana thread maliyeti hedefle.

### Later

- [ ] Hafif ve gizlilik odakli analytics entegrasyonu ekle (opsiyonel).
  - Basit sayfa goruntuleme ve populer yazi metrikleri yeterli olsun.

- [ ] Yorum sistemi yerine geri bildirim baglantisi/formu ekle.
  - Spam maliyeti dusuk, bakimi kolay bir geri bildirim akisi sun.

- [x] CLI ile hizli yazi olusturma komutu ekle.
  - Ornek: `new-post "Baslik"` ile tarihli markdown taslagi uret.

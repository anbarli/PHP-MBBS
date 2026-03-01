# TODO

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

- [ ] RSS ozellestirmeleri ekle.
  - Ozet/tam icerik secimi.
  - Kategori veya etikete gore feed uretimi.

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





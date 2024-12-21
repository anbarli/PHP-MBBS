# PHP Markdown Based Blog System

Basit PHP Markdown Blog Sistemi; PHP ve Markdown kullanarak basit bir blog sistemi oluşturmanızı sağlar. SEO dostu URL'ler ve RSS beslemesi ile modern bir yapı sunar.

Özellikler
----------

*   **Markdown Tabanlı İçerik:** Blog yazıları Markdown formatında oluşturulur ve PHP ile HTML'ye dönüştürülür.
*   **SEO Dostu URL'ler:** .htaccess kullanılarak okunabilir URL yapısı sağlanır.
*   **RSS Desteği:** Blogunuz RSS beslemesiyle takip edilebilir hale gelir.
*   **Kolay Kurulum:** Minimal dosya yapısı ve kolay yapılandırma.

Gereksinimler
-------------

*   PHP 7.4 veya üstü
*   Apache Web Sunucusu (mod\_rewrite etkin olmalı)
*   Bir web tarayıcısı

Kurulum
-------

1.  Projeyi klonlayın:
    
        git clone https://github.com/kullanici-adiniz/php-markdown-blog.git
    
2.  `config.php` dosyasını düzenleyerek site bilgilerinizi girin.
3.  `.htaccess` dosyasını kontrol edin ve URL yeniden yazımının etkin olduğundan emin olun.
4.  Blog yazılarınızı `posts/` klasörüne Markdown formatında ekleyin.
5.  Tarayıcınızda projeyi çalıştırın: `http://localhost/blog/`

Kullanım
--------

### Blog Yazısı Eklemek

`posts/` klasöründe yeni bir `.md` dosyası oluşturun ve içerik ekleyin.

### RSS Beslemesine Erişmek

RSS beslemesine şu URL üzerinden erişebilirsiniz:

    http://yourdomain.com/blog/rss

Proje Yapısı
------------

    
    /blog/
        /posts/         - Blog yazılarının tutulduğu klasör
        /includes/      - Header, footer ve Markdown işleme dosyaları
        index.php       - Ana sayfa
        post.php        - Tekil yazı sayfası
        rss.php         - RSS beslemesi oluşturma
        config.php      - Site yapılandırması
        .htaccess       - URL yeniden yazma kuralları
        

Lisans
------

Bu proje MIT lisansı ile lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına göz atın.
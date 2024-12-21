---
title: PHP Markdown Based Blog System (Basit PHP Markdown Blog Sistemi)
category: Coding
tags: [PHP, Markdown, Blog]
date: 20.12.2024
---

# PHP-MBBS / PHP Markdown Based Blog System (Basit PHP Markdown Blog Sistemi) [🇹🇷]

Bu proje, PHP ve Markdown kullanarak basit bir blog sistemi oluşturmanızı sağlar. SEO dostu URL'ler ve RSS beslemesi ile modern bir yapı sunar.

## Özellikler

- **Markdown Tabanlı İçerik:** Blog yazıları Markdown formatında oluşturulur ve PHP ile HTML'ye dönüştürülür.
- **SEO Dostu URL'ler:** .htaccess kullanılarak okunabilir URL yapısı sağlanır.
- **RSS Desteği:** Blogunuz RSS beslemesiyle takip edilebilir hale gelir.
- **Kolay Kurulum:** Minimal dosya yapısı ve kolay yapılandırma.

## Gereksinimler

- PHP 7.4 veya üstü
- Apache Web Sunucusu (mod_rewrite etkin olmalı)
- Bir web tarayıcısı

## Kurulum

1.  Projeyi klonlayın:

        git clone https://github.com/anbarli/PHP-MBBS.git

2.  `config.php` dosyasını düzenleyerek site bilgilerinizi girin.
3.  `.htaccess` dosyasını kontrol edin ve URL yeniden yazımının etkin olduğundan emin olun.
4.  Blog yazılarınızı `posts/` klasörüne Markdown formatında ekleyin.
5.  Tarayıcınızda projeyi çalıştırın: `http://localhost/blog/`

## Kullanım

### Blog Yazısı Eklemek

`posts/` klasöründe yeni bir `.md` dosyası oluşturun ve içerik ekleyin.

### RSS Beslemesine Erişmek

RSS beslemesine şu URL üzerinden erişebilirsiniz:

    http://yourdomain.com/blog/rss

## Proje Yapısı

    /blog/
        /posts/         - Blog yazılarının tutulduğu klasör
        /includes/      - Header, footer ve Markdown işleme dosyaları
        index.php       - Ana sayfa
        post.php        - Tekil yazı sayfası
        rss.php         - RSS beslemesi oluşturma
        config.php      - Site yapılandırması
        .htaccess       - URL yeniden yazma kuralları


## Bağımlılıklar

Bu proje [Bootstrap](https://github.com/sindresorhus/github-markdown-css) kütüphanesini kullanır.
Bu proje Markdown ayrıştırması için [Parsedown](https://github.com/erusev/parsedown) kütüphanesini kullanır.
Bu proje Markdown stilleri için [github-markdown-css](https://github.com/sindresorhus/github-markdown-css) kütüphanesini kullanır.

## Lisans

Bu proje MIT lisansı ile lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına göz atın.

---------------------------

# PHP-MBBS / PHP Markdown Based Blog System [🇬🇧]

This project allows you to create a simple blog system using PHP and Markdown. It offers a modern structure with SEO-friendly URLs and RSS feed.

## Features

- Markdown Based Content:\*\* Blog posts are created in Markdown format and converted to HTML with PHP.
- SEO Friendly URLs:\*\* Readable URL structure is provided by using .htaccess.
- **RSS Support:** Your blog can be followed with RSS feed.
  **Easy Installation:** Minimal file structure and easy configuration.

## Requirements

- PHP 7.4 or higher
- Apache Web Server (mod_rewrite must be enabled)
- A web browser

## Installation

1.  Clone the project:

        git clone https://github.com/anbarli/PHP-MBBS.git

2.  Edit the `config.php` file and enter your site information.
3.  Check the `.htaccess` file and make sure URL rewriting is enabled.
4.  Add your blog posts in Markdown format to the `posts/` folder.
5.  Run the project in your browser: `http://localhost/blog/`

## Usage

#### Adding a Blog Post

Create a new `.md` file in the `posts/` folder and add content.

### Accessing the RSS Feed

You can access the RSS feed via the following URL:

    http://yourdomain.com/blog/rss

Translated with DeepL.com (free version)

## Project Structure

    /blog/
        /posts/ - Folder where blog posts are kept
        /includes/ - Header, footer and Markdown processing files
        index.php - Home page
        post.php - Single post page
        rss.php - Create an RSS feed
        config.php - Site configuration
        .htaccess - URL rewriting rules


## Dependencies

This project uses the [Bootstrap](https://github.com/sindresorhus/github-markdown-css) library.
This project uses the [Parsedown](https://github.com/erusev/parsedown) library for Markdown parsing.
This project uses the [github-markdown-css](https://github.com/sindresorhus/github-markdown-css) library for Markdown styling.

## License

This project is licensed under the MIT license. See the `LICENSE` file for more information.

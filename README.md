# PHP-MBBS / PHP Markdown Based Blog System

[🇹🇷 Türkçe](#türkçe) | [🇬🇧 English](#english)

---

## 🇹🇷 Türkçe

### 📝 Açıklama
PHP ve Markdown kullanarak basit ve modern bir blog sistemi. SEO dostu URL'ler, RSS beslemesi, admin paneli ve güvenlik önlemleri ile tam donanımlı.

### ✨ Temel Özellikler
- **Markdown Desteği:** Blog yazıları Markdown formatında
- **SEO Optimizasyonu:** Dostu URL'ler, meta etiketler, sitemap
- **Admin Paneli:** Yazı yönetimi, kategori yönetimi, ayarlar
- **Arama Sistemi:** Gelişmiş arama özelliği
- **Dark Mode:** Karanlık/aydınlık tema desteği
- **RSS Beslemesi:** Otomatik RSS oluşturma
- **Güvenlik:** XSS koruması, session yönetimi, şifre hash'leme
- **Performans:** Önbellekleme sistemi, gzip sıkıştırma

### 🔧 Gereksinimler
- PHP 7.4+
- Apache (mod_rewrite etkin)
- Web tarayıcısı

### 📦 Kurulum

1. **Projeyi klonlayın:**
   ```bash
   git clone https://github.com/anbarli/PHP-MBBS.git
   cd PHP-MBBS
   ```

2. **Ayarları yapılandırın:**
   ```bash
   cp config.local.example.php config.local.php
   cp admin/admin.env.example admin/admin.env
   ```

3. **Dosyaları düzenleyin:**
   - `config.local.php`: Site ayarları
   - `admin/admin.env`: Admin bilgileri

4. **Blog yazılarını ekleyin:**
   - `posts/` klasörüne `.md` dosyaları ekleyin

5. **Tarayıcıda açın:**
   - Ana sayfa: `http://localhost/blog/`
   - Admin paneli: `http://localhost/blog/admin/`

### 📝 Blog Yazısı Formatı
```markdown
---
title: Yazı Başlığı
category: Kategori
tags: [etiket1, etiket2]
date: 2024-01-15
---

# İçerik
Markdown formatında yazınız...
```

### 🏗️ Proje Yapısı
```
/blog/
├── posts/          # Blog yazıları
├── admin/          # Admin paneli
├── includes/       # Sistem dosyaları
├── cache/          # Önbellek (otomatik)
├── index.php       # Ana sayfa
├── post.php        # Yazı sayfası
├── search.php      # Arama
├── rss.php         # RSS beslemesi
└── config.php      # Yapılandırma
```

### 🔒 Güvenlik
- XSS koruması
- Path traversal koruması
- Session yönetimi
- Şifre hash'leme
- Admin kimlik doğrulama

### 📊 SEO Özellikleri
- Meta etiketler
- Open Graph
- Twitter Cards
- Structured Data
- XML Sitemap
- Robots.txt

---

## 🇬🇧 English

### 📝 Description
A simple and modern blog system using PHP and Markdown. Fully equipped with SEO-friendly URLs, RSS feed, admin panel, and security measures.

### ✨ Key Features
- **Markdown Support:** Blog posts in Markdown format
- **SEO Optimization:** Friendly URLs, meta tags, sitemap
- **Admin Panel:** Post management, category management, settings
- **Search System:** Advanced search functionality
- **Dark Mode:** Dark/light theme support
- **RSS Feed:** Automatic RSS generation
- **Security:** XSS protection, session management, password hashing
- **Performance:** Caching system, gzip compression

### 🔧 Requirements
- PHP 7.4+
- Apache (mod_rewrite enabled)
- Web browser

### 📦 Installation

1. **Clone the project:**
   ```bash
   git clone https://github.com/anbarli/PHP-MBBS.git
   cd PHP-MBBS
   ```

2. **Configure settings:**
   ```bash
   cp config.local.example.php config.local.php
   cp admin/admin.env.example admin/admin.env
   ```

3. **Edit files:**
   - `config.local.php`: Site settings
   - `admin/admin.env`: Admin credentials

4. **Add blog posts:**
   - Add `.md` files to `posts/` folder

5. **Open in browser:**
   - Homepage: `http://localhost/blog/`
   - Admin panel: `http://localhost/blog/admin/`

### 📝 Blog Post Format
```markdown
---
title: Post Title
category: Category
tags: [tag1, tag2]
date: 2024-01-15
---

# Content
Write your content in Markdown format...
```

### 🏗️ Project Structure
```
/blog/
├── posts/          # Blog posts
├── admin/          # Admin panel
├── includes/       # System files
├── cache/          # Cache (auto-generated)
├── index.php       # Homepage
├── post.php        # Post page
├── search.php      # Search
├── rss.php         # RSS feed
└── config.php      # Configuration
```

### 🔒 Security
- XSS protection
- Path traversal protection
- Session management
- Password hashing
- Admin authentication

### 📊 SEO Features
- Meta tags
- Open Graph
- Twitter Cards
- Structured Data
- XML Sitemap
- Robots.txt

---

## 📄 License
MIT License - see [LICENSE](LICENSE) file for details.

## 🤝 Contributing
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Open Pull Request

## 🆘 Support
For issues, use [GitHub Issues](https://github.com/anbarli/PHP-MBBS/issues).

---

## 28.20.2026 / Update Notes

### Turkish (TR)
- `admin/admin.env` icindeki `ADMIN_PASSWORD` alani artik `password_hash()` cikti formatini kullanir (`$2y$...` / bcrypt-argon2).
- Frontend/admin CSS ve JS dosyalari `assetPath()` ile versiyonlanir (`?v=filemtime`) ve cache kaynakli eski dosya sorunu azalir.
- `.htaccess` CSP kurallari guncellendi:
  - `script-src`: `https://static.cloudflareinsights.com` eklendi.
  - `connect-src`: `https://cdn.jsdelivr.net` ve `https://static.cloudflareinsights.com` eklendi.

### English (EN)
- `ADMIN_PASSWORD` in `admin/admin.env` now expects `password_hash()` output format (`$2y$...` / bcrypt-argon2).
- Frontend/admin CSS and JS assets are versioned via `assetPath()` (`?v=filemtime`) to reduce stale-cache issues.
- `.htaccess` CSP has been updated:
  - `script-src`: added `https://static.cloudflareinsights.com`
  - `connect-src`: added `https://cdn.jsdelivr.net` and `https://static.cloudflareinsights.com`

## CLI Quick Post

Create a draft post file from terminal:

```bash
./new-post "Post Title"
```

Windows:

```powershell
new-post.cmd "Post Title"
```

Options:

```bash
./new-post "Post Title" --category=Genel --tags=php,blog --status=draft --description="Kisa ozet" --date=2026-03-01
```

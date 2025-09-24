
<img width="1166" height="1015" alt="111" src="https://github.com/user-attachments/assets/605fc405-a441-440f-91d9-87f2220b1163" />

This is my e-commerce website, built to showcase my experience and capabilities.<br>
Bu e-ticaret web sitesini, deneyim ve yetkinliklerimi sergilemek için geliştirdim.<br>
<br>
For a quick design overview, see the live demo below. ![Demo: Limited](https://img.shields.io/badge/Demo-Limited-red)<br>
Hızlı bir tasarım özeti için aşağıdaki canlı demoya bakabilirsiniz. ![Demo: Sınırlı](https://img.shields.io/badge/Demo-S%C4%B1n%C4%B1rl%C4%B1-red)<br>
<br>
> **⚠️ Note:** This demo doesn’t represent the final result — <mark>the free host disables MySQL stored procedures</mark>, so some features are limited.<br>
> **⚠️ Not:** Bu demo nihai sonucu tam yansıtmaz — <mark>ücretsiz sağlayıcı MySQL saklı yordamlarını desteklemiyor</mark>, bu yüzden bazı özellikler kısıtlıdır.<br>
<br>
Project document (Turkish):<br>
Proje dokümanı (Türkçe):<br>
[myecommercedocument.pdf](https://github.com/user-attachments/files/22286144/myecommercedocument.pdf)

## Local Setup (XAMPP) / Yerelde Çalıştırma (XAMPP)

**1) 📥 Install XAMPP (PHP 8.x)**<br>
**1) 📥 XAMPP’i (PHP 8.x) Kurun**<br>
<br>
**2) 🚀 Start Apache & MySQL**<br>
**2) 🚀 Apache & MySQL’i Başlatın**<br>
XAMPP Control Panel → **Start** for **Apache** and **MySQL**<br>
XAMPP Control Panel → **Apache** ve **MySQL** için **Start**<br>

<br>

**3) 📂 Copy Project Into `htdocs`**<br>
**3) 📂 Projeyi `htdocs` İçine Kopyalayın**<br>
`C:\xampp\htdocs\YourProject\`<br>
`C:\xampp\htdocs\YourProject\`<br>

<br>

**4) 🗄️ Create DB & Import `.sql` (MySQL)**<br>
**4) 🗄️ Veritabanı Oluşturun ve `.sql` Dosyasını İçe Aktarın (MySQL)**<br>
phpMyAdmin → **Databases** → create → **Import** `.sql`<br>
phpMyAdmin → **Databases** → oluştur → **Import** `.sql`<br>

<br>

**5) 🔧 Edit `db.php` (Host / DB / User / Pass / Port)**<br>
**5) 🔧 `db.php`’yi Düzenleyin (Host / DB / Kullanıcı / Şifre / Port)**<br>
Yerel MySQL bilgilerinize göre ayarlayın (XAMPP varsayılan: user `root`, pass boş).<br>

<br>

**6) 🌐 Run The App**<br>
**6) 🌐 Uygulamayı Çalıştırın**<br>
`http://localhost/YourProject/`<br>
`http://localhost/YourProject/`<br>

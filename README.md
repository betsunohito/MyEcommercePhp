
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
📄 Project document (PDF, Turkish) / Proje dokümanı (PDF — Türkçe):<br>
[![PDF — Project Document (TR)](https://img.shields.io/badge/Project%20Document%20(TR)-PDF-red?logo=adobeacrobat)](https://github.com/user-attachments/files/22286144/myecommercedocument.pdf)<br>

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
`C:\xampp\htdocs\`<br>

<br>

**4) 🗄️ Create DB & Import `.sql` (MySQL)**<br>
**4) 🗄️ Veritabanı Oluşturun ve `.sql` Dosyasını İçe Aktarın (MySQL)**<br>
phpMyAdmin → **Databases** → create → **Import** `.sql`<br>
phpMyAdmin → **Databases** → oluştur → **Import** `.sql`<br>

<br>

🔧 **Step 5 — Configure both `db.php` files**<br>
🔧 **Adım 5 — Her iki `db.php` dosyasını da ayarlayın**<br>
Paths / Yollar:<br>
• `C:\xampp\htdocs\Mysqlecommerce\db.php`<br>
• `C:\xampp\htdocs\Mysqlecommerce\tools\action\db.php`  ← (örnek ikinci konum)<br>
<br>
Set host, db name, user, pass, port for your local MySQL in **both** files.<br>
Yerel MySQL için host, veritabanı adı, kullanıcı, şifre ve port’u **iki dosyada da** ayarlayın.<br>
(Örn.)<br>
```php
<?php
$host = '127.0.0.1';
$db   = 'mysqlecommerce';
$user = 'root';
$pass = ''; // XAMPP'de genelde boş
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo  = new PDO($dsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);
?>
```
<br>

**6) 🌐 Run The App**<br>
**6) 🌐 Uygulamayı Çalıştırın**<br>
`http://localhost/MysqlPhpProject`<br>
`http://localhost/mysqlecommerce`<br>

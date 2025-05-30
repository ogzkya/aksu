# 📁 FILEZILLA İLE SUNUCUYA DOSYA YÜKLEME REHBERİ

## 🔧 1. FILEZILLA KURULUMU

### FileZilla İndirme:
- **İndir:** https://filezilla-project.org/download.php?type=client
- **Windows için:** FileZilla Client (ücretsiz)
- **Kurulum:** Standart kurulum yapın

## 🌐 2. SUNUCU BAĞLANTI BİLGİLERİ

### atakentaksuemlak.com için FTP bilgileri:
```
Sunucu Adresi: atakentaksuemlak.com VEYA ftp.atakentaksuemlak.com
Kullanıcı Adı: [hosting sağlayıcıdan alacağınız FTP kullanıcı adı]
Şifre: [hosting sağlayıcıdan alacağınız FTP şifresi]
Port: 21 (FTP) VEYA 22 (SFTP güvenli)
Protokol: FTP VEYA SFTP (güvenli)
```

**ÖNEMLİ:** Bu bilgileri hosting sağlayıcınızdan (GoDaddy, Natro, vb.) almalısınız!

## 📊 3. FILEZILLA BAĞLANTI KURMA

### Adım 1: FileZilla'yı Açın
1. FileZilla Client'ı başlatın
2. Üst menüde "Site Manager" butonuna tıklayın

### Adım 2: Yeni Site Ekleyin
1. "New Site" butonuna tıklayın
2. Site adını "Atakent Aksu Emlak" olarak ayarlayın

### Adım 3: Bağlantı Ayarları
```
Host: atakentaksuemlak.com
Protocol: SFTP (güvenli) veya FTP
Port: 22 (SFTP) veya 21 (FTP)
Logon Type: Normal
User: [FTP kullanıcı adınız]
Password: [FTP şifreniz]
```

### Adım 4: Bağlan
- "Connect" butonuna tıklayın
- İlk bağlantıda güvenlik sertifikası onayı isteyebilir, "OK" deyin

## 📂 4. DOSYA YÜKLEME İŞLEMİ

### Sol Panel (Yerel Bilgisayar):
```
C:\xampp\htdocs\aksu\
```

### Sağ Panel (Sunucu):
```
/public_html/ veya /httpdocs/ veya /www/
```

### Yükleme Adımları:

#### 1. Hedef Klasörü Bulun:
- Sağ panelde `public_html` veya `httpdocs` klasörüne gidin
- Bu klasör web sitenizin ana dizinidir

#### 2. Dosyaları Seçin:
Sol panelde `C:\xampp\htdocs\aksu\` klasöründen şu dosyaları seçin:

**✅ YÜKLENECEK DOSYALAR:**
```
✅ admin/ (tüm klasör)
✅ api/ (tüm klasör)
✅ assets/ (tüm klasör)
✅ config/ (tüm klasör)
✅ includes/ (tüm klasör)
✅ migrations/ (tüm klasör)
✅ templates/ (tüm klasör)
✅ uploads/ (tüm klasör)
✅ *.php dosyaları (index.php, contact.php, vb.)
✅ .htaccess-production (sonra .htaccess olarak yeniden adlandıracağız)
```

**❌ YÜKLENMEYECEK DOSYALAR:**
```
❌ .git/ klasörü
❌ test-*.php dosyaları
❌ debug-*.php dosyaları
❌ verify-*.php dosyaları
❌ .env dosyası
❌ readme dosyası
```

#### 3. Dosyaları Sürükleyip Bırakın:
- Sol panelden seçtiğiniz dosya/klasörleri sağ panele sürükleyin
- Veya sağ tıklayıp "Upload" seçin

## ⚙️ 5. YÜKLEME SONRASI AYARLAR

### 1. .htaccess Dosyasını Yeniden Adlandırın:
```
.htaccess-production → .htaccess
```

### 2. Klasör İzinlerini Ayarlayın:
- `uploads/` klasörü → Sağ tık → Permissions → 755
- `logs/` klasörü oluşturun → Permissions → 755

### 3. config.php Kontrolü:
- `config/config.php` dosyasında database bilgilerinin doğru olduğunu kontrol edin

## 🛠️ 6. HOSTING PANELİ AYARLARI

### Database Oluşturma:
1. Hosting paneline giriş yapın
2. MySQL Databases bölümüne gidin
3. Yeni database oluşturun: `atakent_aksu_db`
4. Database kullanıcısı oluşturun
5. Kullanıcıyı database'e atayın

### SQL Dosyalarını Çalıştırın:
```
migrations/001_create_agents_table.sql
migrations/002_add_agent_id_to_listings.sql
migrations/003_create_messages_table.sql
migrations/003_insert_test_agent.sql
```

## ✅ 7. TEST VE KONTROL

### Site Testi:
- https://atakentaksuemlak.com/ → Ana sayfa açılıyor mu?
- https://atakentaksuemlak.com/admin/ → Admin paneli çalışıyor mu?
- İletişim formu test edin
- İlan ekleme test edin

## 🆘 8. SORUN GİDERME

### Yaygın Hatalar:

**500 Internal Server Error:**
- .htaccess dosyasını kontrol edin
- PHP versiyonunu 7.4+ yapın

**Database Connection Error:**
- config.php dosyasındaki DB bilgilerini kontrol edin
- Database kullanıcı izinlerini kontrol edin

**Images Not Loading:**
- uploads/ klasörü izinlerini 755 yapın
- Resim yollarını kontrol edin

### FileZilla Bağlantı Sorunları:
- Firewall ayarlarını kontrol edin
- Passive mode'u deneyin (Site Manager → Transfer Settings)
- Port 21 yerine 22 (SFTP) deneyin

## 📞 DESTEK

Hosting sağlayıcınızdan şu bilgileri alın:
- FTP/SFTP sunucu adresi
- FTP kullanıcı adı ve şifre
- Database sunucu adresi
- Database adı, kullanıcı adı ve şifre

**BAŞARILAR! 🎉**

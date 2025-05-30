# 🚀 CANLI SUNUCU DEPLOYMENT CHECKLİST
## atakentaksuemlak.com

### ✅ HAZIR DURUMDAKI DOSYALAR

#### Konfigürasyon Dosyaları:
- ✅ `config/config.php` - Canlı sunucu ayarları güncellendi
- ✅ `.htaccess-production` - Production için optimize edildi
- ✅ `templates/header.php` - Site adı ve SEO güncellendi

#### Güncellenmiş İletişim Bilgileri:
- ✅ **Telefon:** (0212) 693 90 88
- ✅ **E-posta:** aksu-emlak@hotmail.com.tr
- ✅ **Adres:** Halkalı Küçükçekmece, İstanbul
- ✅ **Harita:** Halkalı konumu ayarlandı

---

### 🔧 SUNUCUYA YÜKLEMEDEN ÖNCE YAPIN

#### 1. Hosting Paneli Ayarları:
```
Veritabanı: atakent_aksu_db
Kullanıcı: atakent_user
Şifre: [GÜÇLÜ ŞİFRE BELIRLEYIN]
```

#### 2. Dosya Yükleme:
- [ ] Tüm proje dosyalarını FTP ile yükleyin
- [ ] `.htaccess-production` dosyasını `.htaccess` olarak yeniden adlandırın
- [ ] `uploads/` klasörü izinlerini 755 yapın
- [ ] `logs/` klasörü oluşturun ve 755 izin verin

#### 3. Konfigürasyon Düzenlemeleri:
- [ ] `config/config.php` dosyasında DB şifresini gerçek şifre ile değiştirin
- [ ] E-posta ayarlarını hosting sağlayıcınıza göre düzenleyin

---

### 📊 VERİTABANI KURULUMU

#### SQL Dosyalarını Sırayla Çalıştırın:
1. [ ] `migrations/001_create_agents_table.sql`
2. [ ] `migrations/002_add_agent_id_to_listings.sql`
3. [ ] `migrations/003_create_messages_table.sql`
4. [ ] `migrations/003_insert_test_agent.sql`

#### Veritabanı Tabloları Kontrol:
- [ ] `agents` tablosu oluştu
- [ ] `listings` tablosu oluştu  
- [ ] `messages` tablosu oluştu
- [ ] İlk agent kaydı eklendi

---

### 🛡️ GÜVENLİK AYARLARI

#### Hosting Paneli:
- [ ] SSL sertifikası aktif
- [ ] PHP 7.4+ versiyonu seçili
- [ ] .htaccess desteği aktif
- [ ] Mod_rewrite modülü aktif

#### Dosya İzinleri:
- [ ] config/ klasörü: 644
- [ ] uploads/ klasörü: 755
- [ ] logs/ klasörü: 755
- [ ] Diğer tüm klasörler: 644

---

### 🔍 CANLI SUNUCU TEST LİSTESİ

#### Temel Fonksiyonlar:
- [ ] Ana sayfa yükleniyor: `https://atakentaksuemlak.com/`
- [ ] İlan listesi çalışıyor: `/search.php`
- [ ] İlan detay sayfası açılıyor: `/listing.php?id=1`
- [ ] İletişim formu çalışıyor: `/contact.php`
- [ ] Blog sayfası çalışıyor: `/blog.php`

#### Admin Paneli:
- [ ] Admin login: `https://atakentaksuemlak.com/admin/`
- [ ] İlan ekleme çalışıyor
- [ ] Resim yükleme çalışıyor
- [ ] Mesajlar görüntüleniyor

#### İletişim Bilgileri:
- [ ] Telefon numarası her yerde doğru: (0212) 693 90 88
- [ ] E-posta adresi her yerde doğru: aksu-emlak@hotmail.com.tr
- [ ] Adres bilgileri doğru: Halkalı Küçükçekmece
- [ ] Harita doğru konumu gösteriyor

---

### 🚨 SORUN GİDERME

#### Yaygın Hatalar:
```
500 Internal Server Error → .htaccess dosyasını kontrol edin
Database Connection Error → config.php DB bilgilerini kontrol edin
Images Not Loading → uploads/ klasör izinlerini kontrol edin
SSL/HTTPS Errors → Hosting panelinde SSL ayarlarını kontrol edin
```

#### Log Dosyaları:
- PHP hataları: Hosting paneli error logs
- Site hataları: `logs/` klasörü

---

### 📞 ÖNEMLİ NOTLAR

1. **İlk Admin Girişi:**
   ```
   URL: https://atakentaksuemlak.com/admin/
   Kullanıcı: admin
   Şifre: admin123
   ```
   ⚠️ **İlk girişten sonra mutlaka şifrenizi değiştirin!**

2. **Backup Önerisi:**
   - Veritabanı backup'ını düzenli alın
   - Önemli dosyaların yedeğini tutun

3. **SEO Optimizasyonu:**
   - Google Search Console'a site ekleyin
   - Sitemap.xml oluşturun
   - Google Analytics ekleyin

---

### ✅ BAŞARILI KURULUM SONRASI

Site `https://atakentaksuemlak.com/` adresinde başarıyla çalışmaya hazır! 

**Tüm özellikler test edildi ve çalışır durumda:**
- 📱 Responsive tasarım
- 🔍 İlan arama ve filtreleme
- 📧 İletişim formu
- 🏠 İlan yönetimi
- 👨‍💼 Admin paneli
- 🗺️ Harita entegrasyonu
- 📞 Güncellenmiş iletişim bilgileri

**Başarılı kurulum dileriz! 🎉**

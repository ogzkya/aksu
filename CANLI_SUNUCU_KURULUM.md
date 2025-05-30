# 🌐 CANLI SUNUCU KURULUM REHBERİ
## Atakent Aksu Emlak Web Sitesi

### 📋 ÖN HAZIRLIK

#### 1. Hosting Gereksinimler
- ✅ PHP 7.4 veya üzeri
- ✅ MySQL 5.7 veya üzeri  
- ✅ SSL Sertifikası (HTTPS)
- ✅ En az 1GB disk alanı
- ✅ GD Extension (resim işleme için)

#### 2. Dosya Yükleme
1. **FTP ile tüm proje dosyalarını** hosting'in ana dizinine (public_html veya httpdocs) yükleyin
2. **uploads/ klasörü izinlerini 755** olarak ayarlayın
3. **config/ klasörü izinlerini 644** olarak ayarlayın

---

### 🗄️ VERİTABANI KURULUMU

#### 1. Hosting Panelinden Veritabanı Oluşturun
```
Veritabanı Adı: atakent_aksu_db
Kullanıcı Adı: atakent_user  
Şifre: [GÜVENLİ BİR ŞİFRE]
```

#### 2. SQL Dosyalarını İçe Aktarın
Aşağıdaki sırayla migrations/ klasöründeki dosyaları çalıştırın:
```sql
1. migrations/001_create_agents_table.sql
2. migrations/002_add_agent_id_to_listings.sql  
3. migrations/003_create_messages_table.sql
4. migrations/003_insert_test_agent.sql
```

#### 3. Veritabanı Bağlantı Ayarları
`config/config.php` dosyasında:
```php
'host' => 'localhost', // Hosting sağlayıcının DB host'u
'name' => 'atakent_aksu_db', 
'user' => 'atakent_user',
'pass' => 'GÜÇLÜ_ŞİFRENİZ'
```

---

### ⚙️ KONFIGURASYON DEĞİŞİKLİKLERİ

#### ✅ ZATEN YAPILDI:
- Site URL'i `https://atakentaksuemlak.com/` olarak ayarlandı
- Upload URL'leri güncellenendi
- HTTPS ayarları yapılandırıldı
- Session ayarları canlı sunucu için optimizasyon edildi

---

### 👤 ADMİN PANELİ KURULUMU

#### 1. İlk Admin Kullanıcısı Oluşturun
Site URL'nize gidin: `https://atakentaksuemlak.com/admin/`

İlk çalıştırmada otomatik olarak admin kullanıcısı oluşturulacak:
```
Kullanıcı: admin
Şifre: admin123
```

#### ⚠️ ÖNEMLİ GÜVENLİK
**İlk giriş yaptıktan sonra mutlaka şifrenizi değiştirin!**

---

### 📧 İLETİŞİM BİLGİLERİ KONTROLÜ

#### ✅ GÜNCELLENEN BİLGİLER:
- **Telefon:** (0212) 693 90 88
- **E-posta:** aksu-emlak@hotmail.com.tr  
- **Adres:** Halkalı Küçükçekmece, İstanbul
- **Harita:** Halkalı konumu ayarlandı

---

### 🔍 KONTROL LİSTESİ

#### Kurulum Sonrası Test Edin:
- [ ] Ana sayfa yükleniyor mu?
- [ ] İlan listeleme çalışıyor mu?
- [ ] İlan detay sayfası açılıyor mu?
- [ ] İletişim formu çalışıyor mu?
- [ ] Admin paneli erişilebiliyor mu?
- [ ] Resim yükleme fonksiyonu çalışıyor mu?
- [ ] Harita bölümü doğru konumu gösteriyor mu?

---

### 🚨 SORUN GİDERME

#### Yaygın Problemler:
1. **Resimler gösterilmiyor** → uploads/ klasör izinlerini kontrol edin
2. **Veritabanı bağlantı hatası** → config.php dosyasındaki DB bilgilerini kontrol edin  
3. **404 hataları** → .htaccess dosyası eksik olabilir
4. **SSL hataları** → Hosting panelinden SSL sertifikasını kontrol edin

#### Log Dosyaları:
- PHP hataları: hosting paneli error logs
- Site logları: `logs/` klasörü

---

### 📞 DESTEK
Kurulum ile ilgili sorunlar için hosting sağlayıcınızın teknik destek ekibine başvurun.

**Başarılı kurulum dileriz! 🎉**

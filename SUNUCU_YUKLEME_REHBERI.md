# 🎯 SUNUCUYA YÜKLEME SONRASI YAPILACAKLAR

## 1. VERİTABANI KURULUMU
Hosting panelinde MySQL veritabanı oluşturun ve şu SQL dosyalarını sırayla çalıştırın:

```sql
-- migrations/001_create_agents_table.sql
-- migrations/002_add_agent_id_to_listings.sql  
-- migrations/003_create_messages_table.sql
-- migrations/003_insert_test_agent.sql
```

## 2. KLASÖR İZİNLERİ
```bash
uploads/ → 755
logs/ → 755 (klasörü oluşturun)
config/ → 644
```

## 3. İLK ADMIN GİRİŞİ
```
URL: https://atakentaksuemlak.com/admin/
Kullanıcı: admin
Şifre: admin123
```
⚠️ **İlk girişte mutlaka şifrenizi değiştirin!**

## 4. TEST LİSTESİ
- [ ] Ana sayfa: https://atakentaksuemlak.com/
- [ ] İlan arama: /search.php
- [ ] İletişim formu: /contact.php
- [ ] Admin paneli: /admin/
- [ ] Resim yükleme testi

## 5. GÜVENLİK
- [ ] SSL sertifikası aktif mi?
- [ ] Admin şifresi değiştirildi mi?
- [ ] .htaccess dosyası yüklendi mi?

🎉 **BAŞARILAR!** Site atakentaksuemlak.com adresinde yayında olacak!

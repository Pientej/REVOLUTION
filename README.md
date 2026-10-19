<img width="1895" height="911" alt="image" src="https://github.com/user-attachments/assets/a8d99f35-2d1f-4536-a83c-63e874977dd2" />
Anasayfa




<img width="1917" height="913" alt="image" src="https://github.com/user-attachments/assets/a8f350b2-0fa9-4540-81e0-cd56be8f5893" />
Etkinlikler


<img width="1917" height="908" alt="image" src="https://github.com/user-attachments/assets/efe5a12c-a68a-4233-afe8-8a332c274878" />
Guruplar kısmı




<img width="1911" height="912" alt="image" src="https://github.com/user-attachments/assets/4087702a-5ad9-45f5-a333-b31af9fcad89" />
Profil


🔐 1. Kimlik Doğrulama (Giriş & Kayıt)
Login / Register: E-posta ve şifre ile güvenli giriş ve kayıt sistemi kuruldu.

Güvenlik: Şifreler şifrelendi (bcrypt), 5 hatalı denemede hesap kilitleniyor. CSRF ve SQL Injection korumaları eklendi.

Yönlendirme: Başarılı girişte kullanıcı direkt haritalı ana sayfaya (home.php) yönlendiriliyor.

🗺️ 2. Harita ve Konum (Leaflet.js)
Ücretsiz Altyapı: Google Maps'in kredi kartı zorunluluğundan kurtulmak için tamamen ücretsiz, sınırsız ve API anahtarı gerektirmeyen Leaflet + OpenStreetMap'e geçildi.

Kullanıcı Pini: Kullanıcının kendi konumu haritada Kırmızı, damla şeklinde özel bir ikonla gösterildi.

Ortala Butonu: Sağ alt köşeye, haritayı kullanıcının kendi konumuna odaklayan bir buton eklendi.

📅 3. Etkinlik Sistemi
Oluşturma: Harita üzerinden nokta seçerek etkinlik oluşturma. Formun boş gönderilmesini engelleyen tarayıcı uyarıları eklendi.

Zaman Aşımı: Süresi geçmiş etkinlikler otomatik olarak veritabanından silinip haritadan kaldırılıyor.

Katıl / Ayrıl: Kullanıcılar etkinliklere katılabiliyor veya ayrılabiliyor.

Gizlilik: Etkinlikler "Herkese Açık" veya "Sadece Grubum (Gizli)" olarak ikiye ayrıldı.

👥 4. Grup Sistemi
1 Kullanıcı = 1 Grup: Bir kişi sadece tek bir grup kurabilir (Hem buton gizlenerek hem de sunucu tarafında engellenerek).

Davet & Onay: Gruba katılmak için istek atılır, grup yöneticisi onaylarsa üye olunur.

Yönetici Paneli: Yönetici bekleyen istekleri onaylar, üyeleri atar (kendini atamaz) ve grubu silebilir.

Gizli Toplanma Noktası: Grup kurulurken haritadan bir toplanma noktası seçilir. Bu noktayı sadece o grubun üyeleri görebilir.

Sohbet & Medya: Grup üyelerinin kendi aralarında yazışabildiği, fotoğraf/video linki paylaşabildiği bir sohbet ekranı yapıldı.

👤 5. Profil Sayfası
Kullanıcının kendi bilgilerini (biyografi, e-posta, katılım tarihi) gördüğü şık bir kart tasarlandı.

Katıldığı etkinlikler ve üye olduğu gruplar listelendi.

"Profili Düzenle" özelliği ile bilgiler güncellenebiliyor.

🎨 6. Renk Kodlaması ve Arayüz
Haritadaki Pinler: Mavi (Kendi etkinliğin), Yeşil (Katıldığın), Mor (Gizli Grup), Sarı (Diğer herkese açık).

Sol Menü Listesi: Listedeki takvim ikonlarının renkleri de haritadaki pinlerle birebir eşitlendi.

Mobil Uyumluluk (Responsive): Masaüstündeki sol menü telefonda en alta (alt sekmeler) taşındı. Harita tam ekran yapıldı ve mobilde kaybolmaması için "+" (Etkinlik Oluştur) ve Konum butonları sağ alta sabitlendi.

Kaydırma Hatası Çözümü: Profil ve diğer uzun sayfaların aşağı kaymama sorunu CSS overflow ayarları ile giderildi.

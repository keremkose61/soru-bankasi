# BrainBoost — Soru Bankası ve Deneme Paylaşım Ağı

[![PHP](https://img.shields.io/badge/PHP-Backend-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![PWA](https://img.shields.io/badge/PWA-Supported-5A0FC8?logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![License](https://img.shields.io/badge/License-Not%20specified-lightgrey)](#lisans)

**BrainBoost**, öğrencilerin ve öğretmenlerin eğitim içerikleri etrafında bir araya gelmesini amaçlayan PHP ve MySQL tabanlı bir soru paylaşım ve çalışma platformudur. Kullanıcılar soru paylaşabilir, çözümler üzerinde etkileşim kurabilir ve çalışma süreçlerini Pomodoro araçlarıyla takip edebilir.

> Bu proje geliştirme/öğrenme amaçlıdır. Üretim ortamına alınmadan önce güvenlik, yapılandırma ve dağıtım ayarları gözden geçirilmelidir.

## İçindekiler

- [Özellikler](#özellikler)
- [Teknolojiler](#teknolojiler)
- [Proje yapısı](#proje-yapısı)
- [Kurulum](#kurulum)
- [Yapılandırma ve güvenlik](#yapılandırma-ve-güvenlik)
- [Demo hesapları](#demo-hesapları)
- [Ekran görüntüleri](#ekran-görüntüleri)
- [Bilinen notlar](#bilinen-notlar)
- [Lisans](#lisans)

## Özellikler

### Soru ve çözüm paylaşımı
- Soru oluşturma, görüntüleme ve cevaplama
- Başlık, açıklama ve etiketler üzerinden soru arama
- Soru ve cevapları beğenme; soruları favorilere kaydetme
- En iyi cevabı işaretleme ve cevaplara yorum yazma

### Kullanıcı ve iletişim
- Öğrenci, öğretmen ve yönetici rolleri
- Kullanıcı profilleri ve profil düzenleme
- Kullanıcılar arasında doğrudan mesajlaşma
- Bildirimler, rozetler ve liderlik tablosu
- Şikâyet ve içerik denetleme yönetimi

### Çalışma araçları
- TYT/AYT için geri sayım alanı
- Pomodoro odaklanma zamanlayıcısı ve çalışma istatistikleri
- Soru ve favorilerden yazdırılabilir deneme kitapçığı ve cevap anahtarı oluşturma

### Yapay zekâ ve PWA
- Yapılandırıldığında OpenAI veya Google Gemini destekli içerik denetleme
- API anahtarı bulunmadığında anahtar kelime tabanlı yedek denetleme yaklaşımı
- PWA manifesti, service worker ve çevrimdışı sayfa desteği

## Teknolojiler

- **Backend:** PHP
- **Veritabanı:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Grafikler:** Chart.js (odaklanma istatistikleri)
- **Web uygulaması:** Progressive Web App (PWA)
- **Yerel geliştirme:** Laragon, XAMPP veya benzeri Apache/PHP/MySQL ortamı

## Proje yapısı

```text
soru-bankasi/
├── config.php              # Veritabanı ve uygulama yapılandırması
├── index.php               # Uygulama giriş noktası ve yönlendirme
├── init.sql                # İlk veritabanı şeması ve başlangıç verileri
├── controllers/            # İstek ve uygulama akışı
├── models/                 # Veri erişimi ve iş mantığı
├── core/                   # Ortak servisler (dil, AI ve moderasyon)
├── views/                  # Arayüz şablonları
├── public/                 # CSS, görseller ve herkese açık dosyalar
├── manifest.json           # PWA manifesti
├── sw.js                   # Service worker
└── offline.html            # Çevrimdışı bilgilendirme sayfası
```

## Kurulum

Aşağıdaki adımlar Laragon üzerinden yerel kurulum içindir. XAMPP/WAMP kullanıyorsanız proje klasörünü ilgili web kök dizinine taşıyabilirsiniz.

1. **Depoyu klonlayın**
   ```bash
   git clone https://github.com/keremkose61/soru-bankasi.git
   ```

2. **Web sunucusuna yerleştirin**

   Laragon kullanıyorsanız klasörü `C:\\laragon\\www\\soru-bankasi` konumuna yerleştirin. Apache ve MySQL servislerini başlatın.

3. **Veritabanını oluşturun**

   phpMyAdmin veya HeidiSQL üzerinden `soru_bankasi` adında bir MySQL veritabanı oluşturun. Ardından depodaki `init.sql` dosyasını bu veritabanına içe aktarın.

4. **Veritabanı bağlantısını kontrol edin**

   `config.php` dosyasındaki `DB_HOST`, `DB_USER`, `DB_PASS` ve `DB_NAME` değerlerini kendi yerel ortamınıza göre düzenleyin. Varsayılan yerel ayarlar her bilgisayarda aynı olmayabilir.

5. **Uygulamayı açın**

   Tarayıcıdan şu adresi ziyaret edin:
   ```text
   http://localhost/soru-bankasi
   ```

## Yapılandırma ve güvenlik

- Yapay zekâ servis anahtarlarını kaynak koda yazmayın. Projenin desteklediği yerel `secrets.php` dosyasını kullanın ve bu dosyayı Git'e eklemeyin.
- `.gitignore` dosyası `secrets.php`, `.env`, bağımlılık klasörleri ve yüklenen kullanıcı dosyalarını dışarıda tutacak şekilde yapılandırılmıştır.
- `config.php` içindeki hata gösterimi ayarlarını canlı sunucuda kapatın.
- Kurulumla gelen örnek kullanıcı parolalarını canlıya geçmeden önce değiştirin veya örnek hesapları kaldırın.
- `/migrate` gibi bakım/yükseltme uç noktalarını canlı ortamda koruyun veya devre dışı bırakın.
- Canlı kullanım öncesinde HTTPS, dosya yükleme izinleri, veritabanı yetkileri, oturum güvenliği ve erişim kontrollerini ayrıca denetleyin.

## Demo hesapları

Depodaki `BENIOKU.txt` dosyasında yerel test için örnek yönetici, öğretmen ve öğrenci hesapları listelenmiştir. Bunlar yalnızca yerel geliştirme ortamında kullanılmalıdır. Herkese açık veya canlı bir sunucuya taşımadan önce varsayılan parolaları değiştirin.

## Ekran görüntüleri

Henüz ekran görüntüsü eklenmedi. Uygulamanın ana sayfası, soru detay ekranı, Pomodoro alanı ve yönetim panelinden görüntüler eklendiğinde bu bölümü güncelleyin.

## Bilinen notlar

- Projede Composer veya npm bağımlılık manifesti bulunmadığı için kurulum adımları şu an PHP/MySQL yerel sunucu kurulumu üzerinden anlatılmıştır.
- Harici AI servisleri için geçerli API anahtarı ve ilgili servis ayarları gerekir.
- Özelliklerin ve güvenlik önlemlerinin canlıya alınmadan önce gerçek bir kurulumda test edilmesi gerekir.

## Lisans

Bu depoda henüz bir lisans dosyası bulunmuyor. Lisans belirtilene kadar kodun yeniden kullanımı ve dağıtımı için açık izin verildiği varsayılmamalıdır.

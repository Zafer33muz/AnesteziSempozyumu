-- Bildiri özetleri tablosu
CREATE TABLE IF NOT EXISTS bildiriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    bildiri_turu VARCHAR(50) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    anahtar_kelimeler VARCHAR(255) NOT NULL,
    ozet TEXT NOT NULL,
    dosya_adi VARCHAR(255),
    dosya_yolu VARCHAR(255),
    gonderim_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    durum VARCHAR(50) DEFAULT 'Değerlendirmede',
    kullanici_id INT,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
);

-- Yazarlar tablosu (birden fazla yazar olabilir)
CREATE TABLE IF NOT EXISTS bildiri_yazarlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bildiri_id INT NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    kurum VARCHAR(150) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    sorumlu_yazar BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (bildiri_id) REFERENCES bildiriler(id) ON DELETE CASCADE
);

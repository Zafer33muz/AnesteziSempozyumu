-- Veritabanını oluştur (eğer yoksa)
CREATE DATABASE IF NOT EXISTS kullanicilar;

-- Veritabanını seç
USE kullanicilar;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefon VARCHAR(20) NOT NULL,
    kurum VARCHAR(100) NOT NULL,
    unvan VARCHAR(50) NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    son_giris TIMESTAMP NULL
);

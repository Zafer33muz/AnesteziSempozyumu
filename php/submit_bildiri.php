<?php
// Oturum başlat
session_start();

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Bu işlemi gerçekleştirmek için giriş yapmalısınız.',
        'redirect' => '#login'
    ]);
    exit;
}

// Veritabanı bağlantısını dahil et
require_once 'config.php';

// Bildiriler tablosunun varlığını kontrol et
$table_check = $conn->query("SHOW TABLES LIKE 'bildiriler'");
if ($table_check->num_rows == 0) {
    // Tablo yoksa bildiri_table.sql dosyasını çalıştır
    $sql_file = dirname(__FILE__) . '/bildiri_table.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        if ($conn->multi_query($sql)) {
            do {
                // Her sorgu sonucunu tüket
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Bildiriler tablosu oluşturulamadı: ' . $conn->error
            ]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Bildiriler tablosu için SQL dosyası bulunamadı.'
        ]);
        exit;
    }
}

// POST isteği kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $baslik = isset($_POST['baslik']) ? $_POST['baslik'] : '';
    $ozet = isset($_POST['ozet']) ? $_POST['ozet'] : '';
    $anahtar_kelimeler = isset($_POST['anahtar_kelimeler']) ? $_POST['anahtar_kelimeler'] : '';
    $bildiri_turu = isset($_POST['bildiri_turu']) ? $_POST['bildiri_turu'] : '';
    $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
    $kullanici_id = $_SESSION['user_id'];
    
    // Yazarlar bilgisini al
    $yazar_adlar = isset($_POST['yazar_ad']) ? $_POST['yazar_ad'] : [];
    $yazar_emailler = isset($_POST['yazar_email']) ? $_POST['yazar_email'] : [];
    $yazar_kurumlar = isset($_POST['yazar_kurum']) ? $_POST['yazar_kurum'] : [];
    $yazar_telefonlar = isset($_POST['yazar_telefon']) ? $_POST['yazar_telefon'] : [];
    
    // Dosya yükleme işlemi
    $dosya_adi = null;
    $dosya_yolu = null;
    if (isset($_FILES['bildiri_dosya']) && $_FILES['bildiri_dosya']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/bildiriler/";
        
        // Klasör yoksa oluştur
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $dosya_adi = basename($_FILES["bildiri_dosya"]["name"]);
        $dosya_uzantisi = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
        
        // Dosya adını benzersiz yap
        $yeni_dosya_adi = uniqid('bildiri_') . '_' . time() . '.' . $dosya_uzantisi;
        $target_file = $target_dir . $yeni_dosya_adi;
        
        // İzin verilen dosya uzantıları
        $izin_verilen_uzantilar = array("pdf", "doc", "docx");
        
        // Hata kontrolü
        $errors = [];
        
        // Dosya uzantısı kontrolü
        if (!in_array($dosya_uzantisi, $izin_verilen_uzantilar)) {
            $errors[] = "Sadece PDF, DOC ve DOCX dosyaları yüklenebilir.";
        }
        
        // Dosya boyutu kontrolü (5MB)
        if ($_FILES["bildiri_dosya"]["size"] > 5000000) {
            $errors[] = "Dosya boyutu 5MB'dan küçük olmalıdır.";
        }
        
        // Hata yoksa dosyayı yükle
        if (empty($errors)) {
            if (move_uploaded_file($_FILES["bildiri_dosya"]["tmp_name"], $target_file)) {
                $dosya_adi = $dosya_adi;
                $dosya_yolu = $target_file;
            } else {
                $errors[] = "Dosya yükleme sırasında bir hata oluştu.";
            }
        }
    }
    
    // Hata kontrolü
    $form_errors = [];
    
    // Boş alan kontrolü
    if (empty($baslik) || empty($ozet) || empty($anahtar_kelimeler) || empty($bildiri_turu) || empty($kategori)) {
        $form_errors[] = "Tüm alanları doldurunuz.";
    }
    
    // Özet kelime sayısı kontrolü
    $kelime_sayisi = str_word_count($ozet);
    if ($kelime_sayisi > 300) {
        $form_errors[] = "Özet en fazla 300 kelime olmalıdır. Şu anki kelime sayısı: " . $kelime_sayisi;
    }
    
    // Yazar kontrolü
    if (empty($yazar_adlar) || count($yazar_adlar) < 1) {
        $form_errors[] = "En az bir yazar bilgisi girilmelidir.";
    }
    
    // Varsa dosya hatalarını ekle
    if (!empty($errors)) {
        $form_errors = array_merge($form_errors, $errors);
    }
    
    // Hata yoksa bildiriyi veritabanına ekle
    if (empty($form_errors)) {
        try {
            // Bildiriyi veritabanına ekle
            $stmt = $conn->prepare("INSERT INTO bildiriler (kullanici_id, baslik, bildiri_turu, kategori, anahtar_kelimeler, ozet, dosya_adi, dosya_yolu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $kullanici_id, $baslik, $bildiri_turu, $kategori, $anahtar_kelimeler, $ozet, $dosya_adi, $dosya_yolu);
            
            if (!$stmt->execute()) {
                throw new Exception("Bildiri kaydı sırasında bir hata oluştu: " . $stmt->error);
            }
            
            // Başarılı kayıt
            $response = [
                'status' => 'success',
                'message' => 'Tebrikler bildiri özeti başarıyla gönderildi.'
            ];
            
            // E-posta gönderimi geçici olarak devre dışı bırakıldı
            // Bildiri başarıyla kaydedildi
            $response['mailSent'] = false; // E-posta gönderimi devre dışı
            
            $stmt->close();
            
        } catch (Exception $e) {
            // Yüklenen dosyayı sil
            if ($dosya_yolu && file_exists($dosya_yolu)) {
                unlink($dosya_yolu);
            }
            
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    } else {
        // Form hataları
        $response = [
            'status' => 'error',
            'message' => implode('<br>', $form_errors)
        ];
    }
    
    // JSON yanıtı döndür
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<?php
// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğü dosyasını ayarla
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php_error.log');
error_log('Register.php çalıştırıldı - ' . date('Y-m-d H:i:s'));

// JSON yanıtı için header ayarla
header('Content-Type: application/json');

// Veritabanı bağlantısını dahil et
try {
    require_once 'config.php';
    error_log('config.php başarıyla dahil edildi');
    
    // Veritabanı bağlantısını kontrol et
    if (!isset($conn) || $conn->connect_error) {
        error_log('Veritabanı bağlantısı başarısız: ' . ($conn->connect_error ?? 'Bağlantı değişkeni tanımlı değil'));
        die(json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantısı başarısız. Lütfen daha sonra tekrar deneyin.']));
    }
    
    // Tablo yapısını kontrol et
    $table_check = $conn->query("SHOW TABLES LIKE 'kullanicilar'");
    if ($table_check->num_rows == 0) {
        error_log("'kullanicilar' tablosu bulunamadı!");
        die(json_encode(['status' => 'error', 'message' => 'Veritabanı tablosu bulunamadı. Lütfen yönetici ile iletişime geçin.']));
    }
    
    error_log('Veritabanı bağlantısı ve tablo kontrolü başarılı');
} catch (Exception $e) {
    error_log('Veritabanı bağlantısı hatası: ' . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantısı sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.']));
}

// POST isteği kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('POST isteği alındı');
    
    // Form verilerini al
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
    $kurum = isset($_POST['kurum']) ? trim($_POST['kurum']) : '';
    $unvan = isset($_POST['unvan']) ? trim($_POST['unvan']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    $sifre_tekrar = isset($_POST['sifre_tekrar']) ? $_POST['sifre_tekrar'] : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    error_log("Form verileri: ad=$ad, soyad=$soyad, email=$email, telefon=$telefon, kurum=$kurum, unvan=$unvan");
    
    // Hata dizisi
    $errors = [];
    
    // Form doğrulama
    if (empty($ad)) {
        $errors[] = 'Ad alanı boş bırakılamaz.';
    }
    
    if (empty($soyad)) {
        $errors[] = 'Soyad alanı boş bırakılamaz.';
    }
    
    if (empty($email)) {
        $errors[] = 'E-posta alanı boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($telefon)) {
        $errors[] = 'Telefon alanı boş bırakılamaz.';
    }
    
    if (empty($kurum)) {
        $errors[] = 'Kurum alanı boş bırakılamaz.';
    }
    
    if (empty($unvan)) {
        $errors[] = 'Unvan alanı boş bırakılamaz.';
    }
    
    if (empty($sifre)) {
        $errors[] = 'Şifre alanı boş bırakılamaz.';
    } elseif (strlen($sifre) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır.';
    }
    
    if ($sifre !== $sifre_tekrar) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }
    
    if (!$terms) {
        $errors[] = 'Kişisel verilerin işlenmesine ilişkin aydınlatma metnini kabul etmelisiniz.';
    }
    
    // E-posta adresi kontrolü
    if (empty($errors)) {
        try {
            // E-posta adresi daha önce kayıtlı mı?
            $check_email = $conn->prepare("SELECT id FROM kullanicilar WHERE email = ?");
            if (!$check_email) {
                throw new Exception('E-posta kontrol sorgusu hazırlanamadı: ' . $conn->error);
            }
            
            $check_email->bind_param('s', $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
            }
            
            $check_email->close();
        } catch (Exception $e) {
            error_log('E-posta kontrol hatası: ' . $e->getMessage());
            $errors[] = 'Kullanıcı kontrolü sırasında bir hata oluştu.';
        }
    }
    
    // Hata yoksa kayıt işlemini gerçekleştir
    if (empty($errors)) {
        try {
            // Şifreyi hashle
            $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
            error_log('Parola hashlendi');
            
            // Kullanıcıyı veritabanına ekle
            $stmt = $conn->prepare("INSERT INTO kullanicilar (ad, soyad, email, telefon, kurum, unvan, sifre, kayit_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if (!$stmt) {
                throw new Exception('Kayıt sorgusu hazırlanamadı: ' . $conn->error);
            }
            
            // Parametreler bağlanıyor
            if (!$stmt->bind_param('sssssss', $ad, $soyad, $email, $telefon, $kurum, $unvan, $hashed_password)) {
                throw new Exception('Parametre bağlama hatası: ' . $stmt->error);
            }
            
            // Sorgu çalıştırılıyor
            if (!$stmt->execute()) {
                throw new Exception('Sorgu çalıştırma hatası: ' . $stmt->error);
            }
            
            // Başarılı kayıt
            $user_id = $conn->insert_id;
            error_log("Kullanıcı başarıyla eklendi, ID: $user_id");
            
            $response = [
                'status' => 'success',
                'message' => 'Kayıt başarıyla tamamlandı. Giriş yapabilirsiniz.',
                'user_id' => $user_id,
                'redirect' => 'index.html'
            ];
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log('Kayıt işlemi hatası: ' . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage()
            ];
        }
    } else {
        // Form hataları
        error_log('Form hataları: ' . implode(', ', $errors));
        $response = [
            'status' => 'error',
            'message' => implode('<br>', $errors)
        ];
    }
    
    // JSON yanıtı döndür
    echo json_encode($response);
    exit;
} else {
    // POST olmayan istekler için hata mesajı
    error_log('POST olmayan istek alındı: ' . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Bu endpoint sadece POST istekleri kabul eder. Geçerli metod: ' . $_SERVER['REQUEST_METHOD']
    ]);
}
?>

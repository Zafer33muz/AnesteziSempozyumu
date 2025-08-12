<?php
// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğü dosyasını ayarla
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php_error.log');

// Oturum başlat
session_start();

// Veritabanı bağlantısını dahil et
try {
    require_once 'config.php';
    error_log("config.php başarıyla dahil edildi");
    
    // Veritabanı bağlantısını kontrol et
    if (!isset($conn) || $conn->connect_error) {
        error_log("Veritabanı bağlantısı başarısız: " . ($conn->connect_error ?? 'Bağlantı değişkeni tanımlı değil'));
        die(json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantısı başarısız. Lütfen daha sonra tekrar deneyin.']));
    }
    
    // Tablo yapısını kontrol et
    $table_check = $conn->query("SHOW TABLES LIKE 'kullanicilar'");
    if ($table_check->num_rows == 0) {
        error_log("'kullanicilar' tablosu bulunamadı!");
        die(json_encode(['status' => 'error', 'message' => 'Veritabanı tablosu bulunamadı. Lütfen yönetici ile iletişime geçin.']));
    }
    
    error_log("Veritabanı bağlantısı ve tablo kontrolü başarılı");
} catch (Exception $e) {
    error_log("Veritabanı bağlantısı hatası: " . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantısı sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.']));
}

// Hata ayıklama için log dosyasına yaz
error_log('Login.php çalıştırıldı - ' . date('Y-m-d H:i:s'));

// POST isteği kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Normal POST verilerini kullan (form gönderimi için)
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    
    // POST verilerini günlüğe kaydet
    error_log('POST verileri: ' . print_r($_POST, true));
    
    error_log("Giriş denemesi - E-posta: $email, Şifre uzunluğu: " . strlen($sifre));
    
    // Hata kontrolü
    $errors = [];
    
    // Boş alan kontrolü
    if (empty($email) || empty($sifre)) {
        $errors[] = "E-posta ve şifre alanlarını doldurunuz.";
        error_log("Boş alan hatası: E-posta veya şifre boş");
    }
    
    // Hata yoksa giriş işlemini gerçekleştir
    if (empty($errors)) {
        try {
            // Kullanıcıyı veritabanında ara
            $stmt = $conn->prepare("SELECT id, ad, soyad, email, sifre FROM kullanicilar WHERE email = ?");
            if (!$stmt) {
                throw new Exception('Prepare hatası: ' . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception('Execute hatası: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            error_log('Sorgu sonucu: ' . ($result ? $result->num_rows : 'null') . ' satır bulundu');
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                error_log("Kullanıcı bulundu: ID={$user['id']}, Ad={$user['ad']}, Soyad={$user['soyad']}");
                
                // Veritabanındaki hash'i görüntüle (sadece hata ayıklama için)
                error_log("Veritabanındaki şifre hash'i: {$user['sifre']}");
                
                // Şifre doğrulama
                if (password_verify($sifre, $user['sifre'])) {
                    error_log("Şifre doğrulama başarılı");
                    
                    // Oturum bilgilerini kaydet
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['ad'] . ' ' . $user['soyad'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['logged_in'] = true;
                    
                    error_log("Oturum bilgileri kaydedildi: user_id={$_SESSION['user_id']}, user_name={$_SESSION['user_name']}");
                    
                    // Son giriş zamanını güncelle
                    $update_stmt = $conn->prepare("UPDATE kullanicilar SET son_giris = CURRENT_TIMESTAMP WHERE id = ?");
                    if (!$update_stmt) {
                        error_log("Son giriş güncelleme için prepare hatası: " . $conn->error);
                    } else {
                        $update_stmt->bind_param("i", $user['id']);
                        if (!$update_stmt->execute()) {
                            error_log("Son giriş güncelleme hatası: " . $update_stmt->error);
                        }
                        $update_stmt->close();
                    }
                    
                    // Başarılı giriş
                    $response = [
                        'status' => 'success',
                        'message' => 'Giriş başarılı. Bildiri gönderim sayfasına yönlendiriliyorsunuz.',
                        'redirect' => '#bildiriForm' // Bildiri formuna yönlendir
                    ];
                    error_log("Başarılı giriş yanıtı: " . json_encode($response));
                } else {
                    error_log("Şifre doğrulama başarısız - Girilen şifre: $sifre");
                    // Şifre hatalı
                    $response = [
                        'status' => 'error',
                        'message' => 'E-posta adresi veya şifre hatalı.'
                    ];
                }
            } else {
                error_log("Kullanıcı bulunamadı: $email");
                // Kullanıcı bulunamadı
                $response = [
                    'status' => 'error',
                    'message' => 'E-posta adresi veya şifre hatalı.'
                ];
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Giriş işlemi sırasında istisna: " . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => 'Giriş sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.'
            ];
        }
    } else {
        // Form hataları
        $response = [
            'status' => 'error',
            'message' => implode('<br>', $errors)
        ];
        error_log("Form hataları: " . implode(', ', $errors));
    }
    
    // JSON yanıtı döndür
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

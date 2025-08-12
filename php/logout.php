<?php
// Oturum başlat
session_start();

// Tüm oturum değişkenlerini temizle
$_SESSION = array();

// Oturum çerezini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Oturumu sonlandır
session_destroy();

// JSON yanıtı döndür
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Çıkış işleminiz başarıyla gerçekleştirildi.'
]);
?>

<?php
// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğü dosyasını ayarla
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php_error.log');
error_log('Config.php çalıştırıldı - ' . date('Y-m-d H:i:s'));

// Veritabanı bağlantı parametreleri
$servername = "localhost";
$username = "root"; // MySQL kullanıcı adınızı buraya yazın
$password = "";     // MySQL şifrenizi buraya yazın
$dbname = "kullanicilar";

// Önce veritabanının varlığını kontrol et
try {
    // Veritabanı adı olmadan bağlantı kur
    $temp_conn = new mysqli($servername, $username, $password);
    error_log("Ana MySQL sunucusuna bağlantı deneniyor: $servername, $username");
    
    if ($temp_conn->connect_error) {
        throw new Exception("MySQL sunucusuna bağlantı başarısız: " . $temp_conn->connect_error);
    }
    
    // Veritabanının varlığını kontrol et
    $result = $temp_conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        // Veritabanı yoksa oluştur
        error_log("Veritabanı '$dbname' bulunamadı, oluşturuluyor...");
        if (!$temp_conn->query("CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci")) {
            throw new Exception("Veritabanı oluşturma hatası: " . $temp_conn->error);
        }
        error_log("'$dbname' veritabanı başarıyla oluşturuldu");
    } else {
        error_log("'$dbname' veritabanı zaten mevcut");
    }
    
    $temp_conn->close();
} catch (Exception $e) {
    error_log("Veritabanı kontrol hatası: " . $e->getMessage());
    die("Veritabanı kontrol hatası: " . $e->getMessage());
}

// Asıl veritabanı bağlantısını oluştur
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    error_log("Veritabanı bağlantısı deneniyor: $servername, $username, $dbname");
    
    // Bağlantıyı kontrol et
    if ($conn->connect_error) {
        throw new Exception("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    }
    
    // Türkçe karakter desteği için
    $conn->set_charset("utf8");
    error_log("Veritabanı bağlantısı başarılı");
    
    // Tabloların varlığını kontrol et
    $tables_result = $conn->query("SHOW TABLES LIKE 'kullanicilar'");
    if ($tables_result->num_rows == 0) {
        error_log("'kullanicilar' tablosu bulunamadı, oluşturuluyor...");
        
        // SQL dosyasını oku ve çalıştır
        $sql_file = dirname(__FILE__) . '/database.sql';
        if (file_exists($sql_file)) {
            error_log("database.sql dosyası bulundu, içe aktarılıyor...");
            $sql = file_get_contents($sql_file);
            if ($conn->multi_query($sql)) {
                do {
                    // Her sorgu sonucunu tüket
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                error_log("Tablolar başarıyla oluşturuldu");
            } else {
                throw new Exception("SQL dosyası çalıştırma hatası: " . $conn->error);
            }
        } else {
            throw new Exception("database.sql dosyası bulunamadı");
        }
    } else {
        error_log("'kullanicilar' tablosu zaten mevcut");
    }
} catch (Exception $e) {
    error_log("Veritabanı hatası: " . $e->getMessage());
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

?>

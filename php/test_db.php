<?php
// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğü dosyasını ayarla
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php_error.log');

echo "<h1>Veritabanı Bağlantı Testi</h1>";
echo "<p>Bu sayfa veritabanı bağlantısını ve tabloların varlığını test eder.</p>";
echo "<hr>";

echo "<h2>MySQL Bağlantı Bilgileri:</h2>";
echo "<ul>";
echo "<li>Sunucu: localhost</li>";
echo "<li>Kullanıcı: root</li>";
echo "<li>Veritabanı: mersin_anestezi</li>";
echo "</ul>";

// Veritabanı bağlantı parametreleri
$servername = "localhost";
$username = "root"; // MySQL kullanıcı adınızı buraya yazın
$password = "";     // MySQL şifrenizi buraya yazın
$dbname = "mersin_anestezi";

echo "<h2>Bağlantı Parametreleri:</h2>";
echo "<p>Sunucu: $servername</p>";
echo "<p>Kullanıcı: $username</p>";
echo "<p>Veritabanı: $dbname</p>";

// Veritabanı bağlantısını oluştur
try {
    echo "<h2>Veritabanı Bağlantısı:</h2>";
    
    // Veritabanı varlığını kontrol et
    $conn_test = new mysqli($servername, $username, $password);
    if ($conn_test->connect_error) {
        throw new Exception("MySQL bağlantısı başarısız: " . $conn_test->connect_error);
    }
    
    echo "<p style='color:green'>MySQL bağlantısı başarılı.</p>";
    
    // Veritabanı varlığını kontrol et
    $result = $conn_test->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        echo "<p style='color:red'>Veritabanı '$dbname' bulunamadı!</p>";
        echo "<p>Veritabanını oluşturmak için aşağıdaki SQL komutunu çalıştırın:</p>";
        echo "<pre>CREATE DATABASE $dbname;</pre>";
        echo "<p>Veya database.sql dosyasını içe aktarın.</p>";
        exit;
    }
    
    echo "<p style='color:green'>Veritabanı '$dbname' mevcut.</p>";
    
    // Veritabanını seç
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    }
    
    // Türkçe karakter desteği için
    $conn->set_charset("utf8");
    
    // Tabloları kontrol et
    echo "<h2>Tablo Kontrolü:</h2>";
    
    // Kullanıcılar tablosunu kontrol et
    $result = $conn->query("SHOW TABLES LIKE 'kullanicilar'");
    if ($result->num_rows == 0) {
        echo "<p style='color:red'>Kullanıcılar tablosu bulunamadı!</p>";
        echo "<p>Tabloları oluşturmak için database.sql dosyasını içe aktarın.</p>";
    } else {
        echo "<p style='color:green'>Kullanıcılar tablosu mevcut.</p>";
        
        // Tablo yapısını kontrol et
        $result = $conn->query("DESCRIBE kullanicilar");
        echo "<p>Kullanıcılar tablosu yapısı:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Anahtar</th><th>Varsayılan</th><th>Ekstra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Kullanıcı sayısını kontrol et
        $result = $conn->query("SELECT COUNT(*) as total FROM kullanicilar");
        $row = $result->fetch_assoc();
        echo "<p>Toplam kullanıcı sayısı: " . $row['total'] . "</p>";
    }
    
    // Bildiriler tablosunu kontrol et
    $result = $conn->query("SHOW TABLES LIKE 'bildiriler'");
    if ($result->num_rows == 0) {
        echo "<p style='color:red'>Bildiriler tablosu bulunamadı!</p>";
        echo "<p>Tabloları oluşturmak için database.sql dosyasını içe aktarın.</p>";
    } else {
        echo "<p style='color:green'>Bildiriler tablosu mevcut.</p>";
        
        // Tablo yapısını kontrol et
        $result = $conn->query("DESCRIBE bildiriler");
        echo "<p>Bildiriler tablosu yapısı:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Alan</th><th>Tip</th><th>Null</th><th>Anahtar</th><th>Varsayılan</th><th>Ekstra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Bildiri sayısını kontrol et
        $result = $conn->query("SELECT COUNT(*) as total FROM bildiriler");
        $row = $result->fetch_assoc();
        echo "<p>Toplam bildiri sayısı: " . $row['total'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Hata: " . $e->getMessage() . "</p>";
    error_log("Veritabanı test hatası: " . $e->getMessage());
}
?>

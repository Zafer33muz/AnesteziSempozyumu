<?php
/**
 * Simple mailer class for sending bildiri (abstract) submission notifications
 */
class BildiriMailer {
    private $to;
    private $from;
    private $fromName;
    private $subject;
    private $isHTML = true;
    
    /**
     * Constructor
     * 
     * @param string $to Recipient email address
     * @param string $from Sender email address
     * @param string $fromName Sender name
     */
    public function __construct($to, $from, $fromName) {
        $this->to = $to;
        $this->from = $from;
        $this->fromName = $fromName;
    }
    
    /**
     * Send bildiri submission notification
     * 
     * @param array $bildiriData Abstract submission data
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendBildiriNotification($bildiriData) {
        // Set subject
        $this->subject = "Yeni Bildiri Özeti Gönderildi: " . $bildiriData['baslik'];
        
        // Create email content
        $message = $this->createEmailContent($bildiriData);
        
        // Set headers
        $headers = $this->getHeaders();
        
        // Send email
        return mail($this->to, $this->subject, $message, $headers);
    }
    
    /**
     * Create email content from bildiri data
     * 
     * @param array $bildiriData Abstract submission data
     * @return string Email content
     */
    private function createEmailContent($bildiriData) {
        $content = "<html><body>";
        $content .= "<h2>Yeni Bildiri Özeti Gönderildi</h2>";
        $content .= "<p><strong>Başlık:</strong> " . htmlspecialchars($bildiriData['baslik']) . "</p>";
        $content .= "<p><strong>Bildiri Türü:</strong> " . htmlspecialchars($bildiriData['bildiri_turu']) . "</p>";
        $content .= "<p><strong>Kategori:</strong> " . htmlspecialchars($bildiriData['kategori']) . "</p>";
        $content .= "<p><strong>Anahtar Kelimeler:</strong> " . htmlspecialchars($bildiriData['anahtar_kelimeler']) . "</p>";
        
        // Add author information if available
        if (!empty($bildiriData['yazar_ad'])) {
            $content .= "<h3>Yazarlar</h3>";
            $content .= "<ul>";
            
            foreach ($bildiriData['yazar_ad'] as $index => $ad) {
                $content .= "<li>";
                $content .= "<strong>Ad:</strong> " . htmlspecialchars($ad) . ", ";
                $content .= "<strong>Email:</strong> " . htmlspecialchars($bildiriData['yazar_email'][$index]) . ", ";
                $content .= "<strong>Kurum:</strong> " . htmlspecialchars($bildiriData['yazar_kurum'][$index]);
                $content .= "</li>";
            }
            
            $content .= "</ul>";
        }
        
        // Add abstract text
        $content .= "<h3>Özet</h3>";
        $content .= "<p>" . nl2br(htmlspecialchars($bildiriData['ozet'])) . "</p>";
        
        // Add file information if available
        if (!empty($bildiriData['dosya_adi'])) {
            $content .= "<p><strong>Dosya:</strong> " . htmlspecialchars($bildiriData['dosya_adi']) . "</p>";
        }
        
        $content .= "<p>Bu bildirim otomatik olarak gönderilmiştir.</p>";
        $content .= "</body></html>";
        
        return $content;
    }
    
    /**
     * Get email headers
     * 
     * @return string Email headers
     */
    private function getHeaders() {
        $headers = "From: " . $this->fromName . " <" . $this->from . ">\r\n";
        $headers .= "Reply-To: " . $this->from . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        if ($this->isHTML) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }
        
        return $headers;
    }
}
?>

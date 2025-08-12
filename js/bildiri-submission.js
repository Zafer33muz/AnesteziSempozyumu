// Bildiri Submission JavaScript

// Bildirim gösterme fonksiyonu
function showAlert(type, message) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    `;
    
    // Mevcut bildirimleri temizle
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => {
        alert.remove();
    });
    
    // Bildirimi ekle
    const modalBody = document.querySelector('.modal-body');
    if (modalBody) {
        modalBody.insertBefore(alertContainer, modalBody.firstChild);
    }
    
    // 5 saniye sonra otomatik kapat
    setTimeout(() => {
        alertContainer.classList.remove('show');
        setTimeout(() => {
            alertContainer.remove();
        }, 300);
    }, 5000);
}

// Dosya işleme fonksiyonu
function handleFile(file) {
    if (!file) return;
    
    const filePreview = document.querySelector('.file-preview');
    const fileUploadMessage = document.querySelector('.file-upload-message');
    const fileName = document.querySelector('.file-name');
    const fileSize = document.querySelector('.file-size');
    
    // Dosya boyutunu formatla
    const formatFileSize = (bytes) => {
        if (bytes < 1024) return bytes + ' B';
        else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
        else return (bytes / 1048576).toFixed(2) + ' MB';
    };
    
    // Dosya tipini kontrol et
    const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!validTypes.includes(file.type)) {
        showAlert('danger', 'Sadece PDF, DOC ve DOCX dosyaları yüklenebilir.');
        return;
    }
    
    // Dosya boyutunu kontrol et (5MB)
    if (file.size > 5000000) {
        showAlert('danger', 'Dosya boyutu 5MB\'dan küçük olmalıdır.');
        return;
    }
    
    // Dosya önizlemesini göster
    if (filePreview && fileUploadMessage && fileName && fileSize) {
        filePreview.classList.remove('d-none');
        fileUploadMessage.classList.add('d-none');
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
    }
}

// Sürükle bırak işlevselliği
function initDragAndDrop() {
    const dropZone = document.getElementById('dropZone');
    const fileUpload = document.getElementById('fileUpload');
    
    if (!dropZone || !fileUpload) return;
    
    // Highlight drop zone when dragging over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.classList.add('highlight');
        });
    });
    
    // Remove highlight when dragging leaves
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.classList.remove('highlight');
        });
    });
    
    // Handle file drop
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        handleFile(file);
    });
    
    // Handle file selection via button
    fileUpload.addEventListener('change', function() {
        const file = this.files[0];
        handleFile(file);
    });
    
    // Remove file button
    const removeFileBtn = document.querySelector('.remove-file');
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', function() {
            const filePreview = document.querySelector('.file-preview');
            const fileUploadMessage = document.querySelector('.file-upload-message');
            
            if (filePreview && fileUploadMessage) {
                filePreview.classList.add('d-none');
                fileUploadMessage.classList.remove('d-none');
                fileUpload.value = '';
            }
        });
    }
}

// Yazar ekleme işlevselliği
function initAuthorAddition() {
    const addAuthorBtn = document.getElementById('addAuthorBtn');
    const authorsList = document.getElementById('authorsList');
    
    if (!addAuthorBtn || !authorsList) return;
    
    let authorCount = 1;
    
    addAuthorBtn.addEventListener('click', function() {
        authorCount++;
        
        const authorItem = document.createElement('div');
        authorItem.className = 'author-item mb-3 p-3 border rounded';
        
        authorItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Yazar #${authorCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-author">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-posta <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kurum <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefon <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" required>
                </div>
            </div>
        `;
        
        authorsList.appendChild(authorItem);
        
        // Yazar silme butonu
        const removeAuthorBtn = authorItem.querySelector('.remove-author');
        if (removeAuthorBtn) {
            removeAuthorBtn.addEventListener('click', function() {
                authorsList.removeChild(authorItem);
                
                // Yazar numaralarını güncelle
                const authorItems = authorsList.querySelectorAll('.author-item');
                authorItems.forEach((item, index) => {
                    const authorNumber = item.querySelector('h6');
                    if (authorNumber) {
                        if (index === 0) {
                            authorNumber.textContent = `Yazar #${index + 1} (Sorumlu Yazar)`;
                        } else {
                            authorNumber.textContent = `Yazar #${index + 1}`;
                        }
                    }
                });
                
                authorCount = authorItems.length;
            });
        }
    });
}

// Bildiri formu gönderimi
function initFormSubmission() {
    const submitAbstractBtn = document.getElementById('submitAbstractBtn');
    if (!submitAbstractBtn) return;
    
    submitAbstractBtn.addEventListener('click', function() {
        const abstractForm = document.getElementById('abstractForm');
        if (!abstractForm) return;
        
        // Form doğrulama
        if (!abstractForm.checkValidity()) {
            abstractForm.reportValidity();
            return;
        }
        
        // Etik kurul ve koşullar kontrolü
        const ethicsCheck = document.getElementById('ethicsCheck');
        const termsCheck = document.getElementById('termsCheck');
        
        if (!ethicsCheck.checked || !termsCheck.checked) {
            showAlert('danger', 'Lütfen etik kurul onayını ve bildiri gönderim koşullarını kabul ediniz.');
            return;
        }
        
        // Form verilerini al
        const formData = new FormData();
        formData.append('baslik', document.getElementById('abstractTitle').value);
        formData.append('bildiri_turu', document.getElementById('abstractType').value);
        formData.append('kategori', document.getElementById('abstractCategory').value);
        formData.append('anahtar_kelimeler', document.getElementById('abstractKeywords').value);
        formData.append('ozet', document.getElementById('abstractSummary').value);
        
        // Yazarları ekle
        const authorItems = document.querySelectorAll('.author-item');
        if (authorItems.length > 0) {
            authorItems.forEach((item, index) => {
                const inputs = item.querySelectorAll('input');
                if (inputs.length >= 4) {
                    formData.append(`yazar_ad[${index}]`, inputs[0].value);
                    formData.append(`yazar_email[${index}]`, inputs[1].value);
                    formData.append(`yazar_kurum[${index}]`, inputs[2].value);
                    formData.append(`yazar_telefon[${index}]`, inputs[3].value);
                }
            });
        }
        
        // Dosya ekle
        const fileUpload = document.getElementById('fileUpload');
        if (fileUpload && fileUpload.files[0]) {
            formData.append('bildiri_dosya', fileUpload.files[0]);
        }
        
        // Gönderim butonu devre dışı bırak ve yükleniyor göster
        submitAbstractBtn.disabled = true;
        submitAbstractBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gönderiliyor...';
        
        // AJAX isteği gönder
        fetch('php/submit_bildiri.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Gönderim butonu aktif et
            submitAbstractBtn.disabled = false;
            submitAbstractBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Bildiri Gönder';
            
            if (data.status === 'success') {
                // Başarılı gönderim
                showAlert('success', data.message);
                
                // Formu temizle
                abstractForm.reset();
                
                // Dosya yükleme alanını sıfırla
                const filePreview = document.querySelector('.file-preview');
                const fileUploadMessage = document.querySelector('.file-upload-message');
                
                if (filePreview && fileUploadMessage) {
                    filePreview.classList.add('d-none');
                    fileUploadMessage.classList.remove('d-none');
                    fileUpload.value = '';
                }
                
                // Ek yazarları temizle (ilk yazar hariç)
                const authorsList = document.getElementById('authorsList');
                if (authorsList) {
                    const authorItems = authorsList.querySelectorAll('.author-item');
                    for (let i = authorItems.length - 1; i > 0; i--) {
                        authorsList.removeChild(authorItems[i]);
                    }
                }
            } else {
                // Gönderim başarısız
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            // Gönderim butonu aktif et
            submitAbstractBtn.disabled = false;
            submitAbstractBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Bildiri Gönder';
            
            console.error('Bildiri gönderim hatası:', error);
            showAlert('danger', 'Bildiri gönderimi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        });
    });
}

// Bildiri gönderim arayüzünü oluştur
function createSubmissionInterface() {
    const submissionInterface = document.createElement('div');
    submissionInterface.id = 'submissionInterface';
    submissionInterface.className = 'submission-interface';
    
    submissionInterface.innerHTML = `
        <div class="submission-header mb-4">
            <h3 class="text-center"><i class="fas fa-file-upload me-2" style="color: #EE7003;"></i>Bildiri Özeti Gönderimi</h3>
            <p class="text-center text-muted">Lütfen bildiri özetinizi aşağıdaki formu kullanarak gönderiniz.</p>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Bildiri Bilgileri</h5>
            </div>
            <div class="card-body p-4">
                <form id="abstractForm" class="row g-3">
                    <div class="col-md-12 mb-3">
                        <label for="abstractTitle" class="form-label">Bildiri Başlığı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="abstractTitle" name="baslik" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="abstractType" class="form-label">Bildiri Tipi <span class="text-danger">*</span></label>
                        <select class="form-select" id="abstractType" name="bildiri_turu" required>
                            <option value="" selected disabled>Seçiniz</option>
                            <option value="Sözlü">Sözlü Bildiri</option>
                            <option value="Poster">Poster Bildiri</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="abstractCategory" class="form-label">Bildiri Kategorisi <span class="text-danger">*</span></label>
                        <select class="form-select" id="abstractCategory" name="kategori" required>
                            <option value="" selected disabled>Seçiniz</option>
                            <option value="Genel Anestezi">Genel Anestezi</option>
                            <option value="Rejyonel Anestezi">Rejyonel Anestezi</option>
                            <option value="Yoğun Bakım">Yoğun Bakım</option>
                            <option value="Ağrı">Ağrı</option>
                            <option value="Pediatrik Anestezi">Pediatrik Anestezi</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="abstractKeywords" class="form-label">Anahtar Kelimeler <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="abstractKeywords" name="anahtar_kelimeler" placeholder="Virgülle ayırarak giriniz" required>
                        <div class="form-text">En az 3, en fazla 5 anahtar kelime giriniz.</div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="abstractSummary" class="form-label">Özet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="abstractSummary" name="ozet" rows="6" required></textarea>
                        <div class="form-text">En fazla 300 kelime olmalıdır.</div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Yazarlar</h5>
            </div>
            <div class="card-body p-4">
                <div id="authorsList">
                    <div class="author-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Yazar #1 (Sorumlu Yazar)</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="yazar_ad[]" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="yazar_email[]" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kurum <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="yazar_kurum[]" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="yazar_telefon[]" required>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="addAuthorBtn">
                    <i class="fas fa-plus me-2"></i>Yazar Ekle
                </button>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-file-upload text-primary me-2"></i>Dosya Yükleme</h5>
            </div>
            <div class="card-body p-4">
                <div class="file-upload-container mb-3">
                    <div class="file-upload-area" id="dropZone">
                        <div class="file-upload-message">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #EE7003;"></i>
                            <h5>Dosyanızı buraya sürükleyip bırakın</h5>
                            <p class="text-muted">veya</p>
                            <label for="fileUpload" class="btn btn-outline-primary">
                                <i class="fas fa-folder-open me-2"></i>Dosya Seçin
                            </label>
                            <input type="file" id="fileUpload" name="bildiri_dosya" class="d-none" accept=".pdf,.doc,.docx">
                            <p class="mt-2 text-muted small">Desteklenen formatlar: PDF, DOC, DOCX (Maks. 5MB)</p>
                        </div>
                        <div class="file-preview d-none">
                            <div class="file-info p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf fa-2x me-3" style="color: #EE7003;"></i>
                                    <div class="file-details">
                                        <h6 class="file-name mb-1">dosya.pdf</h6>
                                        <span class="file-size text-muted">0 KB</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-file">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="ethicsCheck" required>
                    <label class="form-check-label" for="ethicsCheck">
                        Etik kurul onayı gerektiren bir çalışma ise, etik kurul onayı alınmıştır.
                    </label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="termsCheck" required>
                    <label class="form-check-label" for="termsCheck">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Bildiri gönderim koşullarını</a> okudum ve kabul ediyorum.
                    </label>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary btn-lg" id="submitAbstractBtn">
                <i class="fas fa-paper-plane me-2"></i>Bildiri Özetini Gönder
            </button>
        </div>
    `;
    
    return submissionInterface;
}

// Sayfa yüklendiğinde çalışacak kod
document.addEventListener('DOMContentLoaded', function() {
    // Bildiri gönderim arayüzünü oluştur ve sayfaya ekle
    const submissionContainer = document.querySelector('.bildiri-submission-container');
    if (submissionContainer) {
        submissionContainer.appendChild(createSubmissionInterface());
        
        // Form gönderim işlevini başlat
        initFormSubmission();
        
        // Dosya yükleme işlevini başlat
        initDragAndDrop();
        
        // Yazar ekleme işlevini başlat
        initAuthorAddition();
    }
    // URL parametrelerini kontrol et ve login=true varsa giriş modalını aç
    function checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('login') === 'true') {
            // Bildiri gönderim modalını aç
            const bildiriModal = new bootstrap.Modal(document.getElementById('bildiriGonderimModal'));
            if (bildiriModal) {
                bildiriModal.show();
                // Login tabını aktif et
                const loginTab = document.getElementById('login-tab');
                if (loginTab) {
                    loginTab.click();
                }
            }
        }
    }
    
    // URL parametrelerini kontrol et
    checkUrlParams();
    
    // Çıkış butonu işlevini ekle
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // AJAX isteği gönder
            fetch('php/logout.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Başarılı çıkış
                    showAlert('success', data.message || 'Çıkış işleminiz başarıyla gerçekleştirildi.');
                    
                    // Modalı kapat
                    setTimeout(() => {
                        const bildiriModal = bootstrap.Modal.getInstance(document.getElementById('bildiriGonderimModal'));
                        if (bildiriModal) {
                            bildiriModal.hide();
                        }
                        
                        // Sayfayı yenile
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }, 1500);
                } else {
                    // Çıkış başarısız
                    showAlert('danger', data.message || 'Çıkış sırasında bir hata oluştu.');
                }
            })
            .catch(error => {
                console.error('Çıkış hatası:', error);
                showAlert('danger', 'Çıkış sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            });
        });
    }
    
    // Login form submission handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Form verilerini al
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Form verilerini hazırla
            const formData = new FormData();
            formData.append('email', email);
            formData.append('sifre', password);
            
            // AJAX isteği gönder
            fetch('php/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Başarılı giriş
                    showAlert('success', data.message);
                    
                    // Hide the login form
                    document.querySelector('#login .row').style.display = 'none';
                    
                    // Bildiri gönderim formuna yönlendir
                    if (data.redirect) {
                        // Bildiri gönderim sekmesini aktif et
                        const bildiriTab = document.querySelector('#bildiriGonderimModal .nav-link[href="#bildiriForm"]');
                        if (bildiriTab) {
                            const tab = new bootstrap.Tab(bildiriTab);
                            tab.show();
                        }
                    }
                    
                    // Show the submission interface
                    const submissionInterface = createSubmissionInterface();
                    document.querySelector('#login').appendChild(submissionInterface);
                    
                    // Bildiri formunu başlat
                    initFormSubmission();
                    
                    // Sürükle bırak işlevselliğini başlat
                    initDragAndDrop();
                    
                    // Yazar ekleme işlevselliğini başlat
                    initAuthorAddition();
                } else {
                    // Giriş başarısız
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Giriş hatası:', error);
                showAlert('danger', 'Giriş sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            });
        });
    }
    
    // Kayıt formu işleme kodu kaldırıldı
});

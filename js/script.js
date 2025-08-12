// Main JavaScript for Mersin Anestezi Semineri Website

// Preloader
window.addEventListener('load', function() {
    console.log('Sayfa yüklendi');
    const preloader = document.getElementById('preloader');
    if (preloader) {
        console.log('Preloader bulundu, kaldırılıyor...');
        preloader.style.opacity = '0';
        setTimeout(function() {
            preloader.style.display = 'none';
            document.body.style.visibility = 'visible';
            console.log('Preloader kaldırıldı, sayfa görünür yapıldı');
        }, 500);
    } else {
        console.log('Preloader bulunamadı');
        document.body.style.visibility = 'visible';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Countdown Timer
    // Set the date we're counting down to (November 11, 2025)
    const countDownDate = new Date("Nov 11, 2025 00:00:00").getTime();
    
    // Update the countdown every 1 second
    const countdownTimer = setInterval(function() {
        // Get today's date and time
        const now = new Date().getTime();
        
        // Find the distance between now and the countdown date
        const distance = countDownDate - now;
        
        // Time calculations for days, hours, minutes and seconds
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        // Display the results
        document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
        document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
        document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
        document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
        
        // If the countdown is finished, display a message
        if (distance < 0) {
            clearInterval(countdownTimer);
            document.getElementById("days").innerHTML = "00";
            document.getElementById("hours").innerHTML = "00";
            document.getElementById("minutes").innerHTML = "00";
            document.getElementById("seconds").innerHTML = "00";
        }
    }, 1000);
    
    // Initialize AOS (Animate on Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Hero Slider (Owl Carousel)
    $('.hero-slider').owlCarousel({
        items: 1,
        loop: true,
        margin: 0,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        smartSpeed: 1000,
        animateOut: 'fadeOut',
    });
    
    // Initialize Owl Carousel for speakers slider
    $('.speakers-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 4000,
        autoplayHoverPause: true,
        navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'],
        responsive: {
            0: {
                items: 1,
                nav: false
            },
            576: {
                items: 2,
                nav: false
            },
            768: {
                items: 3
            },
            992: {
                items: 4
            }
        }
    });
    
    // Program tabs animation and interaction
    const programTabs = document.querySelectorAll('#programTab .nav-link');
    if (programTabs.length > 0) {
        programTabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
                const sessions = targetPane.querySelectorAll('.program-session');
                
                sessions.forEach((session, index) => {
                    // Reset animation
                    session.style.opacity = '0';
                    session.style.transform = 'translateY(20px)';
                    
                    // Animate with delay based on index
                    setTimeout(() => {
                        session.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        session.style.opacity = '1';
                        session.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            });
        });
        
        // Trigger animation for initial active tab
        const initialActiveTab = document.querySelector('#programTab .nav-link.active');
        if (initialActiveTab) {
            const targetPane = document.querySelector(initialActiveTab.getAttribute('data-bs-target'));
            const sessions = targetPane.querySelectorAll('.program-session');
            
            sessions.forEach((session, index) => {
                // Initial animation with delay
                session.style.opacity = '0';
                session.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    session.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    session.style.opacity = '1';
                    session.style.transform = 'translateY(0)';
                }, 500 + (100 * index)); // Additional delay for initial load
            });
        }
    }
    
    // Session hover effects
    const programSessions = document.querySelectorAll('.program-session');
    if (programSessions.length > 0) {
        programSessions.forEach(session => {
            session.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
            });
            
            session.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
            });
        });
    }
    
    // Legacy Program Tabs (if needed)
    const legacyProgramTabs = document.querySelectorAll('.program-tab');
    const programContents = document.querySelectorAll('.program-content');
    
    if (legacyProgramTabs.length > 0) {
        legacyProgramTabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                legacyProgramTabs.forEach(t => t.classList.remove('active'));
                programContents.forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                programContents[index].classList.add('active');
            });
        });
        
        // Set first tab as active by default
        if (legacyProgramTabs[0]) {
            legacyProgramTabs[0].click();
        }
    }
    
    // Sticky Navigation
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                navbar.classList.add('sticky-top');
            } else {
                navbar.classList.remove('sticky-top');
            }
        });
    }
    
    // Back to Top Button
    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Kayıt formu işlemi
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        // Form mesaj alanı için bir fonksiyon oluştur
        function showFormMessage(message, type) {
            // Mevcut mesaj alanını kontrol et
            let messageArea = document.getElementById('formMessages');
            
            // Eğer mesaj alanı yoksa oluştur
            if (!messageArea) {
                messageArea = document.createElement('div');
                messageArea.id = 'formMessages';
                messageArea.className = 'alert mt-3';
                // Formdaki submit butonunun üstüne ekle
                const submitBtn = registrationForm.querySelector('button[type="submit"]').parentNode;
                submitBtn.parentNode.insertBefore(messageArea, submitBtn);
            }
            
            // Mesaj tipine göre sınıf ekle
            messageArea.className = 'alert mt-3 ' + (type === 'success' ? 'alert-success' : 'alert-danger');
            messageArea.innerHTML = message;
            messageArea.style.display = 'block';
            
            // Mesaj alanına scroll yap
            messageArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Form verilerini doğrudan formdan al
            const formData = new FormData(registrationForm);
            
            // Şifre kontrolü
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                showFormMessage('Şifreler eşleşmiyor!', 'error');
                return;
            }
            
            // Submit butonunu devre dışı bırak
            const submitButton = registrationForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Gönderiliyor...';
            
            // AJAX isteği gönder
            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Submit butonunu tekrar etkinleştir
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                if (data.status === 'success') {
                    showFormMessage(data.message, 'success');
                    // Başarılı kayıt sonrası formu sıfırla
                    setTimeout(() => {
                        registrationForm.reset();
                        // Ana sayfaya yönlendir
                        window.location.href = 'index.html';
                    }, 2000); // 2 saniye bekle
                } else {
                    showFormMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                // Submit butonunu tekrar etkinleştir
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                showFormMessage('Kayıt işlemi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.', 'error');
            });
        });
    }
    
    // Giriş formu işlemi
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('Giriş formu gönderiliyor...');
            
            // Form verilerini FormData olarak al
            const formData = new FormData();
            formData.append('email', document.getElementById('loginEmail').value);
            formData.append('sifre', document.getElementById('loginPassword').value);
            
            console.log('Giriş verileri hazırlandı, gönderiliyor...');
            
            // AJAX isteği gönder
            fetch('php/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    // Başarılı giriş sonrası yönlendirme
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Giriş işlemi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            });
        });
    }
});

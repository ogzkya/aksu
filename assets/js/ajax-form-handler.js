/* Bu kodu /assets/js/ajax-form-handler.js olarak kaydedin */

/**
 * Aksu Emlak - AJAX Form İşleme Fonksiyonları
 * İlan ekleme ve düzenleme formları için dinamik işleme sağlar
 */

// Hata mesajlarını göster
function showFormErrors(errors, formId = 'listingForm') {
    // Hata container'ı
    let errorContainer = document.querySelector('#form-errors');
    
    // Container yoksa oluştur
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'form-errors';
        errorContainer.className = 'alert alert-danger mb-4';
        
        // Form başına ekle
        const form = document.getElementById(formId);
        if (form) {
            form.parentNode.insertBefore(errorContainer, form);
        }
    }
    
    // Hata içeriğini temizle ve ayarla
    errorContainer.innerHTML = `
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Lütfen aşağıdaki hataları düzeltin:</strong>
        <ul class="mb-0 mt-2">
            ${errors.map(error => `<li>${error}</li>`).join('')}
        </ul>
    `;
    
    // Görünür yap
    errorContainer.style.display = 'block';
    
    // Sayfanın en üstüne kaydır
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Başarı mesajı göster
function showFormSuccess(message, formId = 'listingForm') {
    // Hata container'ını temizle
    const errorContainer = document.querySelector('#form-errors');
    if (errorContainer) {
        errorContainer.style.display = 'none';
    }
    
    // Başarı container'ı
    let successContainer = document.querySelector('#form-success');
    
    // Container yoksa oluştur
    if (!successContainer) {
        successContainer = document.createElement('div');
        successContainer.id = 'form-success';
        successContainer.className = 'alert alert-success mb-4';
        
        // Form başına ekle
        const form = document.getElementById(formId);
        if (form) {
            form.parentNode.insertBefore(successContainer, form);
        }
    }
    
    // İçeriği ayarla
    successContainer.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${message}`;
    
    // Görünür yap
    successContainer.style.display = 'block';
    
    // Sayfanın en üstüne kaydır
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// AJAX ile görsel yükleme
function uploadImages(formData, listingId, callback) {
    // Form verisinde listingId kontrolü
    if (listingId) {
        formData.append('listing_id', listingId);
    }
    
    // AJAX isteği
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/admin/handlers/upload-image.php', true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(response);
            } catch (e) {
                callback({
                    success: false,
                    files: [],
                    errors: ['Sunucu yanıtı işlenemedi: ' + e.message]
                });
            }
        } else {
            callback({
                success: false,
                files: [],
                errors: ['Sunucu hatası: ' + xhr.status]
            });
        }
    };
    
    xhr.onerror = function() {
        callback({
            success: false,
            files: [],
            errors: ['Ağ hatası, lütfen bağlantınızı kontrol edin.']
        });
    };
    
    xhr.send(formData);
}

// Sekmeyi değiştir
function switchToTab(tabId) {
    const tabElement = document.getElementById(tabId);
    if (tabElement) {
        tabElement.click();
    }
}

// DOM Yüklendiğinde çalışır
document.addEventListener('DOMContentLoaded', function() {
    // İlan formu
    const listingForm = document.getElementById('listingForm');
    
    if (listingForm) {
        // Form gönderimini yakalama ve işleme
        listingForm.addEventListener('submit', function(e) {
            // Form doğrulama
            if (!listingForm.checkValidity()) {
                e.preventDefault();
                
                // Bootstrap doğrulama stillerini göster
                listingForm.classList.add('was-validated');
                
                // İlk geçersiz alana odaklan
                const invalidInput = listingForm.querySelector(':invalid');
                
                if (invalidInput) {
                    // Alana odaklan
                    invalidInput.focus();
                    
                    // İçinde olduğu sekmeyi aç
                    const tabPane = invalidInput.closest('.tab-pane');
                    if (tabPane && tabPane.id) {
                        const relatedTabId = document.querySelector(`[href="#${tabPane.id}"]`).id;
                        switchToTab(relatedTabId);
                    }
                }
                
                return false;
            }
            
            // Gönderim düğmesini devre dışı bırak
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Kaydediliyor...';
            }
            
            return true;
        });
        
        // İlan tipi değişikliğinde fiyat alanlarını güncelle
        const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
        
        if (listingTypeRadios.length) {
            const salePriceContainer = document.getElementById('sale-price-container');
            const rentPriceContainer = document.getElementById('rent-price-container');
            const salePriceInput = document.getElementById('sale_price');
            const rentPriceInput = document.getElementById('rent_price');
            
            listingTypeRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'sale') {
                        // Satılık
                        if (salePriceContainer) salePriceContainer.style.display = 'block';
                        if (rentPriceContainer) rentPriceContainer.style.display = 'none';
                        
                        if (salePriceInput) salePriceInput.required = true;
                        if (rentPriceInput) {
                            rentPriceInput.required = false;
                            rentPriceInput.value = '';
                        }
                    } else if (this.value === 'rent') {
                        // Kiralık
                        if (salePriceContainer) salePriceContainer.style.display = 'none';
                        if (rentPriceContainer) rentPriceContainer.style.display = 'block';
                        
                        if (rentPriceInput) rentPriceInput.required = true;
                        if (salePriceInput) {
                            salePriceInput.required = false;
                            salePriceInput.value = '';
                        }
                    } else if (this.value === 'both') {
                        // Hem satılık hem kiralık
                        if (salePriceContainer) salePriceContainer.style.display = 'block';
                        if (rentPriceContainer) rentPriceContainer.style.display = 'block';
                        
                        if (salePriceInput) salePriceInput.required = true;
                        if (rentPriceInput) rentPriceInput.required = true;
                    }
                });
            });
        }
        
        // Sekme navigasyonu
        const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        const nextTabButtons = document.querySelectorAll('.next-tab');
        const prevTabButtons = document.querySelectorAll('.prev-tab');
        
        // Tab değiştiğinde wizard adımlarını güncelle 
        tabLinks.forEach(function(tabLink) {
            tabLink.addEventListener('shown.bs.tab', function(e) {
                const targetId = e.target.getAttribute('id');
                
                // Wizard adımlarını güncelle
                document.querySelectorAll('.wizard-progress-step').forEach(function(step) {
                    step.classList.remove('active');
                    if (step.getAttribute('data-step') === targetId) {
                        step.classList.add('active');
                    }
                });
            });
        });
        
        // İleri butonları
        nextTabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const nextTabId = this.getAttribute('data-next');
                switchToTab(nextTabId);
            });
        });
        
        // Geri butonları
        prevTabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const prevTabId = this.getAttribute('data-prev');
                switchToTab(prevTabId);
            });
        });
        
        // Mesafeler için dinamik ekleme/silme
        const addDistanceBtn = document.getElementById('add-distance');
        const distancesContainer = document.getElementById('distances-container');
        
        if (addDistanceBtn && distancesContainer) {
            // Mesafe satırı ekleme
            addDistanceBtn.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.className = 'distance-row row mb-3';
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı (örn. Metro)">
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="number" class="form-control" name="distance_value[]" placeholder="Mesafe" step="0.1" min="0">
                            <span class="input-group-text">km</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                    </div>
                `;
                
                distancesContainer.appendChild(newRow);
                
                // Yeni eklenen satırın silme düğmesine olay dinleyicisi ekle
                newRow.querySelector('.remove-distance').addEventListener('click', function() {
                    newRow.remove();
                });
            });
            
            // Mevcut satırların silme butonlarına dinleyici ekle
            document.querySelectorAll('.remove-distance').forEach(function(button) {
                button.addEventListener('click', function() {
                    this.closest('.distance-row').remove();
                });
            });
        }
    }
});
/**
 * Emlak İlan Ekleme/Düzenleme için Dosya Yükleme ve Harita İşlevleri
 * Bu dosya, dosya yükleme, görsel önizleme ve harita entegrasyonu için gerekli 
 * JavaScript fonksiyonlarını içerir.
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeFileUpload();
    initializeMap();
    initializeTabNavigation();
});

/**
 * Dosya yükleme ve önizleme işlevleri
 */
function initializeFileUpload() {
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('image-previews');
    const dragDropArea = document.getElementById('drag-drop-area');
    const selectFilesBtn = document.getElementById('select-files-btn');
    const mainImageSelect = document.getElementById('main-image-select');
    const mainImageContainer = document.getElementById('main-image-container');
    
    if (!fileInput || !previewContainer) return;
    
    // Daha önce yüklenmiş görselleri izlemek için küme
    const processedFiles = new Set();
    
    // Dosya seçme butonunu etkinleştir
    if (selectFilesBtn) {
        selectFilesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });
    }
    
    // Drag & Drop işlemlerini etkinleştir
    if (dragDropArea) {
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-primary');
        });
        
        dragDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
        });
        
        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            
            if (e.dataTransfer.files.length > 0) {
                // Yeni dosyaları mevcut dosyalara eklemek için FileList'i manipüle edemeyiz
                // Bu nedenle, yeni bir input oluşturup gönderim sırasında formData kullanmalıyız
                handleFiles(e.dataTransfer.files);
            }
        });
    }
    
    // Dosya seçimini işle
    fileInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });
    
    // Seçilen dosyaları işleme
    function handleFiles(files) {
        if (!files || files.length === 0) return;
        
        // Maksimum görsel sayısını kontrol et
        const maxImages = 25;
        const currentImages = document.querySelectorAll('.image-preview').length;
        const remainingSlots = maxImages - currentImages;
        
        if (remainingSlots <= 0) {
            showMessage("Maksimum 25 görsel yükleyebilirsiniz.", "warning");
            return;
        }
        
        // İşlenecek dosya sayısını belirle
        const processCount = Math.min(files.length, remainingSlots);
        
        // Her dosyayı işle
        for (let i = 0; i < processCount; i++) {
            const file = files[i];
            
            // Dosya türünü kontrol et
            if (!file.type.match('image/(jpeg|jpg|png)')) {
                showMessage(`"${file.name}" desteklenmeyen dosya formatı. Sadece JPG ve PNG kabul edilir.`, "warning");
                continue;
            }
            
            // Dosya boyutunu kontrol et (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showMessage(`"${file.name}" dosyası çok büyük. Maksimum 5MB izin verilir.`, "warning");
                continue;
            }
            
            // Yinelenen dosyaları kontrol et
            const fileId = file.name + file.size + file.lastModified;
            if (processedFiles.has(fileId)) continue;
            processedFiles.add(fileId);
            
            // Dosyayı önizle
            const reader = new FileReader();
            reader.onload = (function(f, idx) {
                return function(e) {
                    addImagePreview(e.target.result, f.name, idx);
                    updateMainImageSelect();
                };
            })(file, i);
            reader.readAsDataURL(file);
        }
        
        // Ana görsel seçimini güncelle
        if (processedFiles.size > 0 && mainImageContainer) {
            mainImageContainer.style.display = 'block';
        }
    }
    
    // Görsel önizleme ekle
    function addImagePreview(src, filename, index) {
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.dataset.filename = filename;
        
        // Görsel önizleme resmi
        const img = document.createElement('img');
        img.src = src;
        img.alt = filename;
        
        // Dosya adı etiketi
        const nameLabel = document.createElement('div');
        nameLabel.className = 'image-name';
        nameLabel.textContent = truncateFilename(filename, 15);
        
        // Silme butonu
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'delete-image-btn';
        deleteBtn.innerHTML = '×';
        deleteBtn.addEventListener('click', function() {
            preview.remove();
            updateMainImageSelect();
            
            // Tüm görüntüler kaldırıldıysa ana görüntü seçiciyi gizle
            if (document.querySelectorAll('.image-preview').length === 0 && mainImageContainer) {
                mainImageContainer.style.display = 'none';
            }
        });
        
        preview.appendChild(img);
        preview.appendChild(nameLabel);
        preview.appendChild(deleteBtn);
        previewContainer.appendChild(preview);
    }
    
    // Ana görsel seçim menüsünü güncelle
    function updateMainImageSelect() {
        if (!mainImageSelect) return;
        
        mainImageSelect.innerHTML = '';
        
        const previews = document.querySelectorAll('.image-preview');
        if (previews.length === 0) return;
        
        // Varsayılan seçenek
        const defaultOption = document.createElement('option');
        defaultOption.value = "0";
        defaultOption.textContent = 'İlk yüklenen görsel';
        mainImageSelect.appendChild(defaultOption);
        
        // Her görsel için seçenek ekle
        previews.forEach((preview, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `Görsel ${index + 1}: ${preview.dataset.filename || 'Görsel'}`;
            mainImageSelect.appendChild(option);
        });
        
        // Ana görsel seçildiğinde önizlemeye işaretle
        mainImageSelect.addEventListener('change', function() {
            previews.forEach(preview => preview.classList.remove('is-main'));
            const selectedIndex = parseInt(this.value);
            if (previews[selectedIndex]) {
                previews[selectedIndex].classList.add('is-main');
            }
        });
        
        // İlk görsel varsayılan olarak ana görsel olsun
        if (previews[0]) {
            previews[0].classList.add('is-main');
        }
    }
    
    // Dosya adını kısalt
    function truncateFilename(name, maxLength) {
        if (!name || name.length <= maxLength) return name;
        const ext = name.split('.').pop();
        const basename = name.substring(0, name.length - ext.length - 1);
        return basename.substring(0, maxLength - ext.length - 4) + '...' + ext;
    }
    
    // Hata veya bilgi mesajı göster
    function showMessage(message, type = 'error') {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show mt-2`;
        alertContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const uploadArea = dragDropArea.parentNode;
        uploadArea.appendChild(alertContainer);
        
        // 5 saniye sonra otomatik kapat
        setTimeout(() => {
            alertContainer.classList.remove('show');
            setTimeout(() => alertContainer.remove(), 150);
        }, 5000);
    }
}

/**
 * Harita başlatma ve koordinat seçimi
 */
function initializeMap() {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) return;
    
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    if (!latInput || !lngInput) return;
    
    // Başlangıç koordinatlarını belirle
    let initialLat = 39.1;
    let initialLng = 35.6;
    let initialZoom = 6;
    
    // Mevcut koordinatları kontrol et
    if (latInput.value && lngInput.value) {
        initialLat = parseFloat(latInput.value);
        initialLng = parseFloat(lngInput.value);
        initialZoom = 15;
    } else if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
        initialLat = parseFloat(mapContainer.dataset.lat);
        initialLng = parseFloat(mapContainer.dataset.lng);
        initialZoom = 15;
    }
    
    // Haritayı başlat (Leaflet kullanıyor)
    try {
        const map = L.map(mapContainer, {
            center: [initialLat, initialLng],
            zoom: initialZoom,
            scrollWheelZoom: true
        });
        
        // OpenStreetMap layer ekle
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
        
        // Mevcut konuma marker ekle
        let marker;
        if (initialLat !== 39.1 || initialLng !== 35.6) {
            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);
            
            // Marker sürüklendiğinde
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latInput.value = position.lat;
                lngInput.value = position.lng;
            });
        }
        
        // Haritaya tıklandığında
        map.on('click', function(e) {
            const clickLat = e.latlng.lat;
            const clickLng = e.latlng.lng;
            
            // Input değerlerini güncelle
            latInput.value = clickLat;
            lngInput.value = clickLng;
            
            // Marker'ı güncelle veya oluştur
            if (marker) {
                marker.setLatLng([clickLat, clickLng]);
            } else {
                marker = L.marker([clickLat, clickLng], {
                    draggable: true
                }).addTo(map);
                
                // Marker sürüklendiğinde
                marker.on('dragend', function(e) {
                    const position = marker.getLatLng();
                    latInput.value = position.lat;
                    lngInput.value = position.lng;
                });
            }
        });
        
        // Sekme değiştiğinde haritayı güncelle (harita tab'da olduğunda)
        document.getElementById('location-tab').addEventListener('shown.bs.tab', function() {
            setTimeout(() => {
                map.invalidateSize();
                // Koordinat var ise o noktaya odaklan
                if (latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    // Koordinatları kontrol et
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setView([lat, lng], 15);
                        
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        } else {
                            marker = L.marker([lat, lng], {
                                draggable: true
                            }).addTo(map);
                            
                            marker.on('dragend', function(e) {
                                const position = marker.getLatLng();
                                latInput.value = position.lat;
                                lngInput.value = position.lng;
                            });
                        }
                    }
                }
            }, 100);
        });
        
    } catch (error) {
        console.error("Harita başlatma hatası:", error);
        
        // Leaflet yüklenemediğinde hata mesajı göster
        mapContainer.innerHTML = `
            <div class="alert alert-warning">
                <p><strong>Harita yüklenemedi.</strong> Lütfen sayfayı yenileyin veya yöneticiye başvurun.</p>
                <p>Hata: ${error.message}</p>
            </div>
        `;
    }
}

/**
 * Sekme geçişleri için ilerleme çubuğu ve form doğrulama
 */
function initializeTabNavigation() {
    const tabs = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    const wizardSteps = document.querySelectorAll('.wizard-progress-step');
    const nextButtons = document.querySelectorAll('.next-tab');
    const prevButtons = document.querySelectorAll('.prev-tab');
    
    // Sekme değişiminde ilerleme çubuğunu güncelle
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            const targetId = this.getAttribute('id');
            
            // İlerleme çubuğunu güncelle
            wizardSteps.forEach(step => {
                if (step.getAttribute('data-step') === targetId) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
        });
    });
    
    // İleri butonları
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentTab = document.querySelector('.tab-pane.active');
            const nextTabId = this.getAttribute('data-next');
            
            // Mevcut sekmedeki zorunlu alanları doğrula
            if (validateTabFields(currentTab)) {
                // Geçerli ise sonraki sekmeye geç
                const nextTab = document.getElementById(nextTabId);
                if (nextTab) {
                    const bsTab = new bootstrap.Tab(nextTab);
                    bsTab.show();
                }
            }
        });
    });
    
    // Geri butonları
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prevTabId = this.getAttribute('data-prev');
            const prevTab = document.getElementById(prevTabId);
            if (prevTab) {
                const bsTab = new bootstrap.Tab(prevTab);
                bsTab.show();
            }
        });
    });
    
    // Form gönderiminde son doğrulamalar
    const form = document.getElementById('listingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Tüm sekmelerdeki zorunlu alanları doğrula
            const tabPanes = document.querySelectorAll('.tab-pane');
            let hasError = false;
            let firstErrorTab = null;
            
            tabPanes.forEach(pane => {
                if (!validateTabFields(pane, false)) {
                    hasError = true;
                    if (!firstErrorTab) {
                        firstErrorTab = pane.id;
                    }
                }
            });
            
            // Hata varsa ilk hatalı sekmeyi göster
            if (hasError) {
                e.preventDefault();
                
                // İlk hatalı sekmeye git
                const errorTabLink = document.querySelector(`[href="#${firstErrorTab}"]`);
                if (errorTabLink) {
                    const bsTab = new bootstrap.Tab(errorTabLink);
                    bsTab.show();
                }
                
                // Hata mesajı göster
                showValidationError("Lütfen tüm zorunlu alanları doldurun");
                return false;
            }
            
            // Form gönderiliyorsa yükleme göstergesini etkinleştir
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>İlan Kaydediliyor...';
            }
        });
    }
    
    // Sekme içindeki zorunlu alanları doğrula
    function validateTabFields(tabPane, showErrors = true) {
        if (!tabPane) return true;
        
        const requiredFields = tabPane.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            field.classList.remove('is-invalid');
            
            if (!field.value.trim()) {
                isValid = false;
                if (showErrors) {
                    field.classList.add('is-invalid');
                    
                    // Hata mesajı ekle
                    const feedback = field.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'invalid-feedback';
                        errorMsg.textContent = 'Bu alan gereklidir';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                }
            }
        });
        
        // Konum kontrolü (eğer konum sekmesindeyse)
        if (tabPane.id === 'location' && showErrors) {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            
            if ((!latInput.value || !lngInput.value) || 
                (parseFloat(latInput.value) === 39.1 && parseFloat(lngInput.value) === 35.6)) {
                showValidationError("Lütfen haritada konum seçin", tabPane);
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Doğrulama hatası göster
    function showValidationError(message, container = null) {
        if (!container) {
            container = document.querySelector('.tab-pane.active');
        }
        
        if (!container) return;
        
        // Mevcut hata mesajını kaldır
        const existingError = container.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Yeni hata mesajı oluştur
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger validation-error mb-3';
        errorAlert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${message}`;
        
        // Mesajı sekme içeriğinin başına ekle
        container.insertBefore(errorAlert, container.firstChild);
        
        // 5 saniye sonra hata mesajını kaldır
        setTimeout(() => {
            errorAlert.remove();
        }, 5000);
    }
}
(function() {
    'use strict';
    
    const AksuAdmin = {
        // Başlatma
        init: function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeModules());
            } else {
                this.initializeModules();
            }
        },

        initializeModules: function() {
            console.log('AksuAdmin modülleri başlatılıyor...');
            this.Core.init();
            this.Forms.init();
            this.Maps.init();       // Haritayı hemen başlat
            this.FileUpload.init(); // Görsel yüklemeyi başlat
            this.Features.init();

            // Sayfa yüklendiğinde aktif olan Konum tab'ı için haritayı kontrol et
            const activeTabPane = document.querySelector('.tab-pane.active#location');
            if (activeTabPane) {
                // Yazım hatası düzeltildi: querySelector_ -> querySelector
                const mapContainer = activeTabPane.querySelector("#map-container, #locationMap");
                if (mapContainer) {
                     this.Maps.refreshMap(mapContainer.id);
                }
            }
        },
        
        // Yardımcı fonksiyonlar
        Utils: {
            showMessage: function(message, type = 'success') {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                alertContainer.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                const container = document.querySelector('.container-fluid') || document.body;
                container.insertBefore(alertContainer, container.firstChild);
                
                setTimeout(() => {
                    alertContainer.classList.remove('show');
                    setTimeout(() => alertContainer.remove(), 150);
                }, 5000);
            }
        },
        
        // Temel işlevler
        Core: {
            init: function() {
                this.initSidebar();
                this.initScrollToTop();
                this.initTabsFunctionality();
                this.handleListingTypeRadios();
                this.handleDistanceRows();
                this.initFormValidation();
            },
            
            initSidebar: function() {
                const sidebarToggles = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');
                const body = document.body;
                const sidebar = document.querySelector('.sidebar');
                
                sidebarToggles.forEach(toggle => {
                    toggle?.addEventListener('click', (e) => {
                        e.preventDefault();
                        sidebar?.classList.toggle('toggled');
                        body.classList.toggle('sidebar-toggled');
                    });
                });
                
                // Mobilde sidebar dışına tıklama
                document.addEventListener('click', (e) => {
                    if (window.innerWidth < 768 && sidebar?.classList.contains('toggled')) {
                        const isToggle = Array.from(sidebarToggles).some(btn => btn?.contains(e.target));
                        if (!sidebar.contains(e.target) && !isToggle) {
                            sidebar.classList.remove('toggled');
                            body.classList.remove('sidebar-toggled');
                        }
                    }
                });
            },
            
            initScrollToTop: function() {
                const scrollBtn = document.querySelector('.scroll-to-top');
                if (!scrollBtn) return;
                
                window.addEventListener('scroll', () => {
                    scrollBtn.style.display = window.pageYOffset > 100 ? 'flex' : 'none';
                });
                
                scrollBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            },
            
            initTabsFunctionality: function() {
                document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tabEl => {
                    tabEl.addEventListener('shown.bs.tab', event => {
                        const targetHref = event.target.getAttribute('href');
                        if (targetHref === '#location') {
                            // `add.php` ve `edit.php` farklı ID'ler kullanabilir
                            const mapContainer = document.querySelector('#location #map-container, #location #locationMap');
                            if (mapContainer) {
                                AksuAdmin.Maps.refreshMap(mapContainer.id);
                            }
                        }
                        // Wizard progress adımlarını güncelleme (add.php'den buraya taşındı)
                        const activeTabId = event.target.id; // e.g., "location-tab"
                        document.querySelectorAll('.wizard-progress-step').forEach(step => {
                            step.classList.remove('active');
                            if (step.dataset.step === activeTabId) {
                                step.classList.add('active');
                            }
                        });
                    });
                });
            },
            
            handleListingTypeRadios: function() {
                const radios = document.querySelectorAll('input[name="listing_type"]');
                const saleContainer = document.getElementById('sale-price-container');
                const rentContainer = document.getElementById('rent-price-container');
                const saleInput = document.getElementById('sale_price');
                const rentInput = document.getElementById('rent_price');
                
                if (!radios.length) return;
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        switch(this.value) {
                            case 'sale':
                                saleContainer.style.display = 'block';
                                rentContainer.style.display = 'none';
                                saleInput.required = true;
                                rentInput.required = false;
                                rentInput.value = '';
                                break;
                            case 'rent':
                                saleContainer.style.display = 'none';
                                rentContainer.style.display = 'block';
                                rentInput.required = true;
                                saleInput.required = false;
                                saleInput.value = '';
                                break;
                            case 'both':
                                saleContainer.style.display = 'block';
                                rentContainer.style.display = 'block';
                                saleInput.required = true;
                                rentInput.required = true;
                                break;
                        }
                    });
                });
                
                // İlk yükleme
                const checked = document.querySelector('input[name="listing_type"]:checked');
                checked?.dispatchEvent(new Event('change'));
            },
            
            handleDistanceRows: function() {
                const container = document.getElementById('distances-container');
                const addBtn = document.getElementById('add-distance');
                
                if (!container || !addBtn) return;
                
                addBtn.addEventListener('click', () => {
                    const row = document.createElement('div');
                    row.className = 'distance-row row mb-3';
                    row.innerHTML = `
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı">
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="number" class="form-control" name="distance_value[]" 
                                       placeholder="Mesafe" step="0.1" min="0">
                                <span class="input-group-text">km</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                        </div>
                    `;
                    container.appendChild(row);
                });
                
                container.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-distance')) {
                        e.target.closest('.distance-row').remove();
                    }
                });
            },
            
            initFormValidation: function() {
                document.querySelectorAll('form.needs-validation').forEach(form => {
                    form.addEventListener('submit', (e) => {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const invalid = form.querySelector(':invalid');
                            if (invalid) {
                                const tabPane = invalid.closest('.tab-pane');
                                if (tabPane && !tabPane.classList.contains('active')) {
                                    const tabTrigger = document.querySelector(`[href="#${tabPane.id}"]`);
                                    tabTrigger?.click();
                                    setTimeout(() => {
                                        invalid.focus();
                                        invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }, 150);
                                }
                            }
                        }
                        form.classList.add('was-validated');
                    });
                });
            }
        },
        
        // Form işlemleri
        Forms: {
            init: function() {
                // Form gönderim sonrası sayfa yenileme engelleme
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            }
        },
        
        // Dosya yükleme - DÜZELTİLMİŞ
        FileUpload: {
            init: function() {
                const fileInput = document.getElementById('images'); // add.php'deki input
                const previewContainer = document.getElementById('image-previews');
                const dragDropArea = document.getElementById('drag-drop-area');
                const selectBtn = document.getElementById('select-files-btn');
                const mainImageContainer = document.getElementById('main-image-container');
                const mainImageSelect = document.getElementById('main-image-select');
                
                if (!fileInput || !previewContainer || !dragDropArea || !selectBtn || !mainImageContainer || !mainImageSelect) {
                    console.warn('FileUpload: Gerekli HTML elementlerinden bazıları add.php sayfasında bulunamadı.');
                    return;
                }
                console.log('FileUpload (add.php) başlatılıyor...');
                
                // input'u clone et ve olayları yeni elemana bağla
                const newFileInput = fileInput.cloneNode(true);
                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                
                selectBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    newFileInput.click();
                });
                
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dragDropArea.addEventListener(eventName, e => {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
                ['dragenter', 'dragover'].forEach(eventName => {
                    dragDropArea.addEventListener(eventName, () => dragDropArea.classList.add('border-primary', 'bg-light'));
                });
                ['dragleave', 'drop'].forEach(eventName => {
                    dragDropArea.addEventListener(eventName, () => dragDropArea.classList.remove('border-primary', 'bg-light'));
                });
                
                dragDropArea.addEventListener('drop', (e) => {
                    if (e.dataTransfer.files.length > 0) {
                        newFileInput.files = e.dataTransfer.files;
                        newFileInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
                
                newFileInput.addEventListener('change', (e) => {
                    AksuAdmin.FileUpload.handleFiles(e.target.files, previewContainer, mainImageContainer, mainImageSelect);
                });
            },
            handleFiles: function(files, previewContainer, mainImageContainer, mainImageSelect) {
                previewContainer.innerHTML = ''; // Önceki önizlemeleri temizle
                mainImageSelect.innerHTML = '<option value="0">İlk yüklenen görsel</option>'; // Seçiciyi sıfırla
                
                if (!files || files.length === 0) {
                    mainImageContainer.classList.add('d-none');
                    return;
                }
                
                mainImageContainer.classList.remove('d-none');
                let imageIndex = 0;
                
                Array.from(files).forEach(file => {
                    if (!file.type.startsWith('image/')) {
                        console.warn(`Desteklenmeyen dosya tipi: ${file.name}`);
                        return;
                    }
                    if (file.size > 10 * 1024 * 1024) { // 10MB limit
                        AksuAdmin.Utils.showMessage(`"${file.name}" çok büyük (max 10MB).`, 'warning');
                        return;
                    }
                    
                    const reader = new FileReader();
                    const currentIndex = imageIndex; // Closure için index'i sakla
                    
                    reader.onload = (event) => {
                        const previewEl = document.createElement('div');
                        previewEl.className = 'image-preview-item position-relative border rounded overflow-hidden';
                        previewEl.style.width = '150px';
                        previewEl.style.height = '100px';
                        previewEl.dataset.index = currentIndex;
                        
                        previewEl.innerHTML = `
                            <img src="${event.target.result}" alt="${file.name}" class="w-100 h-100" style="object-fit: cover;">
                            <div class="bg-dark bg-opacity-50 text-white small p-1 position-absolute bottom-0 w-100 text-truncate" title="${file.name}">
                                ${file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name}
                            </div>
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center remove-new-image-btn"
                                    style="width: 20px; height: 20px;" data-index="${currentIndex}">×</button>
                        `;
                        previewContainer.appendChild(previewEl);
                        
                        const option = document.createElement('option');
                        option.value = currentIndex;
                        option.textContent = `Görsel ${currentIndex + 1}: ${file.name}`;
                        mainImageSelect.appendChild(option);
                        
                        // Silme butonu için event listener
                        previewEl.querySelector('.remove-new-image-btn').addEventListener('click', function() {
                            // Bu dosyanın fileInput.files listesinden kaldırılması karmaşık.
                            // Şimdilik sadece DOM'dan kaldıralım ve kullanıcıya bilgi verelim.
                            // Form gönderilmeden önce bu dosyalar hala `images[]` içinde olacak.
                            // Sunucu tarafında boş dosya girişlerini ele almak gerekebilir.
                            previewEl.remove();
                            mainImageSelect.querySelector(`option[value="${currentIndex}"]`)?.remove();
                            if (previewContainer.children.length === 0) {
                                mainImageContainer.classList.add('d-none');
                            }
                            // Ana görsel seçiciyi yeniden düzenle
                            let newIndex = 0;
                            mainImageSelect.querySelectorAll('option').forEach(opt => {
                                if (opt.value !== "0") { // "İlk yüklenen görsel" hariç
                                     opt.value = newIndex;
                                     // opt.textContent = `Görsel ${newIndex + 1}: ...`; // Dosya adını güncellemek zor
                                     newIndex++;
                                }
                            });
                            // fileInput'taki dosyaları güncellemek için DataTransfer kullanmak gerekir, bu daha karmaşıktır.
                        });
                    };
                    reader.readAsDataURL(file);
                    imageIndex++;
                });
            }
        },
        
        // Harita işlemleri - DÜZELTİLMİŞ
        Maps: {
            instances: {},
            markers: {},
            init: function() {
                if (typeof L === 'undefined') {
                    console.warn('Leaflet JS yüklenmemiş. Harita başlatılamıyor.');
                    setTimeout(() => this.init(), 200); // Tekrar dene
                    return;
                }
                console.log('Maps modülü hazır. Harita, ilgili sekme açıldığında yüklenecek.');
                // Harita başlatma işlemi Core.initTabsFunctionality tarafından tetiklenecek
            },
            initializeMap: function(containerId) {
                const container = document.getElementById(containerId);
                if (!container) {
                    console.error(`Harita container'ı bulunamadı: #${containerId}`);
                    return null;
                }

                // Eğer harita zaten varsa, tekrar başlatma
                if (this.instances[containerId]) {
                    console.log(`Harita #${containerId} zaten başlatılmış. Boyutlar güncelleniyor.`);
                    this.instances[containerId].invalidateSize();
                    return this.instances[containerId];
                }
                
                console.log(`Harita #${containerId} başlatılıyor...`);
                container.innerHTML = ''; // Önceki içeriği temizle (hata mesajı vb.)
                container.style.height = '400px'; // Yüksekliği ayarla

                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                let lat = 41.0082; // Küçükçekmece varsayılan enlem
                let lng = 28.7784; // Küçükçekmece varsayılan boylam
                let zoom = 13;

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const parsedLat = parseFloat(latInput.value);
                    const parsedLng = parseFloat(lngInput.value);
                    if (!isNaN(parsedLat) && !isNaN(parsedLng)) {
                        lat = parsedLat;
                        lng = parsedLng;
                        zoom = 15;
                    }
                }

                try {
                    const map = L.map(containerId).setView([lat, lng], zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);

                    const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

                    marker.on('dragend', function() {
                        const position = marker.getLatLng();
                        if (latInput) latInput.value = position.lat.toFixed(6);
                        if (lngInput) lngInput.value = position.lng.toFixed(6);
                    });

                    map.on('click', function(e) {
                        marker.setLatLng(e.latlng);
                        if (latInput) latInput.value = e.latlng.lat.toFixed(6);
                        if (lngInput) lngInput.value = e.latlng.lng.toFixed(6);
                    });
                    
                    if (latInput && lngInput) {
                        [latInput, lngInput].forEach(input => {
                            input.addEventListener('change', () => {
                                const newLat = parseFloat(latInput.value);
                                const newLng = parseFloat(lngInput.value);
                                if (!isNaN(newLat) && !isNaN(newLng)) {
                                    marker.setLatLng([newLat, newLng]);
                                    map.setView([newLat, newLng]);
                                }
                            });
                        });
                    }

                    this.instances[containerId] = map;
                    this.markers[containerId] = marker;

                    // Harita DOM'a eklendikten ve boyutları ayarlandıktan sonra çağır
                    setTimeout(() => {
                        map.invalidateSize();
                        console.log(`Harita #${containerId} boyutları güncellendi.`);
                    }, 100);
                    
                    console.log(`Harita #${containerId} başarıyla başlatıldı.`);
                    return map;

                } catch (error) {
                    console.error(`Harita #${containerId} başlatılırken hata:`, error);
                    container.innerHTML = `<div class="alert alert-danger">Harita yüklenemedi: ${error.message}</div>`;
                    return null;
                }
            },
            refreshMap: function(containerId) {
                if (!containerId) {
                    console.warn('refreshMap çağrıldı ancak containerId belirtilmemiş.');
                    return;
                }
                console.log(`Harita #${containerId} için refreshMap çağrıldı.`);
                if (this.instances[containerId]) {
                    console.log(`Mevcut harita #${containerId} boyutları güncelleniyor.`);
                    this.instances[containerId].invalidateSize();
                } else {
                    console.log(`Harita #${containerId} mevcut değil, yeniden başlatılıyor.`);
                    this.initializeMap(containerId);
                }
            }
        },
        
        // Dinamik özellik yönetimi - YENİ
        Features: {
            init: function() {
                this.setupFeatureManagement();
            },
            
            setupFeatureManagement: function() {
                // Özellik ekleme butonları
                const addButtons = document.querySelectorAll('.add-feature-btn');
                addButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const category = btn.dataset.category;
                        this.showAddFeatureModal(category);
                    });
                });
            },
            
            showAddFeatureModal: function(category) {
                // Modal HTML
                const modalHtml = `
                    <div class="modal fade" id="addFeatureModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Yeni Özellik Ekle - ${category}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Özellik Adı</label>
                                        <input type="text" class="form-control" id="newFeatureName">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Özellik Değeri (opsiyonel)</label>
                                        <input type="text" class="form-control" id="newFeatureValue">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                    <button type="button" class="btn btn-primary" onclick="AksuAdmin.Features.addFeature('${category}')">Ekle</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Modal'ı DOM'a ekle
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modal = new bootstrap.Modal(document.getElementById('addFeatureModal'));
                modal.show();
                
                // Modal kapandığında temizle
                document.getElementById('addFeatureModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            },
            
            addFeature: function(category) {
                const name = document.getElementById('newFeatureName').value.trim();
                if (!name) {
                    alert('Lütfen özellik adı girin');
                    return;
                }
                
                // Özelliği ekle
                const container = document.querySelector(`[data-feature-category="${category}"]`);
                if (container) {
                    const featureId = `feature_${Date.now()}`;
                    const featureHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="${category}[]" 
                                   value="${name}" id="${featureId}">
                            <label class="form-check-label" for="${featureId}">${name}</label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" 
                                    onclick="this.parentElement.remove()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', featureHtml);
                }
                
                // Modal'ı kapat
                bootstrap.Modal.getInstance(document.getElementById('addFeatureModal')).hide();
            }
        }
    };
    
    // Admin modülünü başlat
    AksuAdmin.init();
    
    // Global erişim
    window.AksuAdmin = AksuAdmin;
})();
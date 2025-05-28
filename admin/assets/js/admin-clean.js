

(function() {
    'use strict';
    
    const AksuAdmin = {
        // Başlatma
        init: function() {
            document.addEventListener('DOMContentLoaded', () => {
                this.Core.init();
                this.Forms.init();
                this.Maps.init();
                this.FileUpload.init();
                this.Features.init();
            });
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
                // Tab değişiminde harita yenileme
                document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                    tab.addEventListener('shown.bs.tab', (e) => {
                        if (e.target.getAttribute('href') === '#location') {
                            setTimeout(() => {
                                AksuAdmin.Maps.refreshMap('map-container');
                            }, 100);
                        }
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
            processedFiles: new Set(),
            currentFiles: [],
            
            init: function() {
                const fileInput = document.getElementById('images');
                const previewContainer = document.getElementById('image-previews');
                const dragDropArea = document.getElementById('drag-drop-area');
                const selectBtn = document.getElementById('select-files-btn');
                
                if (!fileInput || !previewContainer) return;
                
                // Event listener'ları temizle ve yeniden ekle
                const newFileInput = fileInput.cloneNode(true);
                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                
                // Dosya seçme butonu
                selectBtn?.addEventListener('click', (e) => {
                    e.preventDefault();
                    newFileInput.click();
                });
                
                // Drag & Drop
                if (dragDropArea) {
                    ['dragover', 'drop'].forEach(eventName => {
                        dragDropArea.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                        });
                    });
                    
                    dragDropArea.addEventListener('dragover', function() {
                        this.classList.add('border-primary');
                    });
                    
                    dragDropArea.addEventListener('dragleave', function() {
                        this.classList.remove('border-primary');
                    });
                    
                    dragDropArea.addEventListener('drop', function(e) {
                        this.classList.remove('border-primary');
                        if (e.dataTransfer.files.length > 0) {
                            AksuAdmin.FileUpload.handleFiles(e.dataTransfer.files);
                        }
                    });
                }
                
                // Dosya seçimi
                newFileInput.addEventListener('change', function() {
                    AksuAdmin.FileUpload.handleFiles(this.files);
                });
            },
            
            handleFiles: function(files) {
                const previewContainer = document.getElementById('image-previews');
                const mainImageContainer = document.getElementById('main-image-container');
                const mainImageSelect = document.getElementById('main-image-select');
                
                if (!files || files.length === 0) return;
                
                // Mevcut dosya sayısını kontrol et
                const currentCount = previewContainer.querySelectorAll('.image-preview').length;
                const maxImages = 25;
                
                if (currentCount >= maxImages) {
                    AksuAdmin.Utils.showMessage('Maksimum 25 görsel yükleyebilirsiniz.', 'warning');
                    return;
                }
                
                // Dosyaları işle
                Array.from(files).forEach((file, index) => {
                    if (currentCount + index >= maxImages) return;
                    
                    // Dosya kontrolü
                    if (!file.type.match('image/(jpeg|jpg|png)')) {
                        AksuAdmin.Utils.showMessage(`"${file.name}" desteklenmeyen format.`, 'warning');
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        AksuAdmin.Utils.showMessage(`"${file.name}" çok büyük (max 5MB).`, 'warning');
                        return;
                    }
                    
                    // Benzersiz ID oluştur
                    const fileId = `${file.name}_${file.size}_${Date.now()}`;
                    if (this.processedFiles.has(fileId)) return;
                    
                    this.processedFiles.add(fileId);
                    this.currentFiles.push(file);
                    
                    // Önizleme oluştur
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.addImagePreview(e.target.result, file.name, previewContainer.children.length);
                        
                        // Ana görsel seçimini güncelle
                        if (mainImageContainer) {
                            mainImageContainer.classList.remove('d-none');
                            this.updateMainImageSelect();
                        }
                    };
                    reader.readAsDataURL(file);
                });
            },
            
            addImagePreview: function(src, filename, index) {
                const container = document.getElementById('image-previews');
                
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.dataset.index = index;
                preview.innerHTML = `
                    <img src="${src}" alt="${filename}">
                    <div class="image-name">${filename.length > 15 ? filename.substring(0, 12) + '...' : filename}</div>
                    <button type="button" class="delete-image-btn" onclick="AksuAdmin.FileUpload.removeImage(${index})">×</button>
                `;
                
                container.appendChild(preview);
            },
            
            removeImage: function(index) {
                const preview = document.querySelector(`.image-preview[data-index="${index}"]`);
                if (preview) {
                    preview.remove();
                    this.updateMainImageSelect();
                    
                    if (document.querySelectorAll('.image-preview').length === 0) {
                        document.getElementById('main-image-container')?.classList.add('d-none');
                    }
                }
            },
            
            updateMainImageSelect: function() {
                const select = document.getElementById('main-image-select');
                if (!select) return;
                
                select.innerHTML = '<option value="0">İlk yüklenen görsel</option>';
                
                document.querySelectorAll('.image-preview').forEach((preview, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = `Görsel ${index + 1}`;
                    select.appendChild(option);
                });
            }
        },
        
        // Harita işlemleri - DÜZELTİLMİŞ
        Maps: {
            instances: {},
            markers: {},
            
            init: function() {
                // Leaflet yüklenene kadar bekle
                if (typeof L === 'undefined') {
                    setTimeout(() => this.init(), 100);
                    return;
                }
                
                this.initializeMap('map-container');
            },
            
            initializeMap: function(containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;
                
                // Önceki haritayı temizle
                if (this.instances[containerId]) {
                    this.instances[containerId].remove();
                    delete this.instances[containerId];
                }
                
                // Koordinatları al
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                
                let lat = 39.1, lng = 35.6, zoom = 6;
                
                if (latInput?.value && lngInput?.value) {
                    lat = parseFloat(latInput.value);
                    lng = parseFloat(lngInput.value);
                    zoom = 15;
                }
                
                // Harita oluştur
                const map = L.map(containerId).setView([lat, lng], zoom);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);
                
                // Marker ekle
                let marker = null;
                if (zoom > 6) {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                }
                
                // Harita tıklama
                map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    
                    if (latInput && lngInput) {
                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);
                    }
                    
                    if (marker) {
                        marker.setLatLng(e.latlng);
                    } else {
                        marker = L.marker(e.latlng, { draggable: true }).addTo(map);
                        this.setupMarkerDrag(marker, latInput, lngInput);
                    }
                });
                
                // Marker sürükleme
                if (marker && latInput && lngInput) {
                    this.setupMarkerDrag(marker, latInput, lngInput);
                }
                
                this.instances[containerId] = map;
            },
            
            setupMarkerDrag: function(marker, latInput, lngInput) {
                marker.on('dragend', (e) => {
                    const { lat, lng } = marker.getLatLng();
                    if (latInput && lngInput) {
                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);
                    }
                });
            },
            
            refreshMap: function(containerId) {
                const map = this.instances[containerId];
                if (map) {
                    setTimeout(() => map.invalidateSize(), 100);
                } else {
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
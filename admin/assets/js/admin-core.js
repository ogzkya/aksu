/**
 * Aksu Emlak - Admin Panel JavaScript Fonksiyonları
 * Bu dosya admin.js, form-upload.js ve map-integration.js dosyalarını birleştirerek
 * daha verimli bir kod yapısı oluşturur.
 */

// Tüm Admin Panel modüllerini tek bir IIFE içinde birleştiriyoruz
(function() {
    'use strict';
    
    // Modül nesnesi - Alt modüllerimizi burada saklayacağız
    const AksuAdmin = {
        // DOM'un hazır olup olmadığını kontrol eder ve hazırsa ilgili fonksiyonları çalıştırır
        init: function() {
            document.addEventListener('DOMContentLoaded', function() {
                // Alt modülleri başlat
                AksuAdmin.Core.init();
                AksuAdmin.Forms.init();
                AksuAdmin.Maps.init();
                AksuAdmin.FileUpload.init();
            });
        },
        
        // Yardımcı fonksiyonlar
        Utils: {
            // Birden fazla elementte aynı sınıfı toggle etmek için yardımcı fonksiyon
            toggleClassOnElements: function(elements, className) {
                elements.forEach(function(element) {
                    if (element) {
                        element.classList.toggle(className);
                    }
                });
            },
            
            // URL parametresi alır
            getUrlParam: function(name) {
                const searchParams = new URLSearchParams(window.location.search);
                return searchParams.get(name);
            },
            
            // Mesaj kutusu gösterme
            showMessage: function(message, type = 'success') {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                alertContainer.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Mesajı sayfanın üst kısmına ekle
                const contentContainer = document.querySelector('.container-fluid') || document.body;
                contentContainer.insertBefore(alertContainer, contentContainer.firstChild);
                
                // 5 saniye sonra mesajı otomatik kapat
                setTimeout(() => {
                    const fadeOut = setInterval(() => {
                        if (alertContainer.style.opacity === '') {
                            alertContainer.style.opacity = 1;
                        }
                        if (alertContainer.style.opacity > 0) {
                            alertContainer.style.opacity -= 0.1;
                        } else {
                            clearInterval(fadeOut);
                            alertContainer.remove();
                        }
                    }, 25);
                }, 5000);
            }
        },
        
        // Temel admin panel işlevleri (admin.js)
        Core: {
            init: function() {
                this.initSidebar();
                this.initScrollToTop();
                this.initTabsFunctionality();
                this.handleListingTypeRadios();
                this.handleDistanceRows();
                this.initFormValidation();
            },
            
            // Sidebar işlemleri
            initSidebar: function() {
                const sidebarToggles = [
                    document.querySelector('#sidebarToggle'),
                    document.querySelector('#sidebarToggleTop')
                ].filter(Boolean);
                
                const body = document.body;
                const sidebar = document.querySelector('.sidebar');
                
                // Sidebar toggling için ortak dinleyici
                sidebarToggles.forEach(function(toggle) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (sidebar) {
                            sidebar.classList.toggle('toggled');
                            body.classList.toggle('sidebar-toggled');
                        }
                    });
                });
                
                // Küçük ekranlarda sidebar dışına tıklandığında sidebar'ı kapat
                document.addEventListener('click', function(e) {
                    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('toggled')) {
                        const isToggleButton = sidebarToggles.some(btn => btn && btn.contains(e.target));
                        const isInsideSidebar = sidebar.contains(e.target);
                        
                        if (!isInsideSidebar && !isToggleButton) {
                            sidebar.classList.remove('toggled');
                            body.classList.remove('sidebar-toggled');
                        }
                    }
                });
                
                // Overlay oluştur
                if (!document.querySelector('.sidebar-overlay') && sidebar) {
                    const overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    document.body.appendChild(overlay);
                    
                    // Overlay'e tıklandığında sidebar'ı kapat
                    overlay.addEventListener('click', function() {
                        if (sidebar.classList.contains('toggled')) {
                            sidebar.classList.remove('toggled');
                            body.classList.remove('sidebar-toggled');
                        }
                    });
                }
            },
            
            // Scroll-to-top düğmesi
            initScrollToTop: function() {
                const scrollToTopButton = document.querySelector('.scroll-to-top');
                if (scrollToTopButton) {
                    window.addEventListener('scroll', function() {
                        scrollToTopButton.style.display = window.pageYOffset > 100 ? 'flex' : 'none';
                    });
                    
                    scrollToTopButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
            },
            
            // Tabs işlevselliği
            initTabsFunctionality: function() {
                // Bootstrap tab işlevselliği kontrolü
                const triggerTabList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tab"]'));
                triggerTabList.forEach(function(triggerEl) {
                    const tabTrigger = new bootstrap.Tab(triggerEl);
                    
                    triggerEl.addEventListener('click', function(event) {
                        event.preventDefault();
                        tabTrigger.show();
                    });
                });
                
                // Next/Previous tab navigasyonu
                const nextTabButtons = document.querySelectorAll('.next-tab');
                nextTabButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const nextTabId = this.getAttribute('data-next');
                        const nextTabTrigger = document.getElementById(nextTabId);
                        if (nextTabTrigger) {
                            const tabInstance = bootstrap.Tab.getInstance(nextTabTrigger) || new bootstrap.Tab(nextTabTrigger);
                            tabInstance.show();
                        }
                    });
                });
                
                const prevTabButtons = document.querySelectorAll('.prev-tab');
                prevTabButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const prevTabId = this.getAttribute('data-prev');
                        const prevTabTrigger = document.getElementById(prevTabId);
                        if (prevTabTrigger) {
                            const tabInstance = bootstrap.Tab.getInstance(prevTabTrigger) || new bootstrap.Tab(prevTabTrigger);
                            tabInstance.show();
                        }
                    });
                });
                
                // Tab değiştiğinde haritaları yeniden yükle
                const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
                tabLinks.forEach(function(link) {
                    link.addEventListener('shown.bs.tab', function(e) {
                        const target = e.target.getAttribute('href');
                        
                        // Location tab'ına geçildiğinde haritayı yeniden yükle
                        if (target === '#location') {
                            setTimeout(() => {
                                if (typeof AksuAdmin.Maps.initializeMap === 'function') {
                                    AksuAdmin.Maps.initializeMap('map-container', { draggable: true });
                                }
                            }, 100);
                        }
                        
                        // Aktif tab localStorage'a kaydet
                        localStorage.setItem('activeAdminTab', target);
                    });
                });
                
                // Önceki aktif tabı localStorage'dan getir
                const activeTabId = localStorage.getItem('activeAdminTab');
                if (activeTabId) {
                    const activeTab = document.querySelector(`[href="${activeTabId}"]`);
                    if (activeTab) {
                        activeTab.click();
                    }
                }
            },
            
            // Listeleme türü radio butonları (Fiyat alanları)
            handleListingTypeRadios: function() {
                const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
                const salePriceContainer = document.getElementById('sale-price-container');
                const rentPriceContainer = document.getElementById('rent-price-container');
                const salePriceInput = document.getElementById('sale_price');
                const rentPriceInput = document.getElementById('rent_price');
                
                if (listingTypeRadios.length && salePriceContainer && rentPriceContainer) {
                    listingTypeRadios.forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            if (this.value === 'sale') {
                                salePriceContainer.style.display = 'block';
                                rentPriceContainer.style.display = 'none';
                                if (salePriceInput) salePriceInput.required = true;
                                if (rentPriceInput) {
                                    rentPriceInput.required = false;
                                    rentPriceInput.value = '';
                                }
                            } else if (this.value === 'rent') {
                                salePriceContainer.style.display = 'none';
                                rentPriceContainer.style.display = 'block';
                                if (rentPriceInput) rentPriceInput.required = true;
                                if (salePriceInput) {
                                    salePriceInput.required = false;
                                    salePriceInput.value = '';
                                }
                            } else if (this.value === 'both') {
                                salePriceContainer.style.display = 'block';
                                rentPriceContainer.style.display = 'block';
                                if (salePriceInput) salePriceInput.required = true;
                                if (rentPriceInput) rentPriceInput.required = true;
                            }
                        });
                    });
                    
                    // Sayfa yüklendiğinde mevcut seçime göre durumu ayarla
                    const initialSelectedType = document.querySelector('input[name="listing_type"]:checked');
                    if (initialSelectedType) {
                        initialSelectedType.dispatchEvent(new Event('change'));
                    }
                }
            },
            
            // Mesafe alanları (Distance Rows)
            handleDistanceRows: function() {
                const addDistanceBtn = document.getElementById('add-distance');
                const distancesContainer = document.getElementById('distances-container');
                if (addDistanceBtn && distancesContainer) {
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
                            </div>
                        `;
                        distancesContainer.appendChild(newRow);
                        // Silme butonuna event listener ekle
                        newRow.querySelector('.remove-distance').addEventListener('click', function() {
                            newRow.remove();
                        });
                    });
                    
                    // Mevcut silme butonlarına event listener ekle
                    distancesContainer.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-distance')) {
                            e.target.closest('.distance-row').remove();
                        }
                    });
                }
            },
            
            // Form doğrulama (Validation)
            initFormValidation: function() {
                const forms = document.querySelectorAll('form.needs-validation');
                Array.prototype.slice.call(forms).forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            // Hatalı ilk elemanı bul ve ona odaklan/scroll et
                            const invalidElement = form.querySelector(':invalid');
                            if (invalidElement) {
                                // Eğer eleman bir tab içindeyse o tab'ı aktif et
                                const tabPane = invalidElement.closest('.tab-pane');
                                if (tabPane && !tabPane.classList.contains('active')) {
                                    const tabTrigger = document.querySelector(`[data-bs-target="#${tabPane.id}"]`);
                                    if (tabTrigger) {
                                        const tabInstance = bootstrap.Tab.getInstance(tabTrigger) || new bootstrap.Tab(tabTrigger);
                                        tabInstance.show();
                                        // Tab değiştikten sonra odaklanma için küçük bir gecikme
                                        setTimeout(() => {
                                            invalidElement.focus();
                                            invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }, 150);
                                    } else {
                                        invalidElement.focus();
                                        invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                } else {
                                    invalidElement.focus();
                                    invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }
        },
        
        // Form işleme (form-upload.js)
        Forms: {
            init: function() {
                // Form gönderim engelleme
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
                
                this.initTabNavigation();
            },
            
            // Tab navigasyonu
            initTabNavigation: function() {
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
                        if (AksuAdmin.Forms.validateTabFields(currentTab)) {
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
                            if (!AksuAdmin.Forms.validateTabFields(pane, false)) {
                                hasError = true;
                                if (!firstErrorTab) {
                                    firstErrorTab = pane.id;
                                }
                            }
                        });
                        
                        // Hata varsa işlemi sonlandır
                        if (hasError) {
                            e.preventDefault();
                            
                            // İlk hatalı sekmeye git
                            const errorTabLink = document.querySelector(`[href="#${firstErrorTab}"]`);
                            if (errorTabLink) {
                                const bsTab = new bootstrap.Tab(errorTabLink);
                                bsTab.show();
                            }
                            
                            // Hata mesajı göster
                            AksuAdmin.Forms.showValidationError("Lütfen tüm zorunlu alanları doldurun");
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
            },
            
            // Sekme içindeki zorunlu alanları doğrula
            validateTabFields: function(tabPane, showErrors = true) {
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
                        AksuAdmin.Forms.showValidationError("Lütfen haritada konum seçin", tabPane);
                        isValid = false;
                    }
                }
                
                return isValid;
            },
            
            // Doğrulama hatası göster
            showValidationError: function(message, container = null) {
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
        },
        
        // Dosya yükleme modülü (form-upload.js)
        FileUpload: {
            init: function() {
                this.initFileUpload();
                
                // Image preview işlevini güncelle
                const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
                imageInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const previewContainer = document.querySelector(this.dataset.preview || '#image-previews');
                        
                        if (!previewContainer) return;
                        
                        previewContainer.innerHTML = '';
                        
                        if (this.files && this.files.length > 0) {
                            for (let i = 0; i < this.files.length; i++) {
                                const file = this.files[i];
                                
                                // Skip non-image files
                                if (!file.type.match('image.*')) continue;
                                
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const preview = document.createElement('div');
                                    preview.className = 'image-preview';
                                    
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    preview.appendChild(img);
                                    
                                    const filename = document.createElement('div');
                                    filename.className = 'image-filename';
                                    filename.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                                    preview.appendChild(filename);
                                    
                                    previewContainer.appendChild(preview);
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    });
                });
            },
            
            // Dosya yükleme işlemleri
            initFileUpload: function() {
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
                            AksuAdmin.FileUpload.handleFiles(e.dataTransfer.files);
                        }
                    });
                }
                
                // Dosya seçimini işle
                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        AksuAdmin.FileUpload.handleFiles(this.files);
                    });
                }
            },
            
            // Dosyaları işle
            handleFiles: function(files) {
                const fileInput = document.getElementById('images');
                const previewContainer = document.getElementById('image-previews');
                const mainImageContainer = document.getElementById('main-image-container');
                
                if (!files || files.length === 0 || !previewContainer) return;
                
                // Maksimum görsel sayısını kontrol et
                const maxImages = 25;
                const currentImages = document.querySelectorAll('.image-preview').length;
                const remainingSlots = maxImages - currentImages;
                
                if (remainingSlots <= 0) {
                    AksuAdmin.Utils.showMessage("Maksimum 25 görsel yükleyebilirsiniz.", "warning");
                    return;
                }
                
                // İşlenecek dosya sayısını belirle
                const processCount = Math.min(files.length, remainingSlots);
                
                // Her dosyayı işle
                for (let i = 0; i < processCount; i++) {
                    const file = files[i];
                    
                    // Dosya türünü kontrol et
                    if (!file.type.match('image/(jpeg|jpg|png)')) {
                        AksuAdmin.Utils.showMessage(`"${file.name}" desteklenmeyen dosya formatı. Sadece JPG ve PNG kabul edilir.`, "warning");
                        continue;
                    }
                    
                    // Dosya boyutunu kontrol et (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        AksuAdmin.Utils.showMessage(`"${file.name}" dosyası çok büyük. Maksimum 5MB izin verilir.`, "warning");
                        continue;
                    }
                    
                    // Dosyayı önizle
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        AksuAdmin.FileUpload.addImagePreview(e.target.result, file.name, i);
                        AksuAdmin.FileUpload.updateMainImageSelect();
                    };
                    reader.readAsDataURL(file);
                }
                
                // Ana görsel seçimini güncelle
                if (previewContainer.children.length > 0 && mainImageContainer) {
                    mainImageContainer.style.display = 'block';
                }
            },
            
            // Görsel önizleme ekle
            addImagePreview: function(src, filename, index) {
                const previewContainer = document.getElementById('image-previews');
                if (!previewContainer) return;
                
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.dataset.filename = filename;
                
                // Görsel önizleme resmi
                const img = document.createElement('img');
                img.src = src;
                img.alt = filename;
                preview.appendChild(img);
                
                // Dosya adı etiketi
                const nameLabel = document.createElement('div');
                nameLabel.className = 'image-name';
                nameLabel.textContent = AksuAdmin.FileUpload.truncateFilename(filename, 15);
                preview.appendChild(nameLabel);
                
                // Silme butonu
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'delete-image-btn';
                deleteBtn.innerHTML = '×';
                deleteBtn.addEventListener('click', function() {
                    preview.remove();
                    AksuAdmin.FileUpload.updateMainImageSelect();
                    
                    // Tüm görüntüler kaldırıldıysa ana görüntü seçiciyi gizle
                    const mainImageContainer = document.getElementById('main-image-container');
                    if (document.querySelectorAll('.image-preview').length === 0 && mainImageContainer) {
                        mainImageContainer.style.display = 'none';
                    }
                });
                preview.appendChild(deleteBtn);
                previewContainer.appendChild(preview);
            },
            
            // Ana görsel seçim menüsünü güncelle
            updateMainImageSelect: function() {
                const mainImageSelect = document.getElementById('main-image-select');
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
            },
            
            // Dosya adını kısalt
            truncateFilename: function(name, maxLength) {
                if (!name || name.length <= maxLength) return name;
                const ext = name.split('.').pop();
                const basename = name.substring(0, name.length - ext.length - 1);
                return basename.substring(0, maxLength - ext.length - 4) + '...' + ext;
            }
        },
        
        // Harita entegrasyonu (map-integration.js)
        Maps: {
            // Harita nesnelerini ve marker gruplarını sakla
            instances: {},
            markers: {},
            config: {
                defaultLat: 39.1,
                defaultLng: 35.6,
                defaultZoom: 6,
                detailZoom: 15,
                maxZoom: 19,
                tileUrl: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                tileAttribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            },
            
            init: function() {
                // Sayfadaki tüm haritaları başlat
                this.initializeAllMaps();
                
                // Harita konteynerini gözlemle, görünür hale geldiğinde yeniden yükle
                this.observeMapContainers();
                
                // Tab değişimlerini dinle
                this.setupTabChangeListeners();
            },
            
            // Sayfadaki tüm haritaları başlat
            initializeAllMaps: function() {
                this.initializeMap('map-container');
                this.initializeMap('property-location-map');
                this.initializeMap('property-map');
                this.initializeMap('search-map');
            },
            
            // Tab değişimlerini dinle ve haritaları yeniden boyutlandır
            setupTabChangeListeners: function() {
                const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
                
                tabLinks.forEach(function(tabLink) {
                    tabLink.addEventListener('shown.bs.tab', function(e) {
                        // Yeni tab'da bir harita var mı kontrol et
                        const targetTab = document.querySelector(e.target.getAttribute('href'));
                        if (targetTab) {
                            const maps = targetTab.querySelectorAll('[id$="-map"], [id$="_map"], #map-container');
                            maps.forEach(function(mapContainer) {
                                if (mapContainer._leaflet_id) {
                                    setTimeout(function() {
                                        window.dispatchEvent(new Event('resize'));
                                        if (mapContainer._leaflet_map) {
                                            mapContainer._leaflet_map.invalidateSize();
                                        }
                                    }, 100);
                                } else {
                                    // Eğer harita henüz oluşturulmamışsa, yeniden başlat
                                    AksuAdmin.Maps.initializeMap(mapContainer.id);
                                }
                            });
                        }
                    });
                });
            },
            
            // Harita konteyneri için MutationObserver ayarla
            observeMapContainers: function() {
                const tabPanes = document.querySelectorAll('.tab-pane');
                
                if (tabPanes.length) {
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'class') {
                                const el = mutation.target;
                                if (el.classList.contains('active') && el.classList.contains('show')) {
                                    // Tab aktifleştiğinde içindeki haritayı yeniden başlat
                                    const mapContainers = el.querySelectorAll('[id$="-map"], [id$="_map"], #map-container');
                                    mapContainers.forEach(function(mapContainer) {
                                        setTimeout(function() {
                                            if (mapContainer._leaflet_id) {
                                                window.dispatchEvent(new Event('resize'));
                                                if (mapContainer._leaflet_map) {
                                                    mapContainer._leaflet_map.invalidateSize();
                                                }
                                            } else {
                                                AksuAdmin.Maps.initializeMap(mapContainer.id);
                                            }
                                        }, 100);
                                    });
                                }
                            }
                        });
                    });
                    
                    tabPanes.forEach(function(pane) {
                        observer.observe(pane, { attributes: true });
                    });
                }
            },
            
            // Harita başlatma fonksiyonu
            initializeMap: function(containerId, options = {}) {
                const mapContainer = document.getElementById(containerId);
                
                if (!mapContainer) return null;
                
                // Harita container'ının boyutlarını kontrol et
                if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
                    mapContainer.style.height = '400px';
                }
                
                try {
                    // Leaflet yüklü mü kontrol et
                    if (typeof L === 'undefined') {
                        console.error('Leaflet kütüphanesi yüklenemedi!');
                        AksuAdmin.Maps.loadLeafletDynamically();
                        return null;
                    }
                    
                    // Önceki haritayı temizle (eğer varsa)
                    if (mapContainer._leaflet_id) {
                        mapContainer._leaflet_id = null;
                    }
                    
                    // Ayarları
                    const settings = Object.assign({}, {
                        latInput: options.latInput || document.getElementById('latitude'),
                        lngInput: options.lngInput || document.getElementById('longitude'),
                        initialLat: AksuAdmin.Maps.config.defaultLat,
                        initialLng: AksuAdmin.Maps.config.defaultLng,
                        initialZoom: AksuAdmin.Maps.config.defaultZoom,
                        draggable: options.draggable || false
                    });
                    
                    // Başlangıç konumunu ayarla
                    let initialLat = settings.initialLat;
                    let initialLng = settings.initialLng;
                    let initialZoom = settings.initialZoom;
                    
                    // Veri attribute'larını kontrol et
                    if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
                        initialLat = parseFloat(mapContainer.dataset.lat);
                        initialLng = parseFloat(mapContainer.dataset.lng);
                        initialZoom = AksuAdmin.Maps.config.detailZoom;
                    }
                    
                    // Formdaki gizli alanları kontrol et
                    if (settings.latInput && settings.latInput.value && settings.lngInput && settings.lngInput.value) {
                        initialLat = parseFloat(settings.latInput.value);
                        initialLng = parseFloat(settings.lngInput.value);
                        initialZoom = AksuAdmin.Maps.config.detailZoom;
                    }
                    
                    // Haritayı oluştur
                    const map = L.map(containerId, {
                        center: [initialLat, initialLng],
                        zoom: initialZoom,
                        scrollWheelZoom: true,
                        zoomControl: true
                    });
                    
                    // Referansı sakla
                    mapContainer._leaflet_map = map;
                    AksuAdmin.Maps.instances[containerId] = map;
                    
                    // Tile layer ekle
                    L.tileLayer(AksuAdmin.Maps.config.tileUrl, {
                        attribution: AksuAdmin.Maps.config.tileAttribution,
                        maxZoom: AksuAdmin.Maps.config.maxZoom
                    }).addTo(map);
                    
                    // Marker ekle (varsa koordinatlar)
                    let marker;
                    if (initialZoom > AksuAdmin.Maps.config.defaultZoom) {
                        marker = AksuAdmin.Maps.addMarker([initialLat, initialLng], map, settings.draggable);
                        
                        // Marker sürüklendiğinde (eğer draggable ise)
                        if (settings.draggable && marker && settings.latInput && settings.lngInput) {
                            marker.on('dragend', function(e) {
                                const position = marker.getLatLng();
                                settings.latInput.value = position.lat.toFixed(6);
                                settings.lngInput.value = position.lng.toFixed(6);
                            });
                        }
                    }
                    
                    // Click event ekle (form için)
                    if (settings.latInput && settings.lngInput) {
                        map.on('click', function(e) {
                            const lat = e.latlng.lat;
                            const lng = e.latlng.lng;
                            
                            // Gizli alanlara değerleri ata
                            settings.latInput.value = lat.toFixed(6);
                            settings.lngInput.value = lng.toFixed(6);
                            
                            // Marker'ı güncelle
                            if (marker) {
                                marker.setLatLng(e.latlng);
                            } else {
                                marker = AksuAdmin.Maps.addMarker(e.latlng, map, settings.draggable);
                                
                                // Yeni marker için sürükleme olayı
                                if (settings.draggable) {
                                    marker.on('dragend', function(e) {
                                        const position = marker.getLatLng();
                                        settings.latInput.value = position.lat.toFixed(6);
                                        settings.lngInput.value = position.lng.toFixed(6);
                                    });
                                }
                            }
                        });
                    }
                    
                    // Property verisi ekle (varsa)
                    if (window.propertyData && window.propertyData.length) {
                        AksuAdmin.Maps.addPropertyMarkers(map, window.propertyData);
                    }
                    
                    // Harita tam olarak yüklendiğinde boyutları yeniden hesapla
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 100);
                    
                    // Harita nesnesini sakla
                    AksuAdmin.Maps.markers[containerId] = marker ? [marker] : [];
                    
                    return map;
                    
                } catch (error) {
                    console.error('Harita yüklenirken hata oluştu:', error);
                    return null;
                }
            },
            
            // Marker oluştur
            addMarker: function(latlng, map, draggable = false) {
                // Marker ikonu oluştur
                const markerIcon = L.divIcon({
                    className: 'custom-marker-icon',
                    html: '<div class="marker-container"><div class="marker-pin"></div></div>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                });
                
                // Marker ekle
                return L.marker(latlng, {
                    icon: markerIcon,
                    draggable: draggable
                }).addTo(map);
            },
            
            // Emlak marker'larını ekle
            addPropertyMarkers: function(map, properties) {
                if (!properties || !properties.length) return;
                
                const markers = [];
                
                properties.forEach(function(property) {
                    if (!property.latitude || !property.longitude) return;
                    
                    // Fiyat formatını belirle
                    let priceText = '';
                    let markerClass = 'marker-price-sale'; // Default: satılık için
                    
                    if (property.rent_price && property.rent_price > 0) {
                        priceText = `${AksuAdmin.Maps.formatPrice(property.rent_price)} ₺/ay`;
                        markerClass = 'marker-price-rent'; // Kiralık için
                    } else if (property.sale_price && property.sale_price > 0) {
                        priceText = `${AksuAdmin.Maps.formatPrice(property.sale_price)} ₺`;
                    } else {
                        priceText = 'Fiyat Belirtilmemiş';
                    }
                    
                    // Marker ikonu oluştur
                    const markerIcon = L.divIcon({
                        className: 'property-marker',
                        html: `
                            <div class="marker-container">
                                <div class="marker-icon"><i class="bi bi-house-fill"></i></div>
                                <div class="marker-price ${markerClass}">${priceText}</div>
                            </div>
                        `,
                        iconSize: [140, 50],
                        iconAnchor: [70, 50],
                        popupAnchor: [0, -50]
                    });
                    
                    // Marker ekle
                    const marker = L.marker([property.latitude, property.longitude], {
                        icon: markerIcon
                    }).addTo(map);
                    
                    // Popup içeriği
                    const popupContent = `
                        <div class="map-popup">
                            <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                            <h5 class="popup-title">${property.title}</h5>
                            <p class="popup-price">${priceText}</p>
                            <a href="listing.php?id=${property.id}" class="btn btn-primary w-100">Detaylar</a>
                        </div>
                    `;
                    
                    // Popup ekle
                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'property-popup'
                    });
                    
                    markers.push(marker);
                });
                
                // Tüm marker'ları sığdır
                if (markers.length > 0) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
                
                return markers;
            },
            
            // Fiyat formatla
            formatPrice: function(price) {
                // Fiyat değerini güvenli bir şekilde doğrula
                if (typeof price !== 'number' || isNaN(price)) {
                    price = 0;
                }
                
                return new Intl.NumberFormat('tr-TR').format(price);
            },
            
            // Leaflet'i dinamik olarak yükle
            loadLeafletDynamically: function() {
                // CSS yükle
                if (!document.querySelector('link[href*="leaflet.css"]')) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css';
                    document.head.appendChild(link);
                }
                
                // JS yükle
                if (!document.querySelector('script[src*="leaflet.js"]')) {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js';
                    script.onload = function() {
                        // Leaflet yüklendikten sonra haritaları başlat
                        setTimeout(AksuAdmin.Maps.initializeAllMaps, 100);
                    };
                    document.head.appendChild(script);
                }
            }
        }
    };
    
    // Admin modülünü başlat
    AksuAdmin.init();
    
    // Global olarak erişilebilir yap (gerekirse)
    window.AksuAdmin = AksuAdmin;
    
})();
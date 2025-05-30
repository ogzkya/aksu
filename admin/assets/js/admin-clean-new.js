(function() {
    'use strict';

    var AksuAdmin = {
        init: function() {
            // Çakışma kontrolü - HEMEN başta kontrol et
            if (this.checkConflict()) {
                console.log('Admin-clean.js: Çakışma tespit edildi, sistem durduruldu');
                return;
            }
            
            this.initFileUpload();
            this.initDistanceManager();
            this.initFormValidation();
            this.initTabNavigation();
        },

        checkConflict: function() {
            return (
                window.ListingImageUploader || 
                window.ADD_LISTING_IMAGE_UPLOADER_ACTIVE ||
                document.querySelector('.listing-image-uploader-active') ||
                document.documentElement.classList.contains('listing-image-uploader-active') ||
                document.querySelector('.add-listing-image-uploader') ||
                document.querySelector('#drag-drop-area.add-listing-image-uploader')
            );
        },

        // Dosya yükleme işlevini başlat
        initFileUpload: function() {
            // Çift kontrol
            if (this.checkConflict()) {
                console.log('Admin-clean.js initFileUpload: Çakışma tespit edildi, durduruldu');
                return;
            }

            var fileInput = document.getElementById('images');
            var previewContainer = document.getElementById('image-previews');
            var dragArea = document.getElementById('drag-drop-area');
            var selectBtn = document.getElementById('select-files-btn');

            if (!fileInput || !previewContainer || !dragArea || !selectBtn) {
                console.log('Admin-clean.js: Gerekli elementler bulunamadı');
                return;
            }

            // Son kontrol - eğer drag area'da özel sınıf varsa çık
            if (dragArea.classList.contains('add-listing-image-uploader')) {
                console.log('Admin-clean.js: add-listing-image-uploader sınıfı tespit edildi, durduruldu');
                return;
            }

            console.log('Admin-clean.js: Görsel yükleme sistemi aktifleştiriliyor');

            // Dosya seçme butonu
            selectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });

            // Drag & Drop olayları
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
                dragArea.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            dragArea.addEventListener('dragover', function() {
                dragArea.classList.add('border-primary', 'bg-light');
            });

            dragArea.addEventListener('dragleave', function() {
                dragArea.classList.remove('border-primary', 'bg-light');
            });

            dragArea.addEventListener('drop', function(e) {
                dragArea.classList.remove('border-primary', 'bg-light');
                if (e.dataTransfer.files.length > 0) {
                    AksuAdmin.handleFiles(e.dataTransfer.files, previewContainer);
                }
            });

            fileInput.addEventListener('change', function(e) {
                AksuAdmin.handleFiles(e.target.files, previewContainer);
            });
        },

        // Dosyaları işle ve önizleme oluştur
        handleFiles: function(files, previewContainer) {
            previewContainer.innerHTML = '';
            
            var mainSelect = document.getElementById('main-image-select');
            if (mainSelect) {
                mainSelect.innerHTML = '<option value="0">İlk yüklenen görsel</option>';
            }

            // Her dosyayı işle
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (!file.type.startsWith('image/')) continue;
                if (file.size > 10 * 1024 * 1024) continue; // 10MB limit

                (function(file, index) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var div = document.createElement('div');
                        div.className = 'image-preview-item position-relative border rounded overflow-hidden me-2 mb-2';
                        div.style.cssText = 'width:150px;height:100px;display:inline-block;';
                        
                        div.innerHTML = 
                            '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">' +
                            '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" ' +
                            'style="width:20px;height:20px;" onclick="this.parentElement.remove()">×</button>';
                        
                        previewContainer.appendChild(div);

                        // Ana görsel seçeneklerine ekle
                        if (mainSelect) {
                            var option = document.createElement('option');
                            option.value = index;
                            option.textContent = 'Görsel ' + (index + 1);
                            mainSelect.appendChild(option);
                        }
                    };
                    reader.readAsDataURL(file);
                })(file, i);
            }

            // Ana görsel container'ını göster
            var mainContainer = document.getElementById('main-image-container');
            if (mainContainer) {
                mainContainer.classList.remove('d-none');
            }
        },

        // Mesafe yöneticisini başlat
        initDistanceManager: function() {
            var container = document.getElementById('distances-container');
            var addBtn = document.getElementById('add-distance');

            if (!container || !addBtn) return;

            // Yeni mesafe ekleme
            addBtn.addEventListener('click', function() {
                var row = document.createElement('div');
                row.className = 'distance-row row mb-3';
                row.innerHTML = 
                    '<div class="col-md-5">' +
                        '<input type="text" class="form-control" name="distance_name[]" placeholder="Mekan Adı">' +
                    '</div>' +
                    '<div class="col-md-5">' +
                        '<div class="input-group">' +
                            '<input type="number" class="form-control" name="distance_value[]" placeholder="Mesafe" step="0.1" min="0">' +
                            '<span class="input-group-text">km</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-2">' +
                        '<button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>' +
                    '</div>';
                container.appendChild(row);
            });

            // Mesafe silme
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-distance')) {
                    e.target.closest('.distance-row').remove();
                }
            });
        },

        // Form validasyonunu başlat
        initFormValidation: function() {
            var forms = document.querySelectorAll('form.needs-validation');
            for (var i = 0; i < forms.length; i++) {
                forms[i].addEventListener('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    this.classList.add('was-validated');
                });
            }
        },

        // Tab navigasyonunu başlat
        initTabNavigation: function() {
            // İleri butonları
            var nextBtns = document.querySelectorAll('.next-tab');
            for (var i = 0; i < nextBtns.length; i++) {
                nextBtns[i].addEventListener('click', function() {
                    var nextTabId = this.getAttribute('data-next');
                    var nextTab = document.getElementById(nextTabId);
                    if (nextTab) {
                        var tabInstance = new bootstrap.Tab(nextTab);
                        tabInstance.show();
                    }
                });
            }

            // Geri butonları
            var prevBtns = document.querySelectorAll('.prev-tab');
            for (var i = 0; i < prevBtns.length; i++) {
                prevBtns[i].addEventListener('click', function() {
                    var prevTabId = this.getAttribute('data-prev');
                    var prevTab = document.getElementById(prevTabId);
                    if (prevTab) {
                        var tabInstance = new bootstrap.Tab(prevTab);
                        tabInstance.show();
                    }
                });
            }
        }
    };

    // Sayfa kendi sistemini kullanmıyorsa AksuAdmin'i başlat
    document.addEventListener('DOMContentLoaded', function() {
        // Çakışma kontrolü - birden fazla kontrol noktası
        if (window.ListingImageUploader || 
            window.ADD_LISTING_IMAGE_UPLOADER_ACTIVE ||
            document.querySelector('.listing-image-uploader-active') ||
            document.documentElement.classList.contains('listing-image-uploader-active') ||
            document.querySelector('.add-listing-image-uploader')) {
            console.log('Admin-clean.js: Sayfa kendi sistemini kullanıyor, başlatılmıyor');
            return;
        }
        
        console.log('Admin-clean.js: Sistem başlatılıyor');
        AksuAdmin.init();
    });

    // Backup olarak window load'da da kontrol et
    window.addEventListener('load', function() {
        if (!window.ADD_LISTING_IMAGE_UPLOADER_ACTIVE && 
            !window.ListingImageUploader && 
            !document.querySelector('.listing-image-uploader-active') &&
            !AksuAdmin._initialized) {
            
            // Eğer DOMContentLoaded'da başlamamışsa burada başlat
            if (document.getElementById('images') && !document.querySelector('.add-listing-image-uploader')) {
                console.log('Admin-clean.js: Window load backup başlatma');
                AksuAdmin.init();
                AksuAdmin._initialized = true;
            }
        }
    });
    
    // Global erişim için
    window.AksuAdmin = AksuAdmin;
})();

/* Bu kodu /assets/js/image-uploader.js olarak kaydedin */

/**
 * Aksu Emlak - Modern Resim Yükleme Sistemi
 * Gelişmiş sürükle-bırak ve önizleme yetenekleri ile dosya yükleme
 */

class ImageUploader {
    constructor(options) {
        // Varsayılan ayarlar
        this.settings = {
            dropAreaSelector: '#drag-drop-area',
            fileInputSelector: '#images', // veya '#new_images' düzenleme sayfası için
            previewSelector: '#image-previews',
            mainImageSelectSelector: '#main-image-select',
            mainImageContainerSelector: '#main-image-container',
            selectBtnSelector: '#select-files-btn',
            removeBtnSelector: '.remove-image',
            maxFileSize: 10 * 1024 * 1024, // 10MB
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png'],
            maxFiles: 20,
            editMode: false,
            existingImagesSelector: '.existing-image',
            deleteInputName: 'deleted_images'
        };

        // Kullanıcı ayarlarını birleştir
        Object.assign(this.settings, options || {});

        // DOM elementlerini al
        this.dropArea = document.querySelector(this.settings.dropAreaSelector);
        this.fileInput = document.querySelector(this.settings.fileInputSelector);
        this.previewsContainer = document.querySelector(this.settings.previewSelector);
        this.mainImageSelect = document.querySelector(this.settings.mainImageSelectSelector);
        this.mainImageContainer = document.querySelector(this.settings.mainImageContainerSelector);
        this.selectBtn = document.querySelector(this.settings.selectBtnSelector);
        
        // Silinen görselleri takip etmek için bir dizi
        this.deletedImages = [];
        this.deletedImagesInput = document.createElement('input');
        this.deletedImagesInput.type = 'hidden';
        this.deletedImagesInput.name = this.settings.deleteInputName;
        document.querySelector('form').appendChild(this.deletedImagesInput);
        
        // Düzenleme modu verileri
        this.existingImages = [];
        
        if (this.fileInput && this.dropArea) {
            this.init();
        } else {
            console.error('Gerekli DOM elementleri bulunamadı!');
        }
    }

    init() {
        // Seçme butonuna tıklandığında dosya seçiciyi aç
        this.selectBtn.addEventListener('click', () => this.fileInput.click());
        
        // Dosya seçildiğinde
        this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
        
        // Sürükle bırak olayları
        this.dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.dropArea.classList.add('active');
        });
        
        this.dropArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            this.dropArea.classList.remove('active');
        });
        
        this.dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.dropArea.classList.remove('active');
            
            if (e.dataTransfer.files.length > 0) {
                this.handleFiles(e.dataTransfer.files);
            }
        });
        
        // Düzenleme modunda mevcut resimleri yönet
        if (this.settings.editMode) {
            const existingImgs = document.querySelectorAll(this.settings.existingImagesSelector);
            existingImgs.forEach(img => {
                // Düzenleme modunda her bir mevcut görsele silme işlevi ekle
                const removeBtn = img.querySelector(this.settings.removeBtnSelector);
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const imgId = img.dataset.imageId;
                        if (imgId) {
                            this.deletedImages.push(imgId);
                            this.deletedImagesInput.value = this.deletedImages.join(',');
                            img.classList.add('d-none');
                        }
                    });
                }
                
                // Mevcut görselleri diziye ekle
                if (img.dataset.imageId) {
                    this.existingImages.push({
                        id: img.dataset.imageId,
                        path: img.dataset.imagePath,
                        isMain: img.dataset.isMain === '1'
                    });
                }
            });
            
            // Ana görsel seçiminde mevcut görselleri göster
            this.updateMainImageSelect();
        }
    }
    
    handleFiles(files) {
        // Dosya limit kontrolü
        if (files.length > this.settings.maxFiles) {
            alert(`En fazla ${this.settings.maxFiles} dosya yükleyebilirsiniz!`);
            return;
        }
        
        // Dosya tipi ve boyut kontrolü
        let validFiles = Array.from(files).filter(file => {
            // Tür kontrolü
            if (!this.settings.allowedTypes.includes(file.type)) {
                alert(`${file.name} desteklenmeyen bir dosya türü. Sadece JPG ve PNG dosyaları yükleyebilirsiniz.`);
                return false;
            }
            
            // Boyut kontrolü
            if (file.size > this.settings.maxFileSize) {
                alert(`${file.name} çok büyük! Maksimum dosya boyutu 10MB'dır.`);
                return false;
            }
            
            return true;
        });
        
        // Dosyaları işle ve önizleme oluştur
        validFiles.forEach((file, index) => {
            this.createPreview(file, index);
        });
        
        // Ana görsel seçimi için dropdown'ı güncelle
        this.updateMainImageSelect();
        
        // Ana görsel seçici göster
        if (this.previewsContainer.children.length > 0 || this.existingImages.length > 0) {
            this.mainImageContainer.classList.remove('d-none');
        }
    }
    
    createPreview(file, index) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            // Görsel önizleme oluştur
            const preview = document.createElement('div');
            preview.className = 'image-preview-item position-relative border rounded overflow-hidden m-2';
            preview.style.width = '150px';
            preview.style.height = '100px';
            preview.dataset.fileIndex = index;
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-100 h-100 object-fit-cover';
            preview.appendChild(img);
            
            // Dosya adı göster
            const filename = document.createElement('div');
            filename.className = 'bg-dark bg-opacity-50 text-white small p-1 position-absolute bottom-0 w-100 text-truncate';
            filename.textContent = file.name;
            preview.appendChild(filename);
            
            // Silme butonu ekle
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center';
            removeBtn.style.width = '20px';
            removeBtn.style.height = '20px';
            removeBtn.innerHTML = '×';
            removeBtn.type = 'button';
            removeBtn.addEventListener('click', () => {
                preview.remove();
                
                // Ana görsel seçiciyi güncelle
                this.updateMainImageSelect();
                
                // Tüm önizlemeler kaldırıldıysa ve düzenleme modunda mevcut resim yoksa
                // ana görsel seçiciyi gizle
                if (this.previewsContainer.children.length === 0 && 
                    this.existingImages.filter(img => !this.deletedImages.includes(img.id)).length === 0) {
                    this.mainImageContainer.classList.add('d-none');
                }
            });
            preview.appendChild(removeBtn);
            
            this.previewsContainer.appendChild(preview);
        };
        
        reader.readAsDataURL(file);
    }
    
    updateMainImageSelect() {
        // Ana görsel seçicisini temizle
        this.mainImageSelect.innerHTML = '';
        
        // Düzenleme modunda mevcut görselleri ekle
        if (this.settings.editMode) {
            this.existingImages.forEach((img, index) => {
                // Silinen resimler için seçenek ekleme
                if (this.deletedImages.includes(img.id)) return;
                
                const option = document.createElement('option');
                option.value = `existing_${img.id}`;
                option.textContent = `Mevcut Görsel ${index + 1}`;
                
                // Eğer bu ana görsel ise, seçili yap
                if (img.isMain) {
                    option.selected = true;
                }
                
                this.mainImageSelect.appendChild(option);
            });
        }
        
        // Yeni eklenen görselleri ekle
        const previews = this.previewsContainer.querySelectorAll('.image-preview-item');
        if (previews.length > 0) {
            if (this.settings.editMode) {
                // Düzenleme modunda ise, "Yeni" etiketi ekle
                const groupLabel = document.createElement('optgroup');
                groupLabel.label = 'Yeni Yüklenen Görseller';
                this.mainImageSelect.appendChild(groupLabel);
                
                previews.forEach((preview, index) => {
                    const option = document.createElement('option');
                    option.value = `new_${index}`;
                    option.textContent = `Yeni Görsel ${index + 1}`;
                    groupLabel.appendChild(option);
                });
            } else {
                // Yeni ilan ekleme modunda
                previews.forEach((preview, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = `Görsel ${index + 1}`;
                    this.mainImageSelect.appendChild(option);
                });
            }
        }
    }
}

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', function() {
    // İlan ekleme sayfasında mı, düzenleme sayfasında mı kontrol et
    const isEditPage = window.location.href.includes('edit.php');
    
    if (isEditPage) {
        // Düzenleme sayfası için
        new ImageUploader({
            fileInputSelector: '#new_images', 
            editMode: true
        });
    } else {
        // Ekleme sayfası için
        new ImageUploader();
    }
    
    // Form gönderilmeden önce dosyaların hazırlanması
    const form = document.getElementById('listingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Form doğrulama işlemleri burada
            // Burada özel form doğrulama işlemleri eklenebilir
        });
    }
});
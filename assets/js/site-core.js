/**
 * Aksu Emlak - Site Core JavaScript
 * Bu dosya script.js, map-functions-optimized.js, image-uploader.js, ajax-form-handler.js
 * dosyalarını birleştirerek daha verimli bir kod yapısı oluşturur.
 */

(function() {
    'use strict';
    
    // Ana modül nesnesi
    const AksuSite = {
        // DOM'un hazır olup olmadığını kontrol eder ve hazırsa fonksiyonları çalıştırır
        init: function() {
            document.addEventListener('DOMContentLoaded', function() {
                // Alt modülleri başlat
                AksuSite.Core.init();
                AksuSite.Maps.init();
                AksuSite.Forms.init();
                AksuSite.FileUpload.init();
            });
        },
        
        // Yardımcı fonksiyonlar
        Utils: {
            // URL parametresi alır
            getUrlParam: function(name) {
                const searchParams = new URLSearchParams(window.location.search);
                return searchParams.get(name);
            },
            
            // Geçerli URL'yi parametre ekleme/çıkarma/güncelleme ile değiştirir
            updateUrlParams: function(key, value = null) {
                const url = new URL(window.location.href);
                if (value === null) {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
                window.history.replaceState({}, '', url);
            },
            
            // Fiyat formatla
            formatPrice: function(price) {
                if (typeof price !== 'number' || isNaN(price)) {
                    price = 0;
                }
                return new Intl.NumberFormat('tr-TR').format(price);
            },
            
            // Yazı uzunluğunu kısıtlar
            truncateText: function(text, maxLength) {
                if (!text || text.length <= maxLength) return text;
                return text.substring(0, maxLength) + '...';
            },
            
            // Genel hata yakalama
            handleError: function(error, message = 'Bir hata oluştu') {
                console.error(message, error);
                return false;
            }
        },
        
        // Temel site işlevleri (script.js)
        Core: {
            init: function() {
                this.initMobileSidebar();
                this.initLazyLoading();
                this.initSmoothScroll();
                this.initViewToggle();
                this.initAutoHideAlerts();
                this.initThumbnailGallery();
                this.initScrollToTop();
                
                // AOS (Animate on Scroll) kütüphanesini başlat (eğer varsa)
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        once: true
                    });
                }
            },
            
            // Mobil menü işlemleri
            initMobileSidebar: function() {
                // Add overlay for sidebar on mobile
                if (!document.querySelector('.sidebar-overlay') && document.querySelector('.sidebar')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    document.body.appendChild(overlay);
                    
                    // Close sidebar when clicking overlay
                    overlay.addEventListener('click', function() {
                        document.body.classList.remove('sidebar-toggled');
                    });
                }
            },
            
            // Lazy loading (yavaş yüklenen görseller)
            initLazyLoading: function() {
                if ('loading' in HTMLImageElement.prototype) {
                    // Native lazy loading
                    const images = document.querySelectorAll('img[loading="lazy"]');
                    images.forEach(img => {
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                        }
                    });
                } else {
                    // Fallback for browsers that don't support lazy loading
                    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
                    
                    const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                if (img.dataset.src) {
                                    img.src = img.dataset.src;
                                }
                                observer.unobserve(img);
                            }
                        });
                    });
                    
                    lazyImages.forEach(img => {
                        lazyLoadObserver.observe(img);
                    });
                }
            },
            
            // Yumuşak kaydırma
            initSmoothScroll: function() {
                document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href');
                        const targetElement = document.querySelector(targetId);
                        
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            },
            
            // Izgara/Liste görünümü değiştirme
            initViewToggle: function() {
                const gridViewBtn = document.getElementById('grid-view-btn');
                const listViewBtn = document.getElementById('list-view-btn');
                const gridView = document.getElementById('grid-view');
                const listView = document.getElementById('list-view');
                
                if (gridViewBtn && listViewBtn) {
                    gridViewBtn.addEventListener('click', function() {
                        gridView.classList.remove('d-none');
                        listView.classList.add('d-none');
                        gridViewBtn.classList.add('active');
                        listViewBtn.classList.remove('active');
                        
                        // Tercihi localStorage'a kaydet
                        localStorage.setItem('viewPreference', 'grid');
                    });
                    
                    listViewBtn.addEventListener('click', function() {
                        gridView.classList.add('d-none');
                        listView.classList.remove('d-none');
                        gridViewBtn.classList.remove('active');
                        listViewBtn.classList.add('active');
                        
                        // Tercihi localStorage'a kaydet
                        localStorage.setItem('viewPreference', 'list');
                    });
                    
                    // Sayfa yüklendiğinde kaydedilmiş tercihi uygula
                    const viewPreference = localStorage.getItem('viewPreference');
                    if (viewPreference === 'list') {
                        listViewBtn.click();
                    }
                }
            },
            
            // Otomatik kapanan uyarılar
            initAutoHideAlerts: function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        const fadeEffect = setInterval(() => {
                            if (!alert.style.opacity) {
                                alert.style.opacity = 1;
                            }
                            if (alert.style.opacity > 0) {
                                alert.style.opacity -= 0.1;
                            } else {
                                clearInterval(fadeEffect);
                                alert.style.display = 'none';
                            }
                        }, 25);
                    }, 5000);
                });
            },
            
            // Küçük resim galerisi
            initThumbnailGallery: function() {
                const galleryThumbnails = document.querySelectorAll('.thumbnail-img');
                galleryThumbnails.forEach(thumb => {
                    thumb.addEventListener('click', function() {
                        const slideIndex = this.getAttribute('data-bs-slide-to');
                        const carousel = document.getElementById('propertyCarousel');
                        
                        if (carousel) {
                            const bsCarousel = bootstrap.Carousel.getInstance(carousel);
                            if (bsCarousel) {
                                bsCarousel.to(parseInt(slideIndex));
                            }
                        }
                    });
                });
            },
            
            // Sayfa başına dön butonu
            initScrollToTop: function() {
                const scrollTopBtn = document.getElementById('scrollTop');
                if (scrollTopBtn) {
                    window.addEventListener('scroll', function() {
                        if (window.pageYOffset > 200) {
                            scrollTopBtn.style.opacity = '1';
                            scrollTopBtn.style.visibility = 'visible';
                        } else {
                            scrollTopBtn.style.opacity = '0';
                            scrollTopBtn.style.visibility = 'hidden';
                        }
                    });
                    
                    scrollTopBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    });
                }
            }
        },
        
        // Form işlemleri (ajax-form-handler.js)
        Forms: {
            init: function() {
                this.initFormValidation();
                this.initContactForm();
                this.initSearchForm();
            },
            
            // Form doğrulama
            initFormValidation: function() {
                const forms = document.querySelectorAll('form:not([novalidate])');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        
                        form.classList.add('was-validated');
                    }, false);
                });
            },
            
            // İletişim formu
            initContactForm: function() {
                const contactForm = document.getElementById('contactForm');
                if (contactForm) {
                    contactForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        if (!contactForm.checkValidity()) {
                            contactForm.classList.add('was-validated');
                            return;
                        }
                        
                        // Form verilerini al
                        const formData = new FormData(contactForm);
                        
                        // Submit butonunu devre dışı bırak
                        const submitBtn = contactForm.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Gönderiliyor...';
                        }
                        
                        // AJAX isteği
                        fetch('handlers/contact-handler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Başarılı mesaj
                                AksuSite.Forms.showFormSuccess(data.message || 'Mesajınız başarıyla gönderildi.');
                                contactForm.reset();
                                contactForm.classList.remove('was-validated');
                            } else {
                                // Hata mesajı
                                AksuSite.Forms.showFormErrors(data.errors || ['Bir hata oluştu. Lütfen daha sonra tekrar deneyin.']);
                            }
                        })
                        .catch(error => {
                            // Hata mesajı
                            AksuSite.Forms.showFormErrors(['Bir bağlantı hatası oluştu. Lütfen daha sonra tekrar deneyin.']);
                            console.error('Form gönderimi hatası:', error);
                        })
                        .finally(() => {
                            // Submit butonunu normale döndür
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Gönder';
                            }
                        });
                    });
                }
            },
            
            // Arama formu
            initSearchForm: function() {
                const searchForm = document.getElementById('searchForm');
                if (searchForm) {
                    // Sayfa yüklendiğinde URL parametrelerini forma yansıt
                    const urlParams = new URLSearchParams(window.location.search);
                    
                    urlParams.forEach((value, key) => {
                        const formElement = searchForm.elements[key];
                        if (formElement) {
                            if (formElement.type === 'checkbox') {
                                formElement.checked = value === '1' || value === 'true';
                            } else {
                                formElement.value = value;
                            }
                        }
                    });
                    
                    // Form gönderildiğinde filtreleme yap
                    searchForm.addEventListener('submit', function(e) {
                        // Standard form submission is fine for search
                    });
                    
                    // Listeleme türü değiştiğinde fiyat alanlarını güncelle
                    const listingTypeRadios = searchForm.querySelectorAll('input[name="listing_type"]');
                    if (listingTypeRadios.length) {
                        listingTypeRadios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                const salePriceContainer = document.getElementById('sale-price-container');
                                const rentPriceContainer = document.getElementById('rent-price-container');
                                
                                if (this.value === 'sale') {
                                    if (salePriceContainer) salePriceContainer.style.display = 'block';
                                    if (rentPriceContainer) rentPriceContainer.style.display = 'none';
                                } else if (this.value === 'rent') {
                                    if (salePriceContainer) salePriceContainer.style.display = 'none';
                                    if (rentPriceContainer) rentPriceContainer.style.display = 'block';
                                } else {
                                    if (salePriceContainer) salePriceContainer.style.display = 'block';
                                    if (rentPriceContainer) rentPriceContainer.style.display = 'block';
                                }
                            });
                        });
                        
                        // Sayfa yüklendiğinde mevcut seçime göre durumu ayarla
                        const selectedType = document.querySelector('input[name="listing_type"]:checked');
                        if (selectedType) {
                            selectedType.dispatchEvent(new Event('change'));
                        }
                    }
                }
            },
            
            // Hata mesajlarını göster
            showFormErrors: function(errors, formId = 'contactForm') {
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
            },
            
            // Başarı mesajı göster
            showFormSuccess: function(message, formId = 'contactForm') {
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
        },
        
        // Harita fonksiyonları (map-functions-optimized.js)
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
                this.initAllMaps();
                
                // Sayfa boyutu değiştiğinde haritaları güncelle
                window.addEventListener('resize', function() {
                    // Tüm harita örneklerini yeniden boyutlandır
                    Object.keys(AksuSite.Maps.instances).forEach(id => {
                        if (AksuSite.Maps.instances[id]) {
                            AksuSite.Maps.instances[id].invalidateSize();
                        }
                    });
                });
                
                // Tab değişim dinleyicilerini ayarla
                this.setupMapTabListeners();
            },
            
            // Sayfadaki tüm haritaları başlat
            initAllMaps: function() {
                // Admin haritası
                const adminMap = document.getElementById('map-container');
                if (adminMap) {
                    this.initMap('map-container', {
                        draggable: true
                    });
                }
                
                // İlan detay sayfası haritası
                const propertyMap = document.getElementById('property-location-map');
                if (propertyMap) {
                    this.initMap('property-location-map', {
                        clickable: false
                    });
                }
                
                // Arama sonuçları haritası
                const searchMap = document.getElementById('search-map');
                if (searchMap && typeof propertyData !== 'undefined') {
                    const map = this.initMap('search-map', {
                        clickable: false
                    });
                    
                    if (map) {
                        this.addPropertyMarkers('search-map', propertyData);
                    }
                }
                
                // Ana sayfa haritası
                const homeMap = document.getElementById('property-map');
                if (homeMap && typeof propertyData !== 'undefined') {
                    const map = this.initMap('property-map', {
                        clickable: false
                    });
                    
                    if (map) {
                        this.addPropertyMarkers('property-map', propertyData);
                    }
                }
            },
            
            // Tab değiştiğinde haritaları yeniden yükle
            setupMapTabListeners: function() {
                const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
                
                tabLinks.forEach(function(link) {
                    link.addEventListener('shown.bs.tab', function(e) {
                        const target = e.target.getAttribute('href');
                        
                        // Location tab'ına geçildiğinde haritayı yeniden yükle
                        if (target === '#location') {
                            if (document.getElementById('map-container')) {
                                // Biraz gecikme ile başlat (DOM yüklenmesi için)
                                setTimeout(() => {
                                    AksuSite.Maps.initMap('map-container', {
                                        draggable: true
                                    });
                                }, 100);
                            }
                        }
                    });
                });
            },
            
            // Harita başlatma
            initMap: function(containerId, options = {}) {
                // DOM elementini kontrol et
                const container = document.getElementById(containerId);
                if (!container) {
                    console.error(`Harita konteyneri bulunamadı: #${containerId}`);
                    return null;
                }
                
                // Eğer konteyner görünür değilse, yüklemeyi geciktir
                if (container.offsetWidth === 0 || container.offsetHeight === 0) {
                    setTimeout(() => {
                        AksuSite.Maps.initMap(containerId, options);
                    }, 300);
                    return null;
                }
                
                // Halihazırda başlatılmış haritayı temizle
                if (AksuSite.Maps.instances[containerId]) {
                    AksuSite.Maps.instances[containerId].remove();
                    delete AksuSite.Maps.instances[containerId];
                }
                
                // Ayarları birleştir
                const settings = Object.assign({}, {
                    latInput: options.latInput || document.getElementById('latitude'),
                    lngInput: options.lngInput || document.getElementById('longitude'),
                    initialLat: AksuSite.Maps.config.defaultLat,
                    initialLng: AksuSite.Maps.config.defaultLng,
                    initialZoom: AksuSite.Maps.config.defaultZoom,
                    draggable: options.draggable || false,
                    clickable: options.clickable !== false, // Varsayılan true
                    onMarkerClick: options.onMarkerClick || null,
                    onMapClick: options.onMapClick || null
                });
                
                // Başlangıç konumunu ayarla
                let startLat = settings.initialLat;
                let startLng = settings.initialLng;
                let startZoom = settings.initialZoom;
                
                // Input değerleri varsa ve geçerliyse
                if (settings.latInput && settings.latInput.value && 
                    settings.lngInput && settings.lngInput.value) {
                    const lat = parseFloat(settings.latInput.value);
                    const lng = parseFloat(settings.lngInput.value);
                    
                    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                        startLat = lat;
                        startLng = lng;
                        startZoom = AksuSite.Maps.config.detailZoom;
                    }
                } 
                // Data özniteliklerini kontrol et
                else if (container.dataset.lat && container.dataset.lng) {
                    const lat = parseFloat(container.dataset.lat);
                    const lng = parseFloat(container.dataset.lng);
                    
                    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                        startLat = lat;
                        startLng = lng;
                        startZoom = AksuSite.Maps.config.detailZoom;
                    }
                }
                
                // Haritayı oluştur
                const map = L.map(containerId, {
                    zoomControl: true,
                    scrollWheelZoom: false // Kullanıcı tecrübesi için kapalı, tıklayınca aktifleşir
                }).setView([startLat, startLng], startZoom);
                
                // Aşağıdaki satır haritanın tekerlek ile yakınlaştırılabilmesi için
                map.once('focus', function() { map.scrollWheelZoom.enable(); });
                
                // Harita katmanını ekle
                L.tileLayer(AksuSite.Maps.config.tileUrl, {
                    attribution: AksuSite.Maps.config.tileAttribution,
                    maxZoom: AksuSite.Maps.config.maxZoom
                }).addTo(map);
                
                // İlk marker'ı ekle (varsa koordinatlar)
                let marker = null;
                if (startZoom > AksuSite.Maps.config.defaultZoom) {
                    marker = AksuSite.Maps.addMarker([startLat, startLng], map, settings.draggable);
                    
                    // Marker sürüklendiğinde
                    if (settings.draggable && marker) {
                        marker.on('dragend', function(e) {
                            const position = marker.getLatLng();
                            
                            // Input değerlerini güncelle
                            if (settings.latInput && settings.lngInput) {
                                settings.latInput.value = position.lat.toFixed(6);
                                settings.lngInput.value = position.lng.toFixed(6);
                            }
                            
                            // Özel callback
                            if (typeof settings.onMarkerDrag === 'function') {
                                settings.onMarkerDrag(position);
                            }
                        });
                    }
                }
                
                // Haritaya tıklama olayı
                if (settings.clickable) {
                    map.on('click', function(e) {
                        // Mevcut konumu al
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;
                        
                        // Input değerlerini güncelle
                        if (settings.latInput && settings.lngInput) {
                            settings.latInput.value = lat.toFixed(6);
                            settings.lngInput.value = lng.toFixed(6);
                        }
                        
                        // Marker güncelle
                        if (marker) {
                            marker.setLatLng(e.latlng);
                        } else {
                            marker = AksuSite.Maps.addMarker(e.latlng, map, settings.draggable);
                            
                            // Yeni eklenen marker için sürükleme olayı
                            if (settings.draggable) {
                                marker.on('dragend', function(e) {
                                    const position = marker.getLatLng();
                                    
                                    if (settings.latInput && settings.lngInput) {
                                        settings.latInput.value = position.lat.toFixed(6);
                                        settings.lngInput.value = position.lng.toFixed(6);
                                    }
                                    
                                    if (typeof settings.onMarkerDrag === 'function') {
                                        settings.onMarkerDrag(position);
                                    }
                                });
                            }
                        }
                        
                        // Özel callback
                        if (typeof settings.onMapClick === 'function') {
                            settings.onMapClick(e.latlng, marker);
                        }
                    });
                }
                
                // Harita yüklendiğinde boyutu düzelt (responsive sorunlarına karşı)
                setTimeout(function() {
                    map.invalidateSize();
                }, 100);
                
                // Harita örneğini ve marker'ı sakla
                AksuSite.Maps.instances[containerId] = map;
                AksuSite.Maps.markers[containerId] = marker ? [marker] : [];
                
                return map;
            },
            
            // Marker oluştur
            addMarker: function(latlng, map, draggable = false) {                // Marker icon
                const markerIcon = L.divIcon({
                    className: 'property-marker',
                    html: `
                        <div class="marker-container">
                            <div class="marker-house-icon">
                                <i class="bi bi-house-fill"></i>
                            </div>
                            <div class="marker-price-label">Konum</div>
                        </div>
                    `,
                    iconSize: [120, 60],
                    iconAnchor: [60, 60]
                });
                
                // Marker ekle
                return L.marker(latlng, {
                    icon: markerIcon,
                    draggable: draggable
                }).addTo(map);
            },
            
            // Mülk marker'larını ekle
            addPropertyMarkers: function(mapId, properties) {
                const map = AksuSite.Maps.instances[mapId];
                
                if (!map || !properties || !properties.length) {
                    console.error('Harita veya mülk verileri bulunamadı');
                    return;
                }
                
                // Önceki marker'ları temizle
                if (AksuSite.Maps.markers[mapId] && AksuSite.Maps.markers[mapId].length) {
                    AksuSite.Maps.markers[mapId].forEach(marker => map.removeLayer(marker));
                }
                
                // Yeni marker'ları saklamak için dizi
                AksuSite.Maps.markers[mapId] = [];
                
                // Marker sınırlarını takip etmek için
                const bounds = L.latLngBounds();
                let hasValidMarkers = false;
                
                // Her mülk için marker ekle
                properties.forEach(property => {
                    // Geçerli koordinatlar yoksa atla
                    if (!property.latitude || !property.longitude) {
                        return;
                    }
                    
                    // Koordinatları doğru formata çevir
                    const lat = parseFloat(property.latitude);
                    const lng = parseFloat(property.longitude);
                    
                    // Geçersiz koordinat kontrolü
                    if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) {
                        return;
                    }
                    
                    // Fiyat bilgisini formatla
                    let priceText = '';
                    let markerClass = property.rent_price > 0 ? 'marker-price-rent' : 'marker-price-sale';
                    
                    if (property.rent_price && property.rent_price > 0) {
                        priceText = `${AksuSite.Maps.formatPrice(property.rent_price)} ₺/ay`;
                    } else if (property.sale_price && property.sale_price > 0) {
                        priceText = `${AksuSite.Maps.formatPrice(property.sale_price)} ₺`;
                    } else {
                        priceText = 'Fiyat Belirtilmemiş';
                    }
                      // Özel ikon oluştur
                    const markerIcon = L.divIcon({
                        className: 'property-marker',
                        html: `
                            <div class="marker-container">
                                <div class="marker-house-icon ${property.featured ? 'featured' : ''}">
                                    <i class="bi bi-house-fill"></i>
                                </div>
                                <div class="marker-price-label ${property.featured ? 'featured' : ''}">${priceText}</div>
                            </div>
                        `,
                        iconSize: [120, 60],
                        iconAnchor: [60, 60],
                        popupAnchor: [0, -60]
                    });
                    
                    // Marker oluştur ve haritaya ekle
                    const marker = L.marker([lat, lng], {
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
                    
                    // Marker'ı diziye ekle
                    AksuSite.Maps.markers[mapId].push(marker);
                    
                    // Koordinatı sınırlara ekle
                    bounds.extend([lat, lng]);
                    hasValidMarkers = true;
                });
                
                // Tüm marker'ları haritaya sığdır
                if (hasValidMarkers) {
                    map.fitBounds(bounds.pad(0.1));
                }
            },
            
            // Fiyat formatla
            formatPrice: function(price) {
                // Fiyat değerini güvenli bir şekilde doğrula
                if (typeof price !== 'number' || isNaN(price)) {
                    price = 0;
                }
                
                return new Intl.NumberFormat('tr-TR').format(price);
            }
        },
        
        // Dosya Yükleme (image-uploader.js)
        FileUpload: {
            // Ayarlar
            settings: {
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
            },
            
            // Silinen görselleri takip
            deletedImages: [],
            existingImages: [],
            
            init: function() {
                // DOM elementlerini al
                this.dropArea = document.querySelector(this.settings.dropAreaSelector);
                this.fileInput = document.querySelector(this.settings.fileInputSelector);
                this.previewsContainer = document.querySelector(this.settings.previewSelector);
                this.mainImageSelect = document.querySelector(this.settings.mainImageSelectSelector);
                this.mainImageContainer = document.querySelector(this.settings.mainImageContainerSelector);
                this.selectBtn = document.querySelector(this.settings.selectBtnSelector);
                
                // Düzenleme modu mu?
                this.settings.editMode = window.location.href.includes('edit.php');
                
                if (this.fileInput && (this.dropArea || this.previewsContainer)) {
                    this.initUploader();
                }
            },
            
            // Dosya yükleme işlevini başlat
            initUploader: function() {
                // Silinen görseller için input oluştur (editMode için)
                if (this.settings.editMode) {
                    this.deletedImagesInput = document.createElement('input');
                    this.deletedImagesInput.type = 'hidden';
                    this.deletedImagesInput.name = this.settings.deleteInputName;
                    const form = document.querySelector('form');
                    if (form) {
                        form.appendChild(this.deletedImagesInput);
                    }
                    
                    // Mevcut görselleri işle
                    const existingImgs = document.querySelectorAll(this.settings.existingImagesSelector);
                    existingImgs.forEach(img => {
                        // Silme butonu event dinleyicisi
                        const removeBtn = img.querySelector(this.settings.removeBtnSelector);
                        if (removeBtn) {
                            removeBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                const imgId = img.dataset.imageId;
                                if (imgId) {
                                    this.deletedImages.push(imgId);
                                    this.deletedImagesInput.value = this.deletedImages.join(',');
                                    img.classList.add('d-none');
                                    
                                    // Ana görsel seçimini güncelle
                                    this.updateMainImageSelect();
                                }
                            });
                        }
                        
                        // Mevcut görseli diziye ekle
                        if (img.dataset.imageId) {
                            this.existingImages.push({
                                id: img.dataset.imageId,
                                path: img.dataset.imagePath,
                                isMain: img.dataset.isMain === '1'
                            });
                        }
                    });
                }
                
                // Seçme butonu
                if (this.selectBtn) {
                    this.selectBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.fileInput.click();
                    });
                }
                
                // Dosya seçimi
                if (this.fileInput) {
                    this.fileInput.addEventListener('change', (e) => {
                        if (e.target.files && e.target.files.length > 0) {
                            this.handleFiles(e.target.files);
                        }
                    });
                }
                
                // Sürükle bırak alanı
                if (this.dropArea) {
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
                }
                
                // Ana görsel seçimini güncelle
                this.updateMainImageSelect();
            },
            
            // Dosyaları işle
            handleFiles: function(files) {
                if (!files || files.length === 0 || !this.previewsContainer) return;
                
                // Maksimum görsel sayısını kontrol et
                const maxFiles = this.settings.maxFiles;
                const currentImageCount = this.previewsContainer.querySelectorAll('.image-preview-item').length;
                const remainingSlots = maxFiles - currentImageCount;
                
                if (remainingSlots <= 0) {
                    this.showMessage(`Maksimum ${maxFiles} görsel yükleyebilirsiniz.`, 'warning');
                    return;
                }
                
                // Dosya sayısı kontrolü
                const processCount = Math.min(files.length, remainingSlots);
                
                for (let i = 0; i < processCount; i++) {
                    const file = files[i];
                    
                    // Dosya türü kontrolü
                    if (!this.settings.allowedTypes.includes(file.type)) {
                        this.showMessage(`"${file.name}" desteklenmeyen dosya formatı. Sadece JPG ve PNG kabul edilir.`, 'warning');
                        continue;
                    }
                    
                    // Dosya boyutu kontrolü
                    if (file.size > this.settings.maxFileSize) {
                        this.showMessage(`"${file.name}" dosyası çok büyük. Maksimum 10MB izin verilir.`, 'warning');
                        continue;
                    }
                    
                    // Dosya önizleme
                    const reader = new FileReader();
                    reader.onload = ((file, index) => {
                        return (e) => {
                            this.createPreview(e.target.result, file, index);
                        };
                    })(file, i);
                    reader.readAsDataURL(file);
                }
                
                // Ana görsel seçimini güncelle
                this.updateMainImageSelect();
                
                // Ana görsel seçiciyi göster
                if (this.previewsContainer.children.length > 0 && this.mainImageContainer) {
                    this.mainImageContainer.classList.remove('d-none');
                }
            },
            
            // Önizleme oluştur
            createPreview: function(src, file, index) {
                if (!this.previewsContainer) return;
                
                // Önizleme container'ı
                const preview = document.createElement('div');
                preview.className = 'image-preview-item position-relative border rounded overflow-hidden m-2';
                preview.dataset.fileName = file.name;
                preview.dataset.fileIndex = index;
                preview.style.width = '150px';
                preview.style.height = '100px';
                
                // Görsel
                const img = document.createElement('img');
                img.src = src;
                img.className = 'w-100 h-100 object-fit-cover';
                img.alt = file.name;
                preview.appendChild(img);
                
                // Dosya adı
                const filename = document.createElement('div');
                filename.className = 'bg-dark bg-opacity-50 text-white small p-1 position-absolute bottom-0 w-100 text-truncate';
                filename.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' + file.name.split('.').pop() : file.name;
                preview.appendChild(filename);
                
                // Silme butonu
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center';
                removeBtn.style.width = '20px';
                removeBtn.style.height = '20px';
                removeBtn.innerHTML = '×';
                removeBtn.addEventListener('click', () => {
                    preview.remove();
                    this.updateMainImageSelect();
                    
                    // Tüm görüntüler kaldırıldıysa ana görüntü seçiciyi gizle
                    if (this.previewsContainer.children.length === 0 && this.mainImageContainer) {
                        this.mainImageContainer.classList.add('d-none');
                    }
                });
                preview.appendChild(removeBtn);
                
                this.previewsContainer.appendChild(preview);
            },
            
            // Ana görsel seçimini güncelle
            updateMainImageSelect: function() {
                if (!this.mainImageSelect) return;
                
                // Seçenekelri temizle
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
                const previews = this.previewsContainer ? this.previewsContainer.querySelectorAll('.image-preview-item') : [];
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
                
                // En az 1 seçenek yoksa, container'ı gizle
                if (this.mainImageSelect.options.length === 0 && this.mainImageContainer) {
                    this.mainImageContainer.classList.add('d-none');
                } else if (this.mainImageContainer) {
                    this.mainImageContainer.classList.remove('d-none');
                }
            },
            
            // Mesaj göster
            showMessage: function(message, type = 'error') {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show mt-2`;
                alertContainer.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Form veya yükleme alanı bul
                const targetElement = this.dropArea || this.fileInput || document.querySelector('form');
                if (targetElement) {
                    // Ebeveyn element bul
                    const parentElement = targetElement.parentNode;
                    if (parentElement) {
                        parentElement.insertBefore(alertContainer, targetElement.nextSibling);
                    }
                }
                
                // 5 saniye sonra otomatik kapat
                setTimeout(() => {
                    alertContainer.classList.remove('show');
                    setTimeout(() => alertContainer.remove(), 150);
                }, 5000);
            }
        }
    };
    
    // Siteyi başlat
    AksuSite.init();
    
    // Global olarak erişilebilir yap (gerekirse)
    window.AksuSite = AksuSite;
    
})();
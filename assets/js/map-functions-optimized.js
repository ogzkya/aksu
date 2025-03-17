/* Bu kodu /assets/js/map-functions-optimized.js olarak kaydedin */

/**
 * Aksu Emlak - Optimize Edilmiş ve Güvenilir Harita Fonksiyonları
 */

// İlk Leaflet kontrolü
if (typeof L === 'undefined') {
    console.error('Leaflet kütüphanesi yüklenemedi! Lütfen leaflet.js dosyasını ekleyin.');
}

// Global harita nesnesi
let aksyMap = {
    instances: {}, // Harita örneklerini sakla
    markers: {},   // Marker gruplarını sakla
    config: {
        defaultLat: 39.1,
        defaultLng: 35.6,
        defaultZoom: 6,
        detailZoom: 15,
        maxZoom: 19,
        tileUrl: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        tileAttribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }
};

/**
 * Harita başlatma
 * @param {string} containerId - Harita konteyneri ID
 * @param {Object} options - Seçenekler
 */
function initMap(containerId, options = {}) {
    // DOM elementini kontrol et
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Harita konteyneri bulunamadı: #${containerId}`);
        return null;
    }
    
    // Eğer konteyner görünür değilse, yüklemeyi geciktir
    if (container.offsetWidth === 0 || container.offsetHeight === 0) {
        setTimeout(() => {
            initMap(containerId, options);
        }, 300);
        return null;
    }
    
    // Halihazırda başlatılmış haritayı temizle
    if (aksyMap.instances[containerId]) {
        aksyMap.instances[containerId].remove();
        delete aksyMap.instances[containerId];
    }
    
    // Ayarları birleştir
    const settings = Object.assign({}, {
        latInput: options.latInput || document.getElementById('latitude'),
        lngInput: options.lngInput || document.getElementById('longitude'),
        initialLat: aksyMap.config.defaultLat,
        initialLng: aksyMap.config.defaultLng,
        initialZoom: aksyMap.config.defaultZoom,
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
            startZoom = aksyMap.config.detailZoom;
        }
    } 
    // Data özniteliklerini kontrol et
    else if (container.dataset.lat && container.dataset.lng) {
        const lat = parseFloat(container.dataset.lat);
        const lng = parseFloat(container.dataset.lng);
        
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            startLat = lat;
            startLng = lng;
            startZoom = aksyMap.config.detailZoom;
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
    L.tileLayer(aksyMap.config.tileUrl, {
        attribution: aksyMap.config.tileAttribution,
        maxZoom: aksyMap.config.maxZoom
    }).addTo(map);
    
    // İlk marker'ı ekle (varsa koordinatlar)
    let marker = null;
    if (startZoom > aksyMap.config.defaultZoom) {
        marker = L.marker([startLat, startLng], {
            draggable: settings.draggable
        }).addTo(map);
        
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
                marker = L.marker(e.latlng, {
                    draggable: settings.draggable
                }).addTo(map);
                
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
    aksyMap.instances[containerId] = map;
    aksyMap.markers[containerId] = marker ? [marker] : [];
    
    return map;
}

/**
 * Mülk marker'larını ekle
 * @param {string} mapId - Harita konteyneri ID
 * @param {Array} properties - Mülk verileri dizisi
 */
function addPropertyMarkers(mapId, properties) {
    const map = aksyMap.instances[mapId];
    
    if (!map || !properties || !properties.length) {
        console.error('Harita veya mülk verileri bulunamadı');
        return;
    }
    
    // Önceki marker'ları temizle
    if (aksyMap.markers[mapId] && aksyMap.markers[mapId].length) {
        aksyMap.markers[mapId].forEach(marker => map.removeLayer(marker));
    }
    
    // Yeni marker'ları saklamak için dizi
    aksyMap.markers[mapId] = [];
    
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
            priceText = `${formatPrice(property.rent_price)} ₺/ay`;
        } else if (property.sale_price && property.sale_price > 0) {
            priceText = `${formatPrice(property.sale_price)} ₺`;
        } else {
            priceText = 'Fiyat Belirtilmemiş';
        }
        
        // Özel ikon oluştur
        const markerIcon = L.divIcon({
            className: 'property-marker',
            html: `
                <div class="marker-container">
                    <div class="marker-pin ${property.featured ? 'featured' : ''}">
                        <i class="bi bi-house-fill"></i>
                    </div>
                    <div class="marker-price ${markerClass}">${priceText}</div>
                </div>
            `,
            iconSize: [80, 60],
            iconAnchor: [40, 60],
            popupAnchor: [0, -60]
        });
        
        // Marker oluştur ve haritaya ekle
        const marker = L.marker([lat, lng], {
            icon: markerIcon
        }).addTo(map);
        
        // Popup içeriği
        const popupContent = `
            <div class="map-popup">
                <img src="${property.main_image || '/assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                <h5 class="popup-title">${property.title}</h5>
                <p class="popup-price">${priceText}</p>
                <a href="/listing.php?id=${property.id}" class="btn btn-primary w-100">Detaylar</a>
            </div>
        `;
        
        // Popup ekle
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'property-popup'
        });
        
        // Marker'ı diziye ekle
        aksyMap.markers[mapId].push(marker);
        
        // Koordinatı sınırlara ekle
        bounds.extend([lat, lng]);
        hasValidMarkers = true;
    });
    
    // Tüm marker'ları haritaya sığdır
    if (hasValidMarkers) {
        map.fitBounds(bounds.pad(0.1));
    }
}

/**
 * Fiyatı formatlı şekilde göster
 * @param {number} price - Formatlanacak fiyat
 * @returns {string} - Formatlanmış fiyat
 */
function formatPrice(price) {
    // Fiyat değerini güvenli bir şekilde doğrula
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    
    return new Intl.NumberFormat('tr-TR').format(price);
}

/**
 * Sayfadaki tüm haritaları başlat
 */
function initAllMaps() {
    // Admin haritası
    const adminMap = document.getElementById('map-container');
    if (adminMap) {
        initMap('map-container', {
            draggable: true
        });
    }
    
    // İlan detay sayfası haritası
    const propertyMap = document.getElementById('property-location-map');
    if (propertyMap) {
        initMap('property-location-map', {
            clickable: false
        });
    }
    
    // Arama sonuçları haritası
    const searchMap = document.getElementById('search-map');
    if (searchMap && typeof propertyData !== 'undefined') {
        const map = initMap('search-map', {
            clickable: false
        });
        
        if (map) {
            addPropertyMarkers('search-map', propertyData);
        }
    }
    
    // Ana sayfa haritası
    const homeMap = document.getElementById('property-map');
    if (homeMap && typeof propertyData !== 'undefined') {
        const map = initMap('property-map', {
            clickable: false
        });
        
        if (map) {
            addPropertyMarkers('property-map', propertyData);
        }
    }
}

// Tab değiştiğinde haritaları yeniden yükle
function setupMapTabListeners() {
    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    
    tabLinks.forEach(function(link) {
        link.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('href');
            
            // Location tab'ına geçildiğinde haritayı yeniden yükle
            if (target === '#location') {
                if (document.getElementById('map-container')) {
                    // Biraz gecikme ile başlat (DOM yüklenmesi için)
                    setTimeout(() => {
                        initMap('map-container', {
                            draggable: true
                        });
                    }, 100);
                }
            }
        });
    });
}

// Sayfa yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', function() {
    // Tüm haritaları başlat
    initAllMaps();
    
    // Tab değişim dinleyicilerini ayarla
    setupMapTabListeners();
    
    // Sayfa boyutu değiştiğinde haritaları güncelle
    window.addEventListener('resize', function() {
        // Tüm harita örneklerini yeniden boyutlandır
        Object.keys(aksyMap.instances).forEach(id => {
            if (aksyMap.instances[id]) {
                aksyMap.instances[id].invalidateSize();
            }
        });
    });
});
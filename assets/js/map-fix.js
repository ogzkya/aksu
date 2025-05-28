/**
 * Aksu Emlak - İyileştirilmiş Harita İşlemleri
 * Bu script, harita işaretleme sorununu çözer ve tüm harita işlevlerini birleştirir.
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAllMaps();
});

/**
 * Sayfadaki tüm haritaları başlat
 */
function initializeAllMaps() {
    // Admin haritası (ilan ekleme/düzenleme)
    initializeMapWithMarker('map-container');
    
    // Diğer haritalar
    initializeStaticMap('property-location-map');
    initializeStaticMap('property-map');
    initializeStaticMap('search-map');
}

/**
 * İşaretleyici eklenebilen interaktif harita başlatma (admin paneli için)
 * @param {string} containerId - Harita konteyner ID'si
 */
function initializeMapWithMarker(containerId) {
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) return null;
    
    // Harita görünür değilse yüklemeyi ertele
    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
        setTimeout(() => initializeMapWithMarker(containerId), 200);
        return null;
    }
    
    // Konteyner zaten bir harita içeriyorsa temizle
    if (mapContainer._leaflet_id) {
        mapContainer._leaflet_id = null;
    }
    
    // Gerekli DOM elemanlarını bul
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    if (!latInput || !lngInput) {
        console.log("Harita koordinat inputları bulunamadı");
        return null;
    }
    
    // Başlangıç koordinatlarını belirle
    let initialLat = 39.1;
    let initialLng = 35.6;
    let initialZoom = 6;
    
    // Mevcut koordinatları kontrol et
    if (latInput.value && lngInput.value) {
        initialLat = parseFloat(latInput.value);
        initialLng = parseFloat(lngInput.value);
        
        // Geçerli koordinat var mı kontrol et (0,0 değilse)
        if (!isNaN(initialLat) && !isNaN(initialLng) && 
            !(initialLat === 0 && initialLng === 0)) {
            initialZoom = 15;
        }
    }
    
    console.log("Harita başlatılıyor:", containerId, initialLat, initialLng, initialZoom);
    
    // Haritayı oluştur
    const map = L.map(containerId).setView([initialLat, initialLng], initialZoom);
    
    // Tile layer ekle
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    
    // İlk marker'ı ekle (varsa koordinatlar)
    let marker;
    if (initialZoom > 6) {
        marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);
        
        // Sürüklendiğinde input değerlerini güncelle
        marker.on('dragend', function() {
            const position = marker.getLatLng();
            latInput.value = position.lat.toFixed(6);
            lngInput.value = position.lng.toFixed(6);
        });
    }
    
    // Haritaya tıklandığında marker ekle/güncelle
    map.on('click', function(e) {
        const latlng = e.latlng;
        
        // Input değerlerini güncelle
        latInput.value = latlng.lat.toFixed(6);
        lngInput.value = latlng.lng.toFixed(6);
        
        // Marker'ı güncelle veya oluştur
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, {
                draggable: true
            }).addTo(map);
            
            // Sürüklendiğinde input değerlerini güncelle
            marker.on('dragend', function() {
                const position = marker.getLatLng();
                latInput.value = position.lat.toFixed(6);
                lngInput.value = position.lng.toFixed(6);
            });
        }
    });
    
    // Harita boyutunu düzelt
    setTimeout(function() {
        map.invalidateSize();
    }, 100);
    
    return map;
}

/**
 * Statik harita başlatma (ilan görüntüleme sayfaları için)
 * @param {string} containerId - Harita konteyner ID'si
 */
function initializeStaticMap(containerId) {
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) return null;
    
    // Harita görünür değilse yüklemeyi ertele
    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
        setTimeout(() => initializeStaticMap(containerId), 200);
        return null;
    }
    
    // Konteyner zaten bir harita içeriyorsa temizle
    if (mapContainer._leaflet_id) {
        mapContainer._leaflet_id = null;
    }
    
    try {
        // Başlangıç koordinatlarını belirle
        let initialLat = 39.1;
        let initialLng = 35.6;
        let initialZoom = 6;
        
        // Data attribute'larını kontrol et
        if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
            initialLat = parseFloat(mapContainer.dataset.lat);
            initialLng = parseFloat(mapContainer.dataset.lng);
            initialZoom = 15;
        }
        
        // Haritayı oluştur
        const map = L.map(containerId).setView([initialLat, initialLng], initialZoom);
        
        // Tile layer ekle
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
        
        // İlk marker'ı ekle (varsa koordinatlar)
        if (initialZoom > 6) {
            const markerIcon = L.divIcon({
                className: 'property-marker',
                html: '<div class="marker-container"><div class="marker-pin"></div></div>',
                iconSize: [30, 42],
                iconAnchor: [15, 42]
            });
            
            L.marker([initialLat, initialLng], {
                icon: markerIcon
            }).addTo(map);
        }
        
        // Global propertyData varsa, işaretleyicileri ekle
        if (typeof window.propertyData !== 'undefined' && window.propertyData && window.propertyData.length) {
            addPropertyMarkers(map, window.propertyData);
        }
        
        // Harita boyutunu düzelt
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
        
        return map;
        
    } catch (error) {
        console.error("Harita başlatılırken hata:", error);
        return null;
    }
}

/**
 * Haritaya mülk işaretleyicileri ekle
 * @param {object} map - Leaflet harita nesnesi
 * @param {array} properties - Mülk verileri dizisi
 */
function addPropertyMarkers(map, properties) {
    if (!properties || !properties.length) return [];
    
    const markers = [];
    
    properties.forEach(function(property) {
        if (!property.latitude || !property.longitude) return;
        
        // Fiyat bilgisini formatla
        let priceText = '';
        let markerClass = 'marker-price-sale'; // Default: satılık için
        
        if (property.rent_price && property.rent_price > 0) {
            priceText = `${formatPrice(property.rent_price)} ₺/ay`;
            markerClass = 'marker-price-rent'; // Kiralık için
        } else if (property.sale_price && property.sale_price > 0) {
            priceText = `${formatPrice(property.sale_price)} ₺`;
        } else {
            priceText = 'Fiyat Belirtilmemiş';
        }
        
        // Özel marker ikonu
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
            iconAnchor: [40, 60]
        });
        
        // Marker ekle
        const marker = L.marker([property.latitude, property.longitude], {
            icon: markerIcon
        }).addTo(map);
        
        // Popup ekle
        if (property.id) {
            // Popup içeriği
            const popupContent = `
                <div class="map-popup">
                    <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title || ''}">
                    <h5 class="popup-title">${property.title || 'İlan Detayı'}</h5>
                    <p class="popup-price">${priceText}</p>
                    <a href="listing.php?id=${property.id}" class="btn btn-primary w-100">Detaylar</a>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'property-popup'
            });
        }
        
        markers.push(marker);
    });
    
    // Tüm marker'ları haritaya sığdır
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    
    return markers;
}

/**
 * Fiyatı formatlı şekilde göster
 * @param {number} price - Formatlanacak fiyat
 * @returns {string} - Formatlanmış fiyat
 */
function formatPrice(price) {
    if (typeof price !== 'number' || isNaN(price)) {
        price = 0;
    }
    return new Intl.NumberFormat('tr-TR').format(price);
}

/**
 * Tab değiştiğinde haritayı yeniden yükle
 */
document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function(tab) {
    tab.addEventListener('shown.bs.tab', function(e) {
        // Location tab'ına geçildiğinde haritayı yeniden yükle
        if (e.target.getAttribute('href') === '#location') {
            setTimeout(() => {
                initializeMapWithMarker('map-container');
            }, 100);
        }
    });
});
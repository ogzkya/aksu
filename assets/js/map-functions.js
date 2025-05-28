// Bu kodu /assets/js/map-functions.js olarak kaydedin
// Leaflet harita fonksiyonları için merkezi bir dosya

/**
 * Harita başlatma ve marker ekleme
 * @param {string} containerId - Harita konteyner ID'si
 * @param {object} options - Ek seçenekler (latInput, lngInput, initialMarker, etc.)
 */
function initializeMap(containerId, options = {}) {
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) return null;
    
    // Harita görünür değilse yüklemeyi ertele
    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
        setTimeout(() => initializeMap(containerId, options), 200);
        return null;
    }
    
    // Önceden başlatılmış haritayı temizle
    if (mapContainer._leaflet_id) {
        mapContainer._leaflet_id = null;
    }
    
    // Varsayılan değerler (Türkiye merkezi)
    let initialLat = 39.1;
    let initialLng = 35.6;
    let initialZoom = 6;
    
    // Hidden input elemanları
    const latInput = options.latInput || document.getElementById('latitude');
    const lngInput = options.lngInput || document.getElementById('longitude');
    
    // Var olan koordinatları kontrol et
    if (latInput && latInput.value && lngInput && lngInput.value) {
        initialLat = parseFloat(latInput.value);
        initialLng = parseFloat(lngInput.value);
        initialZoom = 15;
    } else if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
        initialLat = parseFloat(mapContainer.dataset.lat);
        initialLng = parseFloat(mapContainer.dataset.lng);
        initialZoom = 15;
    }
    
    // Haritayı oluştur
    const map = L.map(containerId).setView([initialLat, initialLng], initialZoom);
    
    // Harita katmanını ekle
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    
    // İlk marker'ı ekle (varsa)
    let marker;
    if (initialZoom > 6) {
        marker = L.marker([initialLat, initialLng], {
            draggable: options.draggable || false
        }).addTo(map);
    }
    
    // Haritaya tıklama olayını ekle (form için)
    if (latInput && lngInput) {
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // Gizli input alanlarını güncelle
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
            
            // Marker'ı güncelle veya oluştur
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, {
                    draggable: options.draggable || false
                }).addTo(map);
            }
        });
        
        // Marker sürükleme olayı (eğer draggable ise)
        if (marker && options.draggable) {
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latInput.value = position.lat.toFixed(6);
                lngInput.value = position.lng.toFixed(6);
            });
        }
    }
    
    // Harita yüklendiğinde boyutu düzelt
    setTimeout(function() {
        map.invalidateSize();
    }, 100);
    
    return map;
}

/**
 * Mülk marker'larını haritaya ekler
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
        let markerClass = 'marker-price-sale'; // Default: satılık
        
        if (property.rent_price && property.rent_price > 0) {
            priceText = `${formatPrice(property.rent_price)} ₺/ay`;
            markerClass = 'marker-price-rent'; // Kiralık
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
            iconAnchor: [40, 60],
            popupAnchor: [0, -60]
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
    return new Intl.NumberFormat('tr-TR').format(price);
}

// Sayfa yüklendiğinde haritaları başlat
document.addEventListener('DOMContentLoaded', function() {
    // Admin ekranında harita varsa başlat
    if (document.getElementById('map-container')) {
        initializeMap('map-container', {
            draggable: true
        });
    }
    
    // İlan detay sayfasında harita varsa başlat
    if (document.getElementById('property-location-map')) {
        initializeMap('property-location-map');
    }
    
    // Arama sonuçları haritası
    if (document.getElementById('search-map') && window.propertyData) {
        const searchMap = initializeMap('search-map');
        if (searchMap) {
            addPropertyMarkers(searchMap, window.propertyData);
        }
    }
    
    // Ana sayfa haritası
    if (document.getElementById('property-map') && window.propertyData) {
        const propertyMap = initializeMap('property-map');
        if (propertyMap) {
            addPropertyMarkers(propertyMap, window.propertyData);
        }
    }
    
    // Tab değiştiğinde haritaları yeniden yükle
    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(function(link) {
        link.addEventListener('shown.bs.tab', function(e) {
            if (e.target.getAttribute('href') === '#location') {
                if (document.getElementById('map-container')) {
                    initializeMap('map-container', {
                        draggable: true
                    });
                }
            }
        });
    });
});
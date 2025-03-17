// admin/assets/js/map-integration.js - Harita entegrasyonu düzeltmeleri

document.addEventListener('DOMContentLoaded', function() {
    // Harita yükleme sorununu çözmek için
    initializeAllMaps();
    
    // Harita konteynerini gözlemle, görünür hale geldiğinde yeniden yükle
    observeMapContainers();
    
    // Tab değişimlerini dinle
    setupTabChangeListeners();
});

/**
 * Sayfadaki tüm haritaları başlat
 */
function initializeAllMaps() {
    // Harita container'ları
    initializeMap('map-container');
    initializeMap('property-location-map');
    initializeMap('property-map');
    initializeMap('search-map');
}

/**
 * Tab değişimlerini dinle ve haritaları yeniden boyutlandır
 */
function setupTabChangeListeners() {
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
                        initializeMap(mapContainer.id);
                    }
                });
            }
        });
    });
}

/**
 * Harita konteyneri için MutationObserver ayarla
 * (Tab değiştiğinde haritanın doğru şekilde yüklenmesi için)
 */
function observeMapContainers() {
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
                                    initializeMap(mapContainer.id);
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
}

/**
 * Harita başlatma fonksiyonu
 */
function initializeMap(containerId) {
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
            loadLeafletDynamically();
            return null;
        }
        
        // Önceki haritayı temizle (eğer varsa)
        if (mapContainer._leaflet_id) {
            mapContainer._leaflet_id = null;
        }
        
        // Varsayılan olarak Türkiye merkezine odaklan
        let initialLat = 39.1;
        let initialLng = 35.6;
        let initialZoom = 6;
        
        // Veri attribute'larını kontrol et
        if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
            initialLat = parseFloat(mapContainer.dataset.lat);
            initialLng = parseFloat(mapContainer.dataset.lng);
            initialZoom = 15;
        }
        
        // Formdaki gizli alanları kontrol et
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        
        if (latInput && latInput.value && lngInput && lngInput.value) {
            initialLat = parseFloat(latInput.value);
            initialLng = parseFloat(lngInput.value);
            initialZoom = 15;
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
        
        // Tile layer ekle
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
        
        // Marker ekle (varsa koordinatlar)
        let marker;
        if (initialZoom > 6) {
            marker = addMarker([initialLat, initialLng], map);
        }
        
        // Click event ekle (form için)
        if (latInput && lngInput) {
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                // Gizli alanlara değerleri ata
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                
                // Marker'ı güncelle
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = addMarker(e.latlng, map);
                }
            });
        }
        
        // Property verisi ekle (varsa)
        if (window.propertyData && window.propertyData.length) {
            addPropertyMarkers(map, window.propertyData);
        }
        
        // Harita tam olarak yüklendiğinde boyutları yeniden hesapla
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
        
        return map;
        
    } catch (error) {
        console.error('Harita yüklenirken hata oluştu:', error);
        return null;
    }
}

/**
 * Marker oluştur
 */
function addMarker(latlng, map) {
    // Marker ikonu oluştur
    const markerIcon = L.divIcon({
        className: 'custom-marker-icon',
        html: '<div class="marker-container"><i class="bi bi-geo-alt-fill"></i></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });
    
    // Marker ekle
    return L.marker(latlng, {
        icon: markerIcon,
        draggable: false
    }).addTo(map);
}

/**
 * Emlak marker'larını ekle
 */
function addPropertyMarkers(map, properties) {
    if (!properties || !properties.length) return;
    
    const markers = [];
    
    properties.forEach(function(property) {
        if (!property.latitude || !property.longitude) return;
        
        // Fiyat formatını belirle
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
        
        // Marker ikonu oluştur
        const markerIcon = L.divIcon({
            className: 'property-marker',
            html: `
                <div class="marker-container">
                    <div class="marker-icon"><i class="bi bi-house-fill"></i></div>
                    <div class="marker-price ${markerClass}">${priceText}</div>
                </div>
            `,
            iconSize: [140, 60],
            iconAnchor: [70, 60],
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
    
    // Tüm marker'ları sığdır
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    
    return markers;
}

/**
 * Fiyat formatla
 */
function formatPrice(price) {
    return new Intl.NumberFormat('tr-TR').format(price);
}

/**
 * Leaflet'i dinamik olarak yükle (eğer sayfa yüklenirken yüklenemediyse)
 */
function loadLeafletDynamically() {
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
            setTimeout(initializeAllMaps, 100);
        };
        document.head.appendChild(script);
    }
}
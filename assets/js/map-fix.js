// assets/js/map-fix.js
document.addEventListener('DOMContentLoaded', function() {
    // Harita elementlerini bul
    const mapElements = document.querySelectorAll('#property-map, #property-location-map, #search-map, #map-container');
    
    mapElements.forEach(function(mapElement) {
        if (!mapElement) return;
        
        // Leaflet yüklendiyse haritayı başlat
        if (typeof L !== 'undefined') {
            initializeMap(mapElement);
        } else {
            console.error('Leaflet kütüphanesi yüklenemedi!');
        }
    });
    
    function initializeMap(mapElement) {
        const mapId = mapElement.id;
        console.log("Harita başlatılıyor:", mapId);
        
        // Başlangıç konumu (Türkiye merkezi)
        let startLat = 39.1;
        let startLng = 35.6;
        let startZoom = 6;
        
        // Data attribute'larını kontrol et
        if (mapElement.dataset.lat && mapElement.dataset.lng) {
            startLat = parseFloat(mapElement.dataset.lat);
            startLng = parseFloat(mapElement.dataset.lng);
            startZoom = 15;
        }
        
        // Haritayı başlat
        const map = L.map(mapId).setView([startLat, startLng], startZoom);
        
        // Tile layer ekle
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
        
        // Global propertyData varsa, markerları ekle
        if (typeof propertyData !== 'undefined' && Array.isArray(propertyData) && propertyData.length > 0) {
            addPropertyMarkers(map, propertyData);
        } else if (mapId === 'property-location-map' && typeof singlePropertyData !== 'undefined') {
            // Tek mülk detay sayfası için
            addSinglePropertyMarker(map, singlePropertyData);
        }
        
        // Admin panelinde konum seçme
        if (mapId === 'map-container') {
            enableLocationSelection(map);
        }
        
        return map;
    }
    
    // Mülk markerlarını ekleme
    function addPropertyMarkers(map, properties) {
        const markers = [];
        
        properties.forEach(function(property) {
            if (!property.latitude || !property.longitude) return;
            
            // Marker HTML'i
            const markerHtml = `
                <div class="marker-container">
                    <div class="marker-pin ${property.featured ? 'featured' : ''}">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                    <div class="marker-price marker-price-${property.rent_price > 0 ? 'rent' : 'sale'}">
                        ${formatPrice(property.rent_price > 0 ? property.rent_price : property.sale_price)} ${property.rent_price > 0 ? '₺/ay' : '₺'}
                    </div>
                </div>
            `;
            
            // Özel div icon
            const markerIcon = L.divIcon({
                className: 'property-marker',
                html: markerHtml,
                iconSize: [80, 60],
                iconAnchor: [40, 60]
            });
            
            // Marker ekle
            const marker = L.marker([property.latitude, property.longitude], {
                icon: markerIcon
            }).addTo(map);
            
            // Popup içeriği
            const popupContent = `
                <div class="map-popup">
                    <img src="${property.main_image || '/assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                    <h5 class="popup-title">${property.title}</h5>
                    <p class="popup-price">
                        ${property.rent_price > 0 ? `${formatPrice(property.rent_price)} ₺/ay` : `${formatPrice(property.sale_price)} ₺`}
                    </p>
                    <a href="/listing.php?id=${property.id}" class="btn btn-primary w-100">Detaylar</a>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'property-popup'
            });
            
            markers.push(marker);
        });
        
        // Tüm markerları haritaya sığdır
        if (markers.length > 0) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
        
        return markers;
    }
    
    // Tek mülk için marker ekle
    function addSinglePropertyMarker(map, property) {
        if (!property.latitude || !property.longitude) return;
        
        const markerHtml = `
            <div class="marker-container">
                <div class="marker-pin">
                    <i class="bi bi-house-door-fill"></i>
                </div>
            </div>
        `;
        
        const markerIcon = L.divIcon({
            className: 'property-marker',
            html: markerHtml,
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });
        
        const marker = L.marker([property.latitude, property.longitude], {
            icon: markerIcon
        }).addTo(map);
        
        marker.bindPopup(`<b>${property.title}</b><br>${property.address}`);
        
        return marker;
    }
    
    // Admin paneli için konum seçme
    function enableLocationSelection(map) {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        
        if (!latInput || !lngInput) return;
        
        let marker;
        
        // Mevcut koordinatları kontrol et
        if (latInput.value && lngInput.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);
                
                map.setView([lat, lng], 15);
                
                marker.on('dragend', function() {
                    const pos = marker.getLatLng();
                    latInput.value = pos.lat.toFixed(6);
                    lngInput.value = pos.lng.toFixed(6);
                });
            }
        }
        
        // Haritaya tıklandığında
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
            
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, {
                    draggable: true
                }).addTo(map);
                
                marker.on('dragend', function() {
                    const pos = marker.getLatLng();
                    latInput.value = pos.lat.toFixed(6);
                    lngInput.value = pos.lng.toFixed(6);
                });
            }
        });
    }
    
    function formatPrice(price) {
        return new Intl.NumberFormat('tr-TR').format(price);
    }
});
// admin/assets/js/admin.js - Eklenecek harita entegrasyonu kodu

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const sidebarToggleTop = document.querySelector('#sidebarToggleTop');
    const body = document.querySelector('body');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sidebar-toggled');
        });
    }
    
    if (sidebarToggleTop) {
        sidebarToggleTop.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sidebar-toggled');
        });
    }
    
    // Scroll to top button
    const scrollToTopButton = document.querySelector('.scroll-to-top');
    
    if (scrollToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                scrollToTopButton.style.display = 'flex';
            } else {
                scrollToTopButton.style.display = 'none';
            }
        });
        
        scrollToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Initialize tabs
    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    
    if (tabLinks.length > 0) {
        tabLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active from all tabs
                tabLinks.forEach(tab => tab.classList.remove('active'));
                
                // Add active to clicked tab
                this.classList.add('active');
                
                // Hide all tab panes
                const tabPanes = document.querySelectorAll('.tab-pane');
                tabPanes.forEach(pane => pane.classList.remove('active', 'show'));
                
                // Show the selected tab pane
                const targetId = this.getAttribute('href');
                const targetPane = document.querySelector(targetId);
                
                if (targetPane) {
                    targetPane.classList.add('active', 'show');
                }
            });
        });
    }
    
    // Next tab navigation
    const nextTabButtons = document.querySelectorAll('.next-tab');
    
    if (nextTabButtons.length > 0) {
        nextTabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const nextTabId = this.getAttribute('data-next');
                const nextTab = document.getElementById(nextTabId);
                
                if (nextTab) {
                    nextTab.click();
                }
            });
        });
    }
    
    // Prev tab navigation
    const prevTabButtons = document.querySelectorAll('.prev-tab');
    
    if (prevTabButtons.length > 0) {
        prevTabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const prevTabId = this.getAttribute('data-prev');
                const prevTab = document.getElementById(prevTabId);
                
                if (prevTab) {
                    prevTab.click();
                }
            });
        });
    }
    
    // Initialize Leaflet maps if the container exists
    initializeMap('map-container');
    initializeMap('property-location-map');
    initializeMap('property-map');
    initializeMap('search-map');
    
    // Toggle listing type prices
    const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
    const salePriceContainer = document.getElementById('sale_price_container');
    const rentPriceContainer = document.getElementById('rent_price_container');
    
    if (listingTypeRadios.length > 0 && salePriceContainer && rentPriceContainer) {
        listingTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'sale') {
                    salePriceContainer.style.display = 'block';
                    rentPriceContainer.style.display = 'none';
                    document.getElementById('sale_price').required = true;
                    document.getElementById('rent_price').required = false;
                } 
                else if (this.value === 'rent') {
                    salePriceContainer.style.display = 'none';
                    rentPriceContainer.style.display = 'block';
                    document.getElementById('sale_price').required = false;
                    document.getElementById('rent_price').required = true;
                }
                else {
                    salePriceContainer.style.display = 'block';
                    rentPriceContainer.style.display = 'block';
                    document.getElementById('sale_price').required = true;
                    document.getElementById('rent_price').required = true;
                }
            });
        });
    }
    
    // Image preview for file inputs
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    if (fileInputs.length > 0) {
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                const previewContainer = document.querySelector(this.dataset.preview || '#image-previews');
                
                if (!previewContainer) return;
                
                // Clear previous previews
                previewContainer.innerHTML = '';
                
                if (this.files && this.files.length > 0) {
                    // Create image previews
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        
                        if (!file.type.startsWith('image/')) continue;
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'image-preview';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = file.name;
                            preview.appendChild(img);
                            
                            // Add filename
                            const filename = document.createElement('div');
                            filename.className = 'image-filename';
                            filename.textContent = file.name.length > 15 ? 
                                file.name.substring(0, 12) + '...' : file.name;
                            preview.appendChild(filename);
                            
                            previewContainer.appendChild(preview);
                        };
                        
                        reader.readAsDataURL(file);
                    }
                    
                    // Update select dropdown for main image
                    const mainImageSelect = document.getElementById('main-image-select');
                    if (mainImageSelect) {
                        mainImageSelect.innerHTML = '';
                        
                        for (let i = 0; i < this.files.length; i++) {
                            const option = document.createElement('option');
                            option.value = i;
                            option.textContent = `Görsel ${i + 1}: ${this.files[i].name}`;
                            mainImageSelect.appendChild(option);
                        }
                    }
                }
            });
        });
    }
});

// Map initialization function
function initializeMap(containerId) {
    const mapContainer = document.getElementById(containerId);
    
    if (!mapContainer) return;
    
    // Default to Turkey center
    let initialLat = 39.1;
    let initialLng = 35.6;
    let initialZoom = 6;
    
    // Check for data attributes
    if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
        initialLat = parseFloat(mapContainer.dataset.lat);
        initialLng = parseFloat(mapContainer.dataset.lng);
        initialZoom = 15;
    }
    
    // Create map
    const map = L.map(containerId).setView([initialLat, initialLng], initialZoom);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    
    // Add marker if coordinates are set
    let marker;
    if (initialZoom > 6) {
        marker = createMarker([initialLat, initialLng], map);
    }
    
    // Add click event to update coordinates
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    if (latInput && lngInput) {
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            latInput.value = lat;
            lngInput.value = lng;
            
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = createMarker(e.latlng, map);
            }
        });
    }
    
    // Add property data if available
    if (window.propertyData && window.propertyData.length) {
        addPropertyMarkers(map, window.propertyData);
    }
    
    return map;
}

// Create a custom marker
function createMarker(latlng, map) {
    // Create custom icon
    const markerIcon = L.divIcon({
        className: 'custom-marker-icon',
        html: '<div class="marker-pin"></div>',
        iconSize: [30, 42],
        iconAnchor: [15, 42]
    });
    
    // Add marker to map
    return L.marker(latlng, {
        icon: markerIcon
    }).addTo(map);
}

// Add multiple property markers with popups
function addPropertyMarkers(map, properties) {
    const markers = [];
    
    properties.forEach(function(property) {
        if (!property.latitude || !property.longitude) return;
        
        // Format price
        let priceText = '';
        let priceClass = 'sale';
        
        if (property.rent_price && property.rent_price > 0) {
            priceText = `${formatPrice(property.rent_price)} ₺/ay`;
            priceClass = 'rent';
        } else {
            priceText = `${formatPrice(property.sale_price)} ₺`;
        }
        
        // Create custom icon with price tag
        const markerIcon = L.divIcon({
            className: 'custom-marker-icon',
            html: `<div class="marker-pin"></div><div class="marker-price ${priceClass}">${priceText}</div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -42]
        });
        
        // Add marker to map
        const marker = L.marker([property.latitude, property.longitude], {
            icon: markerIcon
        }).addTo(map);
        
        // Create popup content
        const popupContent = `
            <div class="map-popup">
                <img src="${property.main_image || 'assets/img/property-placeholder.jpg'}" class="popup-image" alt="${property.title}">
                <div class="popup-content">
                    <h5 class="popup-title">${property.title}</h5>
                    <p class="popup-price">${priceText}</p>
                    <p class="popup-address"><i class="bi bi-geo-alt"></i> ${property.city}, ${property.state}</p>
                    <a href="listing.php?id=${property.id}" class="btn btn-primary">Detaylar</a>
                </div>
            </div>
        `;
        
        // Add popup to marker
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'property-popup'
        });
        
        markers.push(marker);
    });
    
    // Fit map to markers if there are any
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    
    return markers;
}

// Format price with thousand separators
function formatPrice(price) {
    return new Intl.NumberFormat('tr-TR').format(price);
}
document.addEventListener('DOMContentLoaded', function() {
    // ---------- Utility Fonksiyonlar ----------
    // Birden fazla elementte aynı sınıfı toggle etmek için yardımcı fonksiyon
    function toggleClassOnElements(elements, className) {
      elements.forEach(function(element) {
        element.classList.toggle(className);
      });
    }
  
    // ---------- Sidebar İşlemleri ----------
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
        if (sidebar) sidebar.classList.toggle('toggled');
        body.classList.toggle('sidebar-toggled');
      });
    });
  
    // Küçük ekranlarda sidebar dışına tıklandığında sidebar'ı kapat
    document.addEventListener('click', function(e) {
      if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('toggled')) {
        const isToggleButton = sidebarToggles.some(btn => btn.contains(e.target));
        if (!sidebar.contains(e.target) && !isToggleButton) {
          sidebar.classList.remove('toggled');
          body.classList.remove('sidebar-toggled');
        }
      }
    });
  
    // ---------- Scroll-to-top Düğmesi ----------
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
  
    // ---------- Tabs İşlevselliği ----------
    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    tabLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        tabLinks.forEach(tab => tab.classList.remove('active'));
        this.classList.add('active');
        const tabPanes = document.querySelectorAll('.tab-pane');
        tabPanes.forEach(pane => pane.classList.remove('active', 'show'));
        const targetId = this.getAttribute('href');
        const targetPane = document.querySelector(targetId);
        if (targetPane) {
          targetPane.classList.add('active', 'show');
        }
      });
    });
  
    // Next/Previous tab navigasyonu
    const nextTabButtons = document.querySelectorAll('.next-tab');
    nextTabButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        const nextTabId = this.getAttribute('data-next');
        const nextTab = document.getElementById(nextTabId);
        if (nextTab) nextTab.click();
      });
    });
    const prevTabButtons = document.querySelectorAll('.prev-tab');
    prevTabButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        const prevTabId = this.getAttribute('data-prev');
        const prevTab = document.getElementById(prevTabId);
        if (prevTab) prevTab.click();
      });
    });
  
    // ---------- Listeleme Türü Radio Butonları (Fiyat Alanları) ----------
    const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
    const salePriceContainer = document.getElementById('sale_price_container');
    const rentPriceContainer = document.getElementById('rent_price_container');
    if (listingTypeRadios.length && salePriceContainer && rentPriceContainer) {
      listingTypeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
          if (this.value === 'sale') {
            salePriceContainer.style.display = 'block';
            rentPriceContainer.style.display = 'none';
            document.getElementById('sale_price').required = true;
            document.getElementById('rent_price').required = false;
          } else if (this.value === 'rent') {
            salePriceContainer.style.display = 'none';
            rentPriceContainer.style.display = 'block';
            document.getElementById('sale_price').required = false;
            document.getElementById('rent_price').required = true;
          } else if (this.value === 'both') {
            salePriceContainer.style.display = 'block';
            rentPriceContainer.style.display = 'block';
            document.getElementById('sale_price').required = true;
            document.getElementById('rent_price').required = true;
          }
        });
      });
    }
  
    // ---------- Resim Önizleme & Ana Görsel Seçimi ----------
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(function(input) {
      input.addEventListener('change', function() {
        const previewContainer = document.querySelector(this.dataset.preview || '#image-previews');
        if (!previewContainer) return;
        previewContainer.innerHTML = '';
        if (this.files && this.files.length > 0) {
          const mainImageContainer = document.getElementById('main-image-container');
          if (mainImageContainer) mainImageContainer.style.display = 'block';
          Array.from(this.files).forEach(function(file, i) {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) {
              const preview = document.createElement('div');
              preview.className = 'image-preview';
  
              const img = document.createElement('img');
              img.src = e.target.result;
              img.alt = file.name;
              preview.appendChild(img);
  
              const filenameDiv = document.createElement('div');
              filenameDiv.className = 'image-filename';
              filenameDiv.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
              preview.appendChild(filenameDiv);
  
              const deleteBtn = document.createElement('button');
              deleteBtn.className = 'delete-btn';
              deleteBtn.innerHTML = '&times;';
              deleteBtn.addEventListener('click', function() {
                preview.remove();
                if (previewContainer.children.length === 0 && mainImageContainer) {
                  mainImageContainer.style.display = 'none';
                }
              });
              preview.appendChild(deleteBtn);
  
              previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(file);
          });
  
          // Ana görsel seçimi için dropdown güncellemesi
          const mainImageSelect = document.getElementById('main-image-select');
          if (mainImageSelect) {
            mainImageSelect.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = "0";
            defaultOption.textContent = 'İlk yüklenen görsel';
            mainImageSelect.appendChild(defaultOption);
            Array.from(this.files).forEach(function(file, i) {
              const option = document.createElement('option');
              option.value = i;
              option.textContent = `Görsel ${i + 1}: ${file.name}`;
              mainImageSelect.appendChild(option);
            });
          }
        } else {
          const mainImageContainer = document.getElementById('main-image-container');
          if (mainImageContainer) mainImageContainer.style.display = 'none';
        }
      });
    });
  
    // ---------- Mesafe Alanları (Distance Rows) ----------
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
          <div class="col-md-2">
            <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
          </div>
        `;
        distancesContainer.appendChild(newRow);
        newRow.querySelector('.remove-distance').addEventListener('click', function() {
          newRow.remove();
        });
      });
      document.querySelectorAll('.remove-distance').forEach(function(btn) {
        btn.addEventListener('click', function() {
          this.closest('.distance-row').remove();
        });
      });
    }
  
    // ---------- Form Doğrulama (Validation) ----------
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      if (form.getAttribute('novalidate') === null) {
        form.addEventListener('submit', function(e) {
          if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const invalidElement = form.querySelector(':invalid');
            if (invalidElement) {
              const tabPane = invalidElement.closest('.tab-pane');
              if (tabPane && !tabPane.classList.contains('show')) {
                const tabLink = document.querySelector(`[href="#${tabPane.id}"]`);
                if (tabLink) tabLink.click();
              }
              invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
              invalidElement.focus();
            }
          }
          form.classList.add('was-validated');
        }, false);
      }
    });
  
    // ---------- Drag & Drop Dosya Yükleme ----------
    const dragDropAreas = document.querySelectorAll('#drag-drop-area');
    dragDropAreas.forEach(area => {
      const fileInput = area.querySelector('input[type="file"]');
      const selectBtn = area.querySelector('#select-files-btn');
      if (selectBtn && fileInput) {
        selectBtn.addEventListener('click', function(e) {
          e.preventDefault();
          fileInput.click();
        });
      }
      area.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
      });
      area.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
      });
      area.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (fileInput && e.dataTransfer.files.length > 0) {
          fileInput.files = e.dataTransfer.files;
          const event = new Event('change', { bubbles: true });
          fileInput.dispatchEvent(event);
        }
      });
    });
  
    // ---------- Harita Entegrasyonu (Leaflet) ----------
    const mapContainers = ['map-container', 'property-location-map', 'property-map', 'search-map'];
    mapContainers.forEach(id => initializeMap(id));
  });
  
  // ---------------------------
  // Harita (Leaflet) Fonksiyonları
  // ---------------------------
  function initializeMap(containerId) {
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) return;
  
    let initialLat = 39.1,
        initialLng = 35.6,
        initialZoom = 6;
  
    if (mapContainer.dataset.lat && mapContainer.dataset.lng) {
      initialLat = parseFloat(mapContainer.dataset.lat);
      initialLng = parseFloat(mapContainer.dataset.lng);
      initialZoom = 15;
    }
  
    const map = L.map(containerId).setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19
    }).addTo(map);
  
    let marker;
    if (initialZoom > 6) {
      marker = createMarker([initialLat, initialLng], map);
    }
  
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
  
    // Global propertyData varsa, işaretleyici ekle
    if (window.propertyData && window.propertyData.length) {
      addPropertyMarkers(map, window.propertyData);
    }
  
    return map;
  }
  
  function createMarker(latlng, map) {
    const markerIcon = L.divIcon({
      className: 'custom-marker-icon',
      html: '<div class="marker-pin"></div>',
      iconSize: [30, 42],
      iconAnchor: [15, 42]
    });
    return L.marker(latlng, { icon: markerIcon }).addTo(map);
  }
  
  function addPropertyMarkers(map, properties) {
    const markers = [];
    properties.forEach(function(property) {
      if (!property.latitude || !property.longitude) return;
      let priceText = '',
          priceClass = 'sale';
      if (property.rent_price && property.rent_price > 0) {
        priceText = `${formatPrice(property.rent_price)} ₺/ay`;
        priceClass = 'rent';
      } else {
        priceText = `${formatPrice(property.sale_price)} ₺`;
      }
      const markerIcon = L.divIcon({
        className: 'custom-marker-icon',
        html: `<div class="marker-pin"></div><div class="marker-price ${priceClass}">${priceText}</div>`,
        iconSize: [30, 42],
        iconAnchor: [15, 42],
        popupAnchor: [0, -42]
      });
      const marker = L.marker([property.latitude, property.longitude], { icon: markerIcon }).addTo(map);
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
      marker.bindPopup(popupContent, {
        maxWidth: 300,
        className: 'property-popup'
      });
      markers.push(marker);
    });
    if (markers.length > 0) {
      const group = new L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.1));
    }
    return markers;
  }
  
  function formatPrice(price) {
    return new Intl.NumberFormat('tr-TR').format(price);
  }
  
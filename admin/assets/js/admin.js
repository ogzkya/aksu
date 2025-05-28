// ak_su/admin/assets/js/admin.js - Güncellenmiş Hali

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
  ].filter(Boolean); // .filter(Boolean) null veya undefined değerleri kaldırır
  const body = document.body;
  const sidebar = document.querySelector('.sidebar');

  // Sidebar toggling için ortak dinleyici
  sidebarToggles.forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      // body.classList.toggle('sidebar-toggled'); // Bootstrap 5'te bu gerekmeyebilir, sidebar sınıfı yeterli olabilir
      if (sidebar) {
        sidebar.classList.toggle('toggled'); // Sidebar'a 'toggled' sınıfını ekle/kaldır
         // Body'ye de toggle ekleyelim, bazı stiller buna bağlı olabilir
         body.classList.toggle('sidebar-toggled');
      }
    });
  });

  // Küçük ekranlarda sidebar dışına tıklandığında sidebar'ı kapat
   document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('toggled')) {
      const isToggleButton = sidebarToggles.some(btn => btn && btn.contains(e.target)); // btn var mı diye kontrol ekle
      const isInsideSidebar = sidebar.contains(e.target);

      if (!isInsideSidebar && !isToggleButton) {
        sidebar.classList.remove('toggled');
        body.classList.remove('sidebar-toggled'); // Body'den de sınıfı kaldır
      }
    }
  });


  // ---------- Scroll-to-top Düğmesi ----------
  const scrollToTopButton = document.querySelector('.scroll-to-top');
  if (scrollToTopButton) {
    window.addEventListener('scroll', function() {
      // Butonun görünürlüğünü kontrol et
      scrollToTopButton.style.display = window.pageYOffset > 100 ? 'flex' : 'none'; // 'flex' veya 'block' olabilir
    });
    scrollToTopButton.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ---------- Tabs İşlevselliği (Bootstrap 5 ile zaten çalışmalı ama ekleyelim) ----------
  // Bootstrap 5 kendi tab yönetimini yaptığı için genellikle bu kod gereksizdir.
  // Ancak yine de ekleyelim, belki özel bir durum vardır.
  const triggerTabList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tab"]'))
  triggerTabList.forEach(function (triggerEl) {
    const tabTrigger = new bootstrap.Tab(triggerEl)

    triggerEl.addEventListener('click', function (event) {
      event.preventDefault()
      tabTrigger.show()
    })
  })


  // Next/Previous tab navigasyonu (Eğer Bootstrap tabları yetmiyorsa)
  const nextTabButtons = document.querySelectorAll('.next-tab');
  nextTabButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      const nextTabId = this.getAttribute('data-next');
      const nextTabTrigger = document.getElementById(nextTabId); // Trigger elementini al
      if (nextTabTrigger) {
          const tabInstance = bootstrap.Tab.getInstance(nextTabTrigger) || new bootstrap.Tab(nextTabTrigger);
          tabInstance.show();
      }
    });
  });
  const prevTabButtons = document.querySelectorAll('.prev-tab');
  prevTabButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      const prevTabId = this.getAttribute('data-prev');
      const prevTabTrigger = document.getElementById(prevTabId); // Trigger elementini al
      if (prevTabTrigger) {
           const tabInstance = bootstrap.Tab.getInstance(prevTabTrigger) || new bootstrap.Tab(prevTabTrigger);
          tabInstance.show();
      }
    });
  });

  // ---------- Listeleme Türü Radio Butonları (Fiyat Alanları) ----------
  // Bu kısım önceki analizde belirtildiği gibi kalabilir, çakışma yaratmıyor.
  const listingTypeRadios = document.querySelectorAll('input[name="listing_type"]');
  const salePriceContainer = document.getElementById('sale-price-container'); // ID'yi kontrol et add.php/edit.php ile eşleşmeli
  const rentPriceContainer = document.getElementById('rent-price-container'); // ID'yi kontrol et add.php/edit.php ile eşleşmeli
  const salePriceInput = document.getElementById('sale_price');
  const rentPriceInput = document.getElementById('rent_price');

  if (listingTypeRadios.length && salePriceContainer && rentPriceContainer) {
    listingTypeRadios.forEach(function(radio) {
      radio.addEventListener('change', function() {
        if (this.value === 'sale') {
          salePriceContainer.style.display = 'block';
          rentPriceContainer.style.display = 'none';
          if (salePriceInput) salePriceInput.required = true;
          if (rentPriceInput) rentPriceInput.required = false;
        } else if (this.value === 'rent') {
          salePriceContainer.style.display = 'none';
          rentPriceContainer.style.display = 'block';
          if (salePriceInput) salePriceInput.required = false;
          if (rentPriceInput) rentPriceInput.required = true;
        } else if (this.value === 'both') {
          salePriceContainer.style.display = 'block';
          rentPriceContainer.style.display = 'block';
          if (salePriceInput) salePriceInput.required = true;
          if (rentPriceInput) rentPriceInput.required = true;
        }
      });
    });
    // Sayfa yüklendiğinde mevcut seçime göre durumu ayarla
     const initialSelectedType = document.querySelector('input[name="listing_type"]:checked');
     if(initialSelectedType) {
         initialSelectedType.dispatchEvent(new Event('change'));
     }
  }


  /* =====================================================
     !!! GÖRSEL ÖNİZLEME KODLARI BURADAN KALDIRILDI !!!
     Bu işlevsellik artık assets/js/image-uploader.js tarafından yönetiliyor.
     ===================================================== */


  // ---------- Mesafe Alanları (Distance Rows) ----------
  // Bu kısım önceki analizde belirtildiği gibi kalabilir, çakışma yaratmıyor.
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
        <div class="col-md-2 d-flex align-items-end"> {/* Align button correctly */}
          <button type="button" class="btn btn-danger w-100 remove-distance">Sil</button>
        </div>
      `;
      distancesContainer.appendChild(newRow);
      // Silme butonuna event listener ekle
      newRow.querySelector('.remove-distance').addEventListener('click', function() {
        newRow.remove();
      });
    });
    // Mevcut silme butonlarına event listener ekle
    distancesContainer.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-distance')) {
          e.target.closest('.distance-row').remove();
      }
    });
  }

  // ---------- Form Doğrulama (Validation) ----------
  // Bootstrap 5 kendi doğrulamasını yapar, bu kod genellikle ek kontrol içindir.
  const forms = document.querySelectorAll('form.needs-validation'); // Sadece belirli formları hedef alabiliriz
   Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

            // Hatalı ilk elemanı bul ve ona odaklan/scroll et
             const invalidElement = form.querySelector(':invalid');
             if (invalidElement) {
                  // Eğer eleman bir tab içindeyse o tab'ı aktif et
                  const tabPane = invalidElement.closest('.tab-pane');
                  if (tabPane && !tabPane.classList.contains('active')) {
                      const tabTrigger = document.querySelector(`[data-bs-target="#${tabPane.id}"]`);
                      if (tabTrigger) {
                          const tabInstance = bootstrap.Tab.getInstance(tabTrigger) || new bootstrap.Tab(tabTrigger);
                          tabInstance.show();
                           // Tab değiştikten sonra odaklanma için küçük bir gecikme
                           setTimeout(() => {
                              invalidElement.focus();
                              invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                           }, 150);
                      } else {
                         invalidElement.focus();
                         invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                      }
                  } else {
                     invalidElement.focus();
                     invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  }
             }
          }
          form.classList.add('was-validated');
        }, false);
      });


  // ---------- Drag & Drop Dosya Yükleme (Bu da image-uploader.js'de yönetiliyor) ----------
  // Bu bölüm de kaldırıldı.


  // ---------- Harita Entegrasyonu (map-integration.js veya map-functions-optimized.js tarafından yönetilmeli) ----------
  // Bu dosyada harita başlatma olmamalı.
  // const mapContainers = ['map-container', 'property-location-map', 'property-map', 'search-map'];
  // mapContainers.forEach(id => initializeMap(id)); // Bu satır KALDIRILMALI
});


// ---------------------------
// Harita (Leaflet) Fonksiyonları
// ---------------------------
// Bu fonksiyonlar artık map-integration.js veya map-functions-optimized.js dosyasında olmalı.
// Bu dosyada (admin.js) olmamalılar.
/*
function initializeMap(containerId) { ... }
function createMarker(latlng, map) { ... }
function addPropertyMarkers(map, properties) { ... }
function formatPrice(price) { ... }
*/
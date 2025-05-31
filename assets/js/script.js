// Add this to assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
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
    
    // Enhanced image handling with lazy loading
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
    
    // Smooth scroll for anchor links
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
    
    // Grid/List view toggle for search page
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
        });
        
        listViewBtn.addEventListener('click', function() {
            gridView.classList.add('d-none');
            listView.classList.remove('d-none');
            gridViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
        });
    }
    
    // Enhanced form validation
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
    
    // Auto-hide notifications after 5 seconds
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
    
    // Enhanced thumbnail gallery in listing page
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
});

// Add this to admin/assets/js/admin.js to enhance the admin experience

document.addEventListener('DOMContentLoaded', function() {
    // Disable form resubmission on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Image preview functionality for file uploads
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewContainer = document.querySelector(this.dataset.preview || '#image-previews');
            
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                    // Skip non-image files
                    if (!file.type.match('image.*')) continue;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'image-preview';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                        
                        const filename = document.createElement('div');
                        filename.className = 'image-filename';
                        filename.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                        preview.appendChild(filename);
                        
                        previewContainer.appendChild(preview);
                    };
                    
                    reader.readAsDataURL(file);
                }
            }
        });
    });
    
    // Tab selection persistence
    const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    // Restore active tab from localStorage
    const activeTabId = localStorage.getItem('activeAdminTab');
    if (activeTabId) {
        const activeTab = document.querySelector(`[href="${activeTabId}"]`);
        if (activeTab) {
            activeTab.click();
        }
    }
    
    tabLinks.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activeAdminTab', e.target.getAttribute('href'));
        });
    });
});

// Replace the marker creation code in index.php, listing.php and search.php

// Example of how to update the marker code
propertyData.forEach(function(property) {
    // Fiyat bilgisi ve renk sınıfı ayarlama
    let priceText = '';
    let markerClass = 'sale'; // Default: satılık için
    
    if (property.rent_price && property.rent_price > 0) {
        priceText = `${formatPrice(property.rent_price)} ₺/ay`;
        markerClass = 'rent'; // Kiralık için 
    } else {
        priceText = `${formatPrice(property.sale_price)} ₺`;
    }
      // Marker ikon stilini güncelle - Yeni ev ikonu tasarımı
    const propertyIcon = L.divIcon({
        className: 'property-marker',
        html: `<div class="marker-container">
                <div class="marker-house-icon ${property.featured ? 'featured' : ''}">
                    <i class="bi bi-house-fill"></i>
                </div>
                <div class="marker-price-label ${property.featured ? 'featured' : ''}">${priceText}</div>
               </div>`,
        iconSize: [120, 60],
        iconAnchor: [60, 60],
        popupAnchor: [0, -60]
    });
    
    // Marker ekle
    const marker = L.marker([property.latitude, property.longitude], {
        icon: propertyIcon
    }).addTo(map);
    
    // Popup içeriği - modernized
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
});
</div>
            <!-- End Main Content -->
            
            <!-- Footer -->
            <footer class="bg-white py-4 mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            &copy; <?= date('Y') ?> Aksu Emlak. Tüm hakları saklıdır.
                        </div>
                        <div class="text-muted small">
                            Version 2.0 - Admin Panel
                        </div>
                    </div>
                </div>
            </footer>
            <!-- End Footer -->
        </div>
        <!-- End Content Wrapper -->
    </div>
    <!-- End Page Wrapper -->
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet CSS VE JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
      <!-- Admin JS -->
    <script src="<?= $adminUrl ?>assets/js/admin-clean.js"></script>
    
    <!-- HAR İTA TETİKLEME -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let mapInitialized = false;
        
        // Bootstrap tab event'lerini dinle
        const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabTriggers.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                const href = e.target.getAttribute('href');
                if (href === '#location' && !mapInitialized) {
                    setTimeout(function() {
                        initMap();
                        mapInitialized = true;
                    }, 100);
                } else if (href === '#location' && window.currentMap) {
                    setTimeout(function() {
                        window.currentMap.invalidateSize();
                    }, 100);
                }
            });
        });
        
        function initMap() {
            const mapContainer = document.getElementById('map-container');
            if (!mapContainer || typeof L === 'undefined') return;
            
            mapContainer.style.height = '400px';
            mapContainer.style.width = '100%';
            
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const lat = (latInput && latInput.value) ? parseFloat(latInput.value) : 41.0082;
            const lng = (lngInput && lngInput.value) ? parseFloat(lngInput.value) : 28.7784;
            
            try {
                window.currentMap = L.map('map-container').setView([lat, lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(window.currentMap);
                
                const marker = L.marker([lat, lng], { draggable: true }).addTo(window.currentMap);
                
                marker.on('dragend', function() {
                    const pos = marker.getLatLng();
                    if (latInput) latInput.value = pos.lat.toFixed(6);
                    if (lngInput) lngInput.value = pos.lng.toFixed(6);
                });
                
                window.currentMap.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    if (latInput) latInput.value = e.latlng.lat.toFixed(6);
                    if (lngInput) lngInput.value = e.latlng.lng.toFixed(6);
                });
                
                setTimeout(function() {
                    if (window.currentMap) {
                        window.currentMap.invalidateSize();
                    }
                }, 200);
                
            } catch (error) {
                console.error('Harita oluşturma hatası:', error);
            }
        }
    });
    </script>
    
    <!-- TinyMCE Editor (sadece gerektiğinde) -->
    <?php if (isset($useTinyMCE) && $useTinyMCE): ?>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea.tinymce',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; }'
        });
    </script>
    <?php endif; ?>
    
    <!-- Sayfa özel scripts varsa -->
    <?php if (isset($pageScripts)): ?>
        <?= $pageScripts ?>
    <?php endif; ?>
</body>
</html>
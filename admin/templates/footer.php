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
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Admin JS - TEK DOSYA (Tüm eski dosyalar kaldırıldı) -->
    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/js/admin-clean.js"></script>
    
    <!-- TinyMCE Editor (sadece gerektiğinde) -->
    <?php if (isset($useTinyMCE) && $useTinyMCE): ?>
    <script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
</div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Emlak İlan Sitesi <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="bi bi-arrow-up"></i>
    </a>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    
    <!-- Admin Core JS - TÜM FONKSİYONLARI İÇERİR -->
    <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/js/admin-core.js"></script>
    
    <!-- KULLANILMAYAN DOSYALAR KALDIRILDI:
         - admin.js (admin-core.js içinde mevcut)
         - form-upload.js (admin-core.js içinde mevcut)
         - map-integration.js (admin-core.js içinde mevcut)
    -->
</body>
</html>
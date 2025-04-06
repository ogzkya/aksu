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
    
    <!-- Admin JS -->
     <script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/js/admin.js"></script>
<script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/js/form-upload.js"></script>
<script src="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/js/map-integration.js"></script>

    
    <!-- Uygulama JS Dosyaları -->
<!-- <script src="/assets/js/map-functions-optimized.js"></script>
<script src="/assets/js/image-uploader.js"></script>
<script src="/assets/js/ajax-form-handler.js"></script> -->
</body>
</html>
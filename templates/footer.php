</main>
    <!-- Ana İçerik Bitiş -->
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">Aksu Emlak</h5>
                    <p>Aksu Emlak, gayrimenkul sektöründe satılık ve kiralık mülkler için güvenilir bir platform sağlar.</p>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3">Hızlı Erişim</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Ana Sayfa</a></li>
                        <li><a href="search.php?listing_type=sale" class="text-white">Satılık İlanlar</a></li>
                        <li><a href="search.php?listing_type=rent" class="text-white">Kiralık İlanlar</a></li>
                        <li><a href="blog.php" class="text-white">Blog</a></li>
                        <li><a href="contact.php" class="text-white">İletişim</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3">İletişim</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt me-2"></i> Atatürk Cad. No: 123, Aksu, Antalya</li>
                        <li><i class="bi bi-telephone me-2"></i> (0242) 555 55 55</li>
                        <li><i class="bi bi-envelope me-2"></i> info@aksu-emlak.com</li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> Aksu Emlak. Tüm hakları saklıdır.</p>
                </div>
                
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-facebook fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-twitter fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-instagram fs-4"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    
    <!-- Site JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
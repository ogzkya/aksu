</main>
    <!-- Ana İçerik Bitiş -->
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Aksu Emlak Bilgileri -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Aksu Emlak</h5>
                    <p>2000 yılından bu yana İstanbul ve çevresinde gayrimenkul sektöründe satılık ve kiralık mülkler için güvenilir bir platform sağlıyoruz.</p>
                    <div class="mt-4">
                        <p class="mb-1"><i class="bi bi-geo-alt me-2"></i> Halkalı Küçükçekmece</p>
                        <p class="mb-1"><i class="bi bi-telephone me-2"></i> (0242) 555 55 55</p>
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i> aksu-emlak@hotmail.com</p>
                    </div>
                </div>
                
                <!-- Hızlı Erişim -->
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Hızlı Erişim</h5>
                    <ul class="list-unstyled">
                        <?php
                        footer_link('index.php', 'Ana Sayfa');
                        footer_link('search.php?listing_type=sale', 'Satılık İlanlar');
                        footer_link('search.php?listing_type=rent', 'Kiralık İlanlar');
                        footer_link('blog.php', 'Blog');
                        footer_link('contact.php', 'İletişim');
                        ?>
                    </ul>
                </div>
                
                <!-- Kategoriler -->
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Kategoriler</h5>
                    <ul class="list-unstyled">
                        <?php
                        footer_link('search.php?category=Apartment', 'Daireler');
                        footer_link('search.php?category=House', 'Müstakil Evler');
                        footer_link('search.php?category=Commercial', 'Ticari Mülkler');
                        footer_link('search.php?category=Land', 'Arsalar');
                        footer_link('search.php', 'Tüm Kategoriler');
                        ?>
                    </ul>
                </div>
                
                <!-- Bülten Aboneliği ve Sosyal Medya -->
                <div class="col-md-4">
                    <h5 class="mb-3">Bültenimize Abone Olun</h5>
                    <p>En yeni gayrimenkul fırsatlarından ve kampanyalardan haberdar olmak için abone olun.</p>
                    <form class="mt-3">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="E-posta adresiniz" required>
                            <button class="btn btn-accent" type="submit">Abone Ol</button>
                        </div>
                    </form>
                    <h5 class="mt-4 mb-3">Bizi Takip Edin</h5>
                    <ul class="list-inline">
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-facebook fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-twitter fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-instagram fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-linkedin fs-4"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-youtube fs-4"></i></a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-md-0">&copy; <?= date('Y') ?> Aksu Emlak. Tüm hakları saklıdır.</p>
                </div>
                
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-white">Gizlilik Politikası</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white">Kullanım Şartları</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white">Çerez Politikası</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    
    
    <!-- Scroll to Top Butonu -->
    <a href="#" class="scroll-to-top" id="scrollTop">
        <i class="bi bi-arrow-up"></i>
    </a>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    
    <!-- AOS JS for animations -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    
    <!-- Site JS -->
     <script src="assets/js/site-core.js"></script>
    <!-- <script src="assets/js/map-fix.js"></script>
    <script src="assets/js/image-upload-fix.js"></script>
    <script src="assets/js/script.js"></script> -->
</body>
</html>
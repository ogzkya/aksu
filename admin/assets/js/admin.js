// admin/assets/js/admin.js
document.addEventListener("DOMContentLoaded", function() {
    // Toggle sidebar
    document.querySelector("#sidebarToggle").addEventListener("click", function(e) {
        e.preventDefault();
        document.querySelector("body").classList.toggle("sidebar-toggled");
    });
    
    document.querySelector("#sidebarToggleTop").addEventListener("click", function(e) {
        e.preventDefault();
        document.querySelector("body").classList.toggle("sidebar-toggled");
    });
    
    // Scroll to top button
    const scrollToTopButton = document.querySelector(".scroll-to-top");
    
    function toggleScrollToTopButton() {
        if (window.pageYOffset > 100) {
            scrollToTopButton.style.display = "block";
        } else {
            scrollToTopButton.style.display = "none";
        }
    }
    
    window.addEventListener("scroll", toggleScrollToTopButton);
    
    scrollToTopButton.addEventListener("click", function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
});
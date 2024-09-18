document.addEventListener("DOMContentLoaded", function() {
    const backToTopBtn = document.getElementById("btnbacktotop");
    console.log(backToTopBtn)
    if (backToTopBtn) {
        window.addEventListener("scroll", function () {
            if (window.scrollY > 50) {

                backToTopBtn.style.display = "block";
            } else {
                backToTopBtn.style.display = "none";
            }
        });
        backToTopBtn.addEventListener("click", function () {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }
});

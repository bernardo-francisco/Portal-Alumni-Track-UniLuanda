/* ================================================================
   HOME — Carrossel do Hero (loop infinito)
   Ficheiro carregado apenas na página inicial
================================================================ */

(function () {
    function iniciarCarrossel() {
        const slides = document.querySelectorAll('.hero-bg-slide');
        const dots   = document.querySelectorAll('.hero-dot');

        if (!slides.length) return;

        console.log('[home.js] Carrossel iniciado com', slides.length, 'slides');

        let current  = 0;
        let autoplay = null;

        // Garantir que só o primeiro está ativo
        slides.forEach((s, i) => {
            if (i === 0) s.classList.add('active');
            else s.classList.remove('active');
        });
        dots.forEach((d, i) => {
            if (i === 0) d.classList.add('active');
            else d.classList.remove('active');
        });

        function goTo(index) {
            if (slides[current]) slides[current].classList.remove('active');
            if (dots[current])    dots[current].classList.remove('active');

            current = (index + slides.length) % slides.length;

            if (slides[current]) slides[current].classList.add('active');
            if (dots[current])    dots[current].classList.add('active');
        }

        function next() { goTo(current + 1); }

        function startAutoplay() {
            if (autoplay) clearInterval(autoplay);
            autoplay = setInterval(next, 5000);
        }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                goTo(i);
                startAutoplay();
            });
        });

        startAutoplay();
    }

    // Como este ficheiro só é carregado na home, o DOM já está pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarCarrossel);
    } else {
        iniciarCarrossel();
    }
})();
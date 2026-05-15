    <footer class="footer-expanded">
        <div class="footer-left">
            <h3>PPLG 3 Engineering</h3>
            <p>&copy; 2026 Class X PPLG 3 &bull; Software Engineering Excellence</p>
        </div>
        <div class="footer-right">
            <a href="index.php">Beranda</a>
            <a href="structure.php">Struktur</a>
            <a href="students.php">Siswa</a>
            <a href="gallery.php">Gallery</a>
            <a href="projects.php">Project</a>
            <a href="contact.php">Contact</a>
        </div>
    </footer>

<script>
/* ============================================
   GLOBAL PREMIUM JS — Scroll Reveal, Navbar,
   Particle Canvas, Stats Counter
   ============================================ */

(function() {

    // ── 1. NAVBAR: Scroll shrink & scrolled class ──────────────
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const onScroll = () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ── 2. HAMBURGER ──────────────────────────────────────────
    const btn    = document.getElementById('navHamburger');
    const drawer = document.getElementById('navDrawer');
    if (btn && drawer) {
        btn.addEventListener('click', () => drawer.classList.toggle('open'));
        drawer.querySelectorAll('a').forEach(a =>
            a.addEventListener('click', () => drawer.classList.remove('open'))
        );
        document.addEventListener('click', e => {
            if (!btn.contains(e.target) && !drawer.contains(e.target))
                drawer.classList.remove('open');
        });
    }

    // ── 3. SCROLL REVEAL (Intersection Observer) ─────────────
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // ── 4. ANIMATED STATS COUNTER ─────────────────────────────
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseFloat(el.getAttribute('data-target'));
                const suffix = el.getAttribute('data-suffix') || '';
                const isFloat = String(target).includes('.');
                let start = 0;
                const duration = 1800;
                const step = (timestamp) => {
                    if (!start) start = timestamp;
                    const progress = Math.min((timestamp - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 4); // ease out quart
                    const current = eased * target;
                    el.textContent = (isFloat ? current.toFixed(1) : Math.floor(current)) + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
                counterObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));

    // ── 5. PARTICLE CANVAS (Home page only) ───────────────────
    const canvas = document.getElementById('hero-canvas');
    if (canvas) {
        const ctx  = canvas.getContext('2d');
        let W, H, particles = [], animId;

        const resize = () => {
            W = canvas.width  = canvas.offsetWidth;
            H = canvas.height = canvas.offsetHeight;
        };

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x  = Math.random() * W;
                this.y  = Math.random() * H;
                this.r  = Math.random() * 2.2 + 0.6;
                this.vx = (Math.random() - 0.5) * 0.35;
                this.vy = (Math.random() - 0.5) * 0.35;
                this.a  = Math.random() * 0.5 + 0.2;
                this.color = Math.random() > 0.5 ? '37,99,235' : '139,92,246';
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${this.color},${this.a})`;
                ctx.fill();
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
            }
        }

        const drawLines = () => {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(37,99,235,${0.12 * (1 - dist/120)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        };

        const animate = () => {
            ctx.clearRect(0, 0, W, H);
            drawLines();
            particles.forEach(p => { p.update(); p.draw(); });
            animId = requestAnimationFrame(animate);
        };

        resize();
        // ~1 particle per 6000px²
        const count = Math.min(Math.floor((W * H) / 6000), 80);
        for (let i = 0; i < count; i++) particles.push(new Particle());
        animate();

        const ro = new ResizeObserver(() => {
            cancelAnimationFrame(animId);
            resize();
            animate();
        });
        ro.observe(canvas.parentElement);
    }

})();
</script>
</body>
</html>

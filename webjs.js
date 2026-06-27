// Custom crosshair cursor
const cursor = document.createElement('div');
cursor.id = 'custom-cursor';
document.documentElement.appendChild(cursor);

document.addEventListener('mousemove', (e) => {
    cursor.style.transform = `translate3d(${e.clientX - 10}px, ${e.clientY - 10}px, 0)`;
});

document.addEventListener('DOMContentLoaded', () => {

    // Theme switching
    function setTheme(themeName) {
        document.body.classList.toggle('light-theme', themeName === 'light');
        localStorage.setItem('portfolio-theme', themeName);
    }
    window.setTheme = setTheme;

    if (localStorage.getItem('portfolio-theme') === 'light') {
        document.body.classList.add('light-theme');
    }

    // Hide loader after page fully loads
    window.addEventListener('load', () => {
        const loader = document.getElementById('loader');
        if (!loader) return;
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }, 1500);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Scroll progress bar
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed; top: 0; left: 0; height: 4px;
        background: var(--accent); z-index: 10001; width: 0%;
        transition: width 0.1s ease-out;
    `;
    document.body.appendChild(progressBar);

    // Merged scroll handler: progress bar + active nav link
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links a');

    window.addEventListener('scroll', () => {
        const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        progressBar.style.width = scrolled + '%';

        let current = '';
        sections.forEach(section => {
            if (window.scrollY >= section.offsetTop - 60) {
                current = section.id;
            }
        });
        navLinks.forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === '#' + current);
        });
    });

    // Section reveal on scroll
    const observer = new IntersectionObserver(
        entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }),
        { threshold: 0.1 }
    );
    document.querySelectorAll('.section').forEach(s => observer.observe(s));

    // Ticker
    const tickerItems = [
        'Frontend Developer', 'ReactJS', 'PUP · BSCS', 'OJT Ready',
        'Jaru', 'Kalayaan Laguna', 'TypeScript', 'UI/UX', 'Node.js',
        'TailwindCSS', 'GWA 1.57', 'Open to Work', 'Jortog', 'Clean Code'
    ];
    const track = document.getElementById('ticker');
    if (track) {
        const items = [...tickerItems, ...tickerItems].map(t => `<span>✶ ${t}</span>`).join('');
        track.innerHTML = items + items;
    }

    // Konami code easter egg
    const konamiCode = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'b', 'a'
    ];
    let konamiIndex = 0;

    document.addEventListener('keydown', (e) => {
        if (e.key === konamiCode[konamiIndex]) {
            konamiIndex++;
            if (konamiIndex === konamiCode.length) {
                alert('GAMER MODE ACTIVATED: Visuals Overclocked!');
                document.body.style.filter = 'invert(1) hue-rotate(180deg)';
                document.documentElement.style.setProperty('--accent', '#ff003c');
                konamiIndex = 0;
            }
        } else {
            konamiIndex = 0;
        }
    });

});

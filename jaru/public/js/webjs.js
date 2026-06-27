const cursor = document.createElement('div');
cursor.id = 'custom-cursor';
document.documentElement.appendChild(cursor);

document.addEventListener('mousemove', (e) => {
    cursor.style.transform = `translate3d(${e.clientX - 10}px, ${e.clientY - 10}px, 0)`;
});

document.addEventListener("DOMContentLoaded", () => {

    window.addEventListener('load', () => {
        const loader = document.getElementById('loader');
        if (loader) {
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }, 1500);
        }

        window.setTheme = function(themeName) {
            if (themeName === 'light') {
                document.body.classList.add('light-theme');
                localStorage.setItem('portfolio-theme', 'light');
            } else {
                document.body.classList.remove('light-theme');
                localStorage.setItem('portfolio-theme', 'dark');
            }
        };

        if (localStorage.getItem('portfolio-theme') === 'light') {
            document.body.classList.add('light-theme');
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });
    });

    const progressBar = document.createElement('div');
    progressBar.style.cssText = `position:fixed;top:0;left:0;height:4px;background:var(--accent);z-index:10001;width:0%;transition:width 0.1s ease-out;`;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        progressBar.style.width = ((winScroll / height) * 100) + "%";
    });

    const sections = document.querySelectorAll('section, div.section');
    const navLinks = document.querySelectorAll('.nav-links a');
    window.addEventListener('scroll', () => {
        let current = "";
        sections.forEach(section => {
            if (window.pageYOffset >= section.offsetTop - 60) current = section.getAttribute("id") || "";
        });
        navLinks.forEach(link => {
            link.classList.remove("active");
            const href = link.getAttribute("href") || "";
            if (current && href.includes(current)) link.classList.add("active");
        });
    });

    const pattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
    let currentKey = 0;
    document.addEventListener('keydown', (e) => {
        if (e.key === pattern[currentKey]) {
            currentKey++;
            if (currentKey === pattern.length) {
                activateGamerMode();
                currentKey = 0;
            }
        } else { currentKey = 0; }
    });

    function activateGamerMode() {
        alert("GAMER MODE ACTIVATED: Visuals Overclocked!");
        document.body.style.filter = "invert(1) hue-rotate(180deg)";
        document.documentElement.style.setProperty('--accent', '#ff003c');
    }

    const tickerItems = ['ComSci Student', 'Mabangis na Gamer', 'Frank Dagat Worshipper', 'Adventurer', 'Jaru', 'PUP', 'Laguna', "Kalayaan's Finest", 'Palpitate Gods', 'Dumudurog sa Valo', 'Dumadakdak sa 6 footers', 'Kuya Ja', 'Jortog'];
    const track = document.getElementById('ticker');
    if (track) {
        const full = [...tickerItems, ...tickerItems].map(t => '<span>\u2736 ' + t + '</span>').join('');
        track.innerHTML = full + full;
    }

    // ── HOBBY GALLERY ─────────────────────────────────────────
    let galleryInterval = null;

    const hobbyData = {
        gaming: ['public/images/ow.jpg', 'public/images/ml.jpg', 'public/images/valo.jpg'],
        movies: ['public/images/op.jpg', 'public/images/beforesunrise.jpg'],
        traveling: ['public/images/taytayfallss.jpg', 'public/images/yambo.jpg'],
        music: ['public/images/sza.jpg', 'public/images/frank.jpg', 'public/images/drake.jpg']
    };

    const labels = {
        gaming: '🎮 Gaming',
        movies: '🎬 Movies & Series',
        traveling: '✈️ Traveling',
        music: '🎧 Music'
    };

    window.switchHobby = function(type) {
        const images = hobbyData[type];
        const imgElement = document.getElementById('hobby-image');
        const caption = document.getElementById('image-caption');
        let currentIndex = 0;

        if (!imgElement || !images || images.length === 0) return;

        clearInterval(galleryInterval);
        galleryInterval = null;

        if (caption) caption.textContent = labels[type] || '';

        const changeImage = () => {
            imgElement.style.opacity = '0';
            setTimeout(() => {
                imgElement.src = images[currentIndex];
                imgElement.style.opacity = '1';
                currentIndex = (currentIndex + 1) % images.length;
            }, 400);
        };

        changeImage();

        if (images.length > 1) {
            galleryInterval = setInterval(changeImage, 2000);
        }
    };

    // Auto-start gaming on load
    setTimeout(() => { window.switchHobby('gaming'); }, 500);

});
document.addEventListener('DOMContentLoaded', function () {
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');

        header.addEventListener('click', () => {
            accordionItems.forEach(accItem => {
                if (accItem !== item) {
                    accItem.classList.remove('active');
                }
            });

            item.classList.toggle('active');
        });
    });

    const navLinks = document.querySelectorAll('header nav a');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href.startsWith('#')) {
                e.preventDefault();

                const targetSection = document.querySelector(href);

                if (targetSection) {
                    window.scrollTo({
                        top: targetSection.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    const contactForm = document.getElementById('contact');

    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const reason = document.getElementById('reason').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !reason || !email || !message) {
                alert('Por favor complete todos los campos.');
                return;
            }

            const phoneNumber = '+1234567890';
            const whatsappMessage = `Nuevo mensaje de contacto:
*Nombre:* ${name}
*Motivo:* ${reason}
*Email:* ${email}

*Mensaje:*
${message}`;

            const encodedMessage = encodeURIComponent(whatsappMessage);

            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;

            window.open(whatsappUrl, '_blank');

            contactForm.reset();
        });
    }

    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.backgroundColor = '#ffeeee';
        });

        card.addEventListener('mouseleave', function () {
            this.style.backgroundColor = 'var(--secondary-color)';
        });
    });

    const sections = document.querySelectorAll('.section');

    window.addEventListener('scroll', () => {
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;

            if (pageYOffset >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });

    const servicesSlider = document.querySelector('.services-slider');
    const prevButton = document.querySelector('.slider-control.prev');
    const nextButton = document.querySelector('.slider-control.next');

    if (servicesSlider && prevButton && nextButton) {
        const cardWidth = 300;

        prevButton.addEventListener('click', () => {
            servicesSlider.scrollBy({
                left: -cardWidth,
                behavior: 'smooth'
            });
        });

        nextButton.addEventListener('click', () => {
            servicesSlider.scrollBy({
                left: cardWidth,
                behavior: 'smooth'
            });
        });

        let autoScrollInterval;

        const startAutoScroll = () => {
            autoScrollInterval = setInterval(() => {
                const maxScroll = servicesSlider.scrollWidth - servicesSlider.clientWidth;
                const currentScroll = servicesSlider.scrollLeft;

                if (currentScroll >= maxScroll - 10) {
                    servicesSlider.scrollTo({
                        left: 0,
                        behavior: 'smooth'
                    });
                } else {
                    servicesSlider.scrollBy({
                        left: cardWidth,
                        behavior: 'smooth'
                    });
                }
            }, 5000);
        };

        const stopAutoScroll = () => {
            clearInterval(autoScrollInterval);
        };

        startAutoScroll();

        servicesSlider.addEventListener('mouseenter', stopAutoScroll);
        servicesSlider.addEventListener('mouseleave', startAutoScroll);
        prevButton.addEventListener('mouseenter', stopAutoScroll);
        prevButton.addEventListener('mouseleave', startAutoScroll);
        nextButton.addEventListener('mouseenter', stopAutoScroll);
        nextButton.addEventListener('mouseleave', startAutoScroll);
    }

    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));

            button.classList.add('active');

            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    const galleryItems = document.querySelectorAll('.gallery-item');

    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = `slideIn 0.8s ease forwards ${entry.target.style.getPropertyValue('--delay')}`;
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    galleryItems.forEach(item => {
        observer.observe(item);
    });

    const header = document.querySelector('header');
    const backToTopBtn = document.getElementById('backToTopBtn');
    let lastScrollY = window.pageYOffset;
    let isHeaderVisible = true;

    if (backToTopBtn) {
        backToTopBtn.style.display = 'none';

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    window.addEventListener('scroll', () => {
        const currentScrollY = window.pageYOffset;

        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            if (isHeaderVisible) {
                header.style.transform = 'translateY(-100%)';
                isHeaderVisible = false;
            }
        } else {
            if (!isHeaderVisible) {
                header.style.transform = 'translateY(0)';
                isHeaderVisible = true;
            }
        }

        if (backToTopBtn) {
            if (currentScrollY > 300) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        }

        lastScrollY = currentScrollY;
    });
});

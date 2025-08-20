function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}

function animateCounters() {
    document.querySelectorAll('.counter, .metric-value').forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count')) || 0;
        const start = parseInt(counter.textContent) || 0;

        if (isNaN(target) || target === 0) {
            counter.textContent = target;
            return;
        }

        const duration = 1500;
        const steps = 60;
        const increment = (target - start) / steps;
        let current = start;
        let step = 0;

        const timer = setInterval(() => {
            step++;
            current = Math.min(start + increment * step, target);
            counter.textContent = Math.floor(current);

            if (step >= steps || current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            }
        }, duration / steps);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.counter, .metric-value').forEach(counter => {
        const target = counter.getAttribute('data-count');
        if (target) {
            counter.textContent = target;
        }
    });

    initCharts();

    setTimeout(() => {
        animateCounters();
    }, 500);

    document.querySelectorAll('input[name="chartPeriod"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                changePeriod(this.value);
            }
        });
    });

    document.querySelectorAll('.chart-card, .stat-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý form tìm kiếm
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const location = searchForm.querySelector('[name="location"]').value;
            if (!location.trim()) {
                e.preventDefault();
                alert('Vui lòng nhập khu vực tìm kiếm');
            }
        });
    }

    // Hiển thị/ẩn mật khẩu
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const password = document.querySelector('#password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    }

    // Thêm animation cho các phần tử khi scroll
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function handleScrollAnimation() {
        const elements = document.querySelectorAll('.card, .review-item');
        elements.forEach(element => {
            if (isElementInViewport(element) && !element.classList.contains('animate-fade-in')) {
                element.classList.add('animate-fade-in');
            }
        });
    }

    window.addEventListener('scroll', handleScrollAnimation);
    handleScrollAnimation();
});

// Thêm hiệu ứng smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add smooth scrolling animation
const smoothScroll = element => {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
};

// Add animation on scroll
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('active');
        }
    });
};

// Initialize animations
window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);

// Add hover effects on room cards
document.querySelectorAll('.room-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.classList.add('shadow-lg');
    });
    
    card.addEventListener('mouseleave', function() {
        this.classList.remove('shadow-lg');
    });
});

// Add loading spinner for images
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('load', function() {
        this.classList.add('fade-in');
    });
});

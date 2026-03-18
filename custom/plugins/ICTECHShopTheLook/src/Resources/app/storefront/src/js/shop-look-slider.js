export default class ShopLookSlider {
    constructor(el) {
        this.el = el;
        this.container = el.querySelector('.ict-slider-container');
        this.slides = this.container.querySelectorAll('.ict-slide');
        this.prevBtn = el.querySelector('.ict-slider-prev');
        this.nextBtn = el.querySelector('.ict-slider-next');
        this.dotsContainer = el.querySelector('.ict-slider-dots');
        this.options = JSON.parse(el.dataset.shopLookSliderOptions || '{}');
        this.currentIndex = 0;
        this.itemsPerView = 6;
        this.autoplayInterval = null;

        if (this.slides.length === 0) return;

        this._init();
    }

    _updateItemsPerView() {
        const w = window.innerWidth;
        if (w >= 1200) this.itemsPerView = 6;
        else if (w >= 992) this.itemsPerView = 4;
        else if (w >= 768) this.itemsPerView = 3;
        else this.itemsPerView = 2;
    }

    _createDots() {
        if (!this.dotsContainer) return;
        this.dotsContainer.innerHTML = '';
        const total = Math.ceil(this.slides.length / this.itemsPerView);
        for (let i = 0; i < total; i++) {
            const dot = document.createElement('button');
            dot.classList.add('ict-slider-dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this._goToSlide(i * this.itemsPerView));
            this.dotsContainer.appendChild(dot);
        }
    }

    _updateSlider() {
        const slideWidth = 100 / this.itemsPerView;
        this.container.style.transform = `translateX(${-(this.currentIndex * slideWidth)}%)`;
        if (this.dotsContainer) {
            this.dotsContainer.querySelectorAll('.ict-slider-dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === Math.floor(this.currentIndex / this.itemsPerView));
            });
        }
    }

    _goToSlide(index) {
        this.currentIndex = Math.max(0, Math.min(index, this.slides.length - this.itemsPerView));
        this._updateSlider();
    }

    _nextSlide() {
        this.currentIndex = this.currentIndex < this.slides.length - this.itemsPerView
            ? this.currentIndex + 1 : 0;
        this._updateSlider();
    }

    _prevSlide() {
        this.currentIndex = this.currentIndex > 0
            ? this.currentIndex - 1 : this.slides.length - this.itemsPerView;
        this._updateSlider();
    }

    _init() {
        this._updateItemsPerView();

        this.container.style.display = 'flex';
        this.container.style.transition = `transform ${this.options.speed || 300}ms ease`;
        this.container.style.width = `${this.slides.length * (100 / this.itemsPerView)}%`;
        this.slides.forEach(slide => {
            slide.style.flex = `0 0 ${100 / this.slides.length}%`;
        });

        this._createDots();
        this._updateSlider();

        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this._prevSlide());
        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this._nextSlide());

        if (this.options.autoSlide) {
            this.autoplayInterval = setInterval(() => this._nextSlide(), this.options.autoplayTimeout || 5000);
            this.el.addEventListener('mouseenter', () => clearInterval(this.autoplayInterval));
            this.el.addEventListener('mouseleave', () => {
                if (this.options.autoSlide) {
                    this.autoplayInterval = setInterval(() => this._nextSlide(), this.options.autoplayTimeout || 5000);
                }
            });
        }

        window.addEventListener('resize', () => {
            this._updateItemsPerView();
            this._createDots();
            this._goToSlide(0);
        });
    }
}

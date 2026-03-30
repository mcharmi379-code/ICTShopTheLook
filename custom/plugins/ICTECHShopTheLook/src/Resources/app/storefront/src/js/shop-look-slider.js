export default class ShopLookSlider {
    constructor(el) {
        this.el             = el;
        this.container      = el.querySelector('.ict-slider-container');
        this.slides         = this.container.querySelectorAll('.ict-slide');
        this.prevBtn        = el.querySelector('.ict-slider-prev');
        this.nextBtn        = el.querySelector('.ict-slider-next');
        this.dotsContainer  = el.querySelector('.ict-slider-dots');
        this.options        = JSON.parse(el.dataset.shopLookSliderOptions || '{}');
        this.currentIndex   = 0;
        this.itemsPerView   = 6;
        this.realCount      = 0; // original slide count before cloning
        this.cloneOffset    = 0;
        this.autoplayInterval = null;

        if (this.slides.length === 0) return;

        this._init();
    }

    _updateItemsPerView() {
        const breakpoints = [
            { min: 1200, items: 6 },
            { min: 992,  items: 4 },
            { min: 768,  items: 3 },
            { min: 0,    items: 2 },
        ];
        this.itemsPerView = breakpoints.find(bp => window.innerWidth >= bp.min).items;
    }

    _cloneSlides() {
        // Remove any previously cloned slides
        this.container.querySelectorAll('.ict-slide--clone').forEach(c => c.remove());

        const originals = Array.from(this.container.querySelectorAll('.ict-slide:not(.ict-slide--clone)'));
        this.realCount = originals.length;
        this.cloneOffset = Math.min(this.itemsPerView, this.realCount);

        if (this.cloneOffset === 0) {
            this.slides = this.container.querySelectorAll('.ict-slide');
            return;
        }

        // Clone on both sides so autoplay can keep moving visually left-to-right
        // while we reset the track position off-screen after each loop.
        originals.slice(-this.cloneOffset).forEach(slide => {
            const clone = slide.cloneNode(true);
            clone.classList.add('ict-slide--clone');
            this.container.insertBefore(clone, this.container.firstChild);
        });

        originals.slice(0, this.cloneOffset).forEach(slide => {
            const clone = slide.cloneNode(true);
            clone.classList.add('ict-slide--clone');
            this.container.appendChild(clone);
        });

        // Re-query all slides including clones
        this.slides = this.container.querySelectorAll('.ict-slide');
    }

    _createDots() {
        if (!this.dotsContainer) return;
        this.dotsContainer.innerHTML = '';
        const totalDotPages = Math.ceil(this.realCount / this.itemsPerView);
        for (let dotIndex = 0; dotIndex < totalDotPages; dotIndex++) {
            const dot = document.createElement('button');
            dot.classList.add('ict-slider-dot');
            if (dotIndex === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this._goToSlide(dotIndex * this.itemsPerView));
            this.dotsContainer.appendChild(dot);
        }
    }

    _updateSlider(animate = true) {
        if (!animate) {
            this.container.style.transition = 'none';
        } else {
            this.container.style.transition = `transform ${this.options.speed || 300}ms ease`;
        }

        const slideEl = this.slides[0];
        const slideWidth = slideEl ? slideEl.offsetWidth : 0;
        this.container.style.transform = `translateX(${-(this.currentIndex * slideWidth)}px)`;

        // Buttons: based on real slides only (not clones)
        const realIndex = this._getRealIndex();
        if (this.prevBtn) this.prevBtn.disabled = realIndex === 0;
        if (this.nextBtn) this.nextBtn.disabled = realIndex >= this.realCount - this.itemsPerView;

        if (this.dotsContainer) {
            const activeDot = Math.floor(realIndex / this.itemsPerView);
            this.dotsContainer.querySelectorAll('.ict-slider-dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === activeDot);
            });
        }
    }

    _goToSlide(index) {
        const boundedIndex = Math.max(0, Math.min(index, Math.max(0, this.realCount - this.itemsPerView)));
        this.currentIndex = this.options.autoSlide ? boundedIndex + this.cloneOffset : boundedIndex;
        this._updateSlider();
    }

    _nextSlide(wrap = false) {
        const maxRealIndex = Math.max(0, this.realCount - this.itemsPerView);
        const realIndex = this._getRealIndex();

        if (realIndex < maxRealIndex) {
            this.currentIndex++;
            this._updateSlider();
        } else if (wrap) {
            this.currentIndex++;
            this._updateSlider(true);

            if (this.currentIndex >= this.realCount + this.cloneOffset) {
                const speed = this.options.speed || 300;
                setTimeout(() => {
                    this.currentIndex = this.cloneOffset;
                    this._updateSlider(false);
                    void this.container.offsetWidth;
                    this.container.style.transition = `transform ${speed}ms ease`;
                }, speed + 20);
            }
        }
        // manual click at end: do nothing (button is disabled anyway)
    }

    _prevSlide() {
        if (this._getRealIndex() > 0) {
            this.currentIndex--;
            this._updateSlider();
        }
    }

    _getRealIndex() {
        if (!this.options.autoSlide || this.realCount === 0) {
            return this.currentIndex;
        }

        const normalizedIndex = (this.currentIndex - this.cloneOffset + this.realCount) % this.realCount;
        return Math.min(normalizedIndex, Math.max(0, this.realCount - this.itemsPerView));
    }

    _setSlideSizes() {
        const totalSlides = this.slides.length;
        const pct = 100 / this.itemsPerView;
        this.container.style.width = `${totalSlides * pct}%`;
        this.slides.forEach(slide => {
            slide.style.flex = `0 0 ${100 / totalSlides}%`;
        });
    }

    _init() {
        this._updateItemsPerView();

        this.container.style.display = 'flex';
        this.container.style.transition = `transform ${this.options.speed || 300}ms ease`;

        if (this.options.autoSlide) {
            this._cloneSlides();
            this.currentIndex = this.cloneOffset;
        } else {
            this.realCount = this.slides.length;
        }

        this._setSlideSizes();
        this._createDots();
        this._updateSlider();

        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this._prevSlide());
        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this._nextSlide());

        if (this.options.autoSlide) {
            this.autoplayInterval = setInterval(() => this._nextSlide(true), this.options.autoplayTimeout || 5000);
            this.el.addEventListener('mouseenter', () => clearInterval(this.autoplayInterval));
            this.el.addEventListener('mouseleave', () => {
                if (this.options.autoSlide) {
                    this.autoplayInterval = setInterval(() => this._nextSlide(true), this.options.autoplayTimeout || 5000);
                }
            });
        }

        window.addEventListener('resize', () => {
            this._updateItemsPerView();
            if (this.options.autoSlide) {
                this._cloneSlides();
            } else {
                this.realCount = this.slides.length;
            }
            this._setSlideSizes();
            this._createDots();
            this._goToSlide(0);
        });
    }
}

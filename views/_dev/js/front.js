/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

class MdgHomePush {
  constructor() {
    this.slidesContainer = '#mdghomepush-slides-wrap'
    this.$slidesContainer = document.getElementById('mdghomepush-slides-wrap');
    this.initializeSlides();

    this.pushesContainer = '#mdghomepush-pushes-wrap'
    this.$pushesContainer = document.getElementById('mdghomepush-pushes-wrap');
    this.initializePushes();

  }

  initializeSlides() {
    if (typeof (this.$slidesContainer) != 'undefined' && this.$slidesContainer != null) {
      this.slidesOptions = {
        type: 'loop',
        perPage: 1,
        autoplay: mdgHomeSlide_break > 0,
        waitForTransition: mdgHomeSlide_break,
        speed: mdgHomeSlide_transition,
        lazyLoad: 'nearby',
        cover: true,
        height: mdgHomeSlide_height
      }

      switch (mdgHomeSlide_axe) {
        case 1:
          this.slidesOptions.direction = 'ttb'
          break
        case 2:
          this.slidesOptions.type = 'fade'
          this.slidesOptions.rewind = true
          break
      }

      this.renderSlides();
    }
  }

  initializePushes() {
    if (typeof (this.$pushesContainer) != 'undefined' && this.$pushesContainer != null) {
      this.renderPushes();
    }
  }

  renderSlides() {
    new Splide(this.slidesContainer, this.slidesOptions).mount();
  }

  renderPushes() {
    const $images = this.$pushesContainer.querySelectorAll('li img');
    [...$images].forEach(($image) => {
      var image = new Image();
      image.src = $image.dataset.src;
      image.onload = () => {
        $image.src = $image.dataset.src;
        if (typeof $image.dataset.ismain && $image.dataset.ismain == 'true') {
          $image.parentNode.parentNode.classList.add('initialized');
        }
      };
    });
  }
}

new MdgHomePush;
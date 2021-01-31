/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

class MdgHomePush_Slide {
  constructor() {
    this.container = '#mdghomepush-slides-wrap'

    this.options = {
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
        this.options.direction = 'ttb'
        break
      case 2:
        this.options.type = 'fade'
        this.options.rewind = true
        break
    }

    this.render();
  }

  render() {
    new Splide(this.container, this.options).mount();
  }

}

new MdgHomePush_Slide;
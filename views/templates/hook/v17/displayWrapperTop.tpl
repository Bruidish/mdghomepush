{**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 *}

{if $elements && $displayFullscreen}
  <div id="mdghomepush-slides-wrap" class="mdghomepush-slides-wrap-full splide">
    <div class="splide__track">
      <ul class="splide__list">
        {foreach $elements as $element}
          {include "module:mdghomepush/views/templates/hook/v17/_partials/slide.tpl" fullscreen=true}
        {/foreach}
      </ul>
    </div>
    <div class="splide__arrows">
      <button class="splide__arrow splide__arrow--prev"><span class="material-icons">chevron_left</span></button>
      <button class="splide__arrow splide__arrow--next"><span class="material-icons">chevron_right</span></button>
    </div>
  </div>
{/if}
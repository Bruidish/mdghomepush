{**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 *}

{if $elements}
  <div id="mdg-homepush">
    <ul class="col-xs-12">
      {foreach $elements as $element}
        {include "module:mdghomepush/views/templates/hook/v16_partials/slide.tpl"}
      {/foreach}
    </ul>
  </div>
{/if}
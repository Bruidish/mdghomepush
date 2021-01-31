{**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 *}

{if $elements}
  <div id="mdghomepush-pushes-wrap">
    <ul class="row">
      {foreach $elements as $element}
        {include "module:mdghomepush/views/templates/hook/v17/_partials/push.tpl"}
      {/foreach}
    </ul>
  </div>

  {if $mdgHomePush_height}
  <style>
    #mdghomepush-pushes-wrap > ul > li {
      height: {$mdgHomePush_height}px;
    }
  </style>
  {/if}
{/if}
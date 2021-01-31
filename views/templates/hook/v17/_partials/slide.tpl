{**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 *}

{if $element}
    <li {if $element->html_id}id="{$element->html_id}"{/if} class="mdghome-slide splide__slide {$element->html_class}">
        <img class="img-fluid" src="{$element->image}" alt="{$element->title|escape:'quotes'}" />
        {if $element->link}<a href="{$element->link}" {if $element->link_target == 1}target="_blank"{/if}>{else}<div>{/if}
        {if $element->display_title || $element->display_text}
            <div class="{if $fullscreen}container{else}inner-content{/if}">
                <div class="mdghome-slide-caption p-2" {if $element->color_bg || $element->color_text}style="{if $element->color_bg}background-color: {$element->color_bg};{/if}{if $element->color_text}color: {$element->color_text}{/if}"{/if}>
                {if $element->display_title}
                    <h2 class="h2 text-center mb-2">{$element->title}</h2>
                {/if}
                {if $element->display_text}
                    {$element->text nofilter}
                {/if}
                </div>
            </div>
        {/if}
        {if $element->link}</a>{else}</div>{/if}
    </li>
{/if}
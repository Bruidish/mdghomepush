{**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 *}

{if $element}
    <li {if $element->html_id}id="{$element->html_id}"{/if} class="mdghome-push col-12 {$element->html_class} {if $element->image_hover}hasImgHover{/if}">
        {if $element->link}<a href="{$element->link}" {if $element->link_target == 1}target="_blank"{/if}>{else}<div>{/if}
        <img src="{$element->image}" alt="{$element->title|escape:'quotes'}" />
        {if $element->image_hover}
            <img src="{$element->image_hover}" alt="{$element->title|escape:'quotes'}" />
        {/if}
        <div class="mdghome-push-caption m-1" {if $element->color_text}style="color: {$element->color_text}"{/if}>
            <div class="p-2">
                {if $element->display_title}
                    <h2 class="h2 text-center mb-2">{$element->title}</h2>
                {/if}
                {if $element->display_text}
                    {$element->text nofilter}
                {/if}
            </div>
            {if $element->color_bg}
                <div class="mdghome-push-bg" style="background-color: {$element->color_bg};"></div>
            {/if}
        </div>
        {if $element->link}</a>{else}</div>{/if}
    </li>
{/if}
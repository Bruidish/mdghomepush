<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 2.0.0 - 2019-10-31
 * @copyright 1019
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

/**
 * Module autoloader
 */
spl_autoload_register(function ($className) {
    $baseNamespace = "mdg\\homepush";
    $regNamespace = "mdg\\\\homepush";

    if (preg_match("/^{$regNamespace}\\\\(Controllers|Models|Traits|Forms)(\\\\[a-z]+)?\\\\([a-z\\\\]*)$/i", $className, $matches) && count($matches) >= 3) {
        $matches[2] = str_replace('\\', '', $matches[2]);
        $filePath = "$matches[1]/" . ($matches[2] ? $matches[2] . '/' : "") . "$matches[3]";
        $classNamespace = "$matches[1]\\" . ($matches[2] ? $matches[2] . '\\' : "") . "$matches[3]";

        $exists = [
            'override' => false,
            'version' => false,
        ];

        if (file_exists(__DIR__ . "/../src/core/{$filePath}.php")) {
            require __DIR__ . "/../src/core/{$filePath}.php";
        }
        if (version_compare(_PS_VERSION_, '1.7', '<') && file_exists(__DIR__ . "/../src/v16/{$filePath}.php")) {
            $exists['version'] = 'v16';
            require __DIR__ . "/../src/v16/{$filePath}.php";
        } elseif (version_compare(_PS_VERSION_, '1.6.9', '>') && version_compare(_PS_VERSION_, '1.7.9', '<') && file_exists(__DIR__ . "/../src/v17/{$filePath}.php")) {
            $exists['version'] = 'v17';
            require __DIR__ . "/../src/v17/{$filePath}.php";
        }
        if (file_exists(__DIR__ . "/../src/override/{$filePath}.php")) {
            $exists['override'] = true;
            require __DIR__ . "/../src/override/{$filePath}.php";
        }

        if ($exists['override'] !== false) {
            class_alias("{$baseNamespace}\\override\\$classNamespace", $className, false);
        } elseif ($exists['version'] !== false) {
            class_alias("{$baseNamespace}\\{$exists['version']}\\$classNamespace", $className, false);
        } else {
            class_alias("{$baseNamespace}\\core\\$classNamespace", $className, false);
        }
    }
});

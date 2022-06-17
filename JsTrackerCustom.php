<?php
/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\JsTrackerCustom;

use Piwik\Plugin;

/**
 *
 */
class JsTrackerCustom extends Plugin
{
    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function getClientSideTranslationKeys(&$result)
    {
        $result[] = 'JsTrackerCustom_AddCustomJs';
        $result[] = 'JsTrackerCustom_JsTrackerCustom';
    }
}

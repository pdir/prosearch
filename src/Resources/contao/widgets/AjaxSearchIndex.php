<?php

declare(strict_types=1);

/*
 * Prosearch bundle for Contao Open Source CMS
 *
 * @package    prosearch
 * @license    ProSearch is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
 * @author     Alexander Naumov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProSearch;

use Contao\Config;
use Contao\Widget;

/**
 * Class AjaxSearchIndex.
 */
class AjaxSearchIndex extends Widget
{
    /**
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * @return string
     */
    public function generate()
    {
        // translation
        $bStr = $GLOBALS['TL_LANG']['MSC']['ajaxSearchIndex']['button'];

        // load active modules
        $activeModules = deserialize(Config::get('searchIndexModules'));

        // encode to json
        $json = json_encode($activeModules);

        // set to global js varaible
        $GLOBALS['TL_MOOTOOLS'][] = '<script>var proSearchActiveModules = '.$json.';</script>';

        // load js
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/prosearch/assets/JsIndex.js|static';

        return '<div class="index_list"><ul class="ul"></ul></div><div class="ajaxSearchIndex"><a class="tl_submit" style="margin-bottom: 5px; margin-top: 5px" onclick="Backend.getScrollOffset();return AjaxRequest.ajaxSearchIndex()">'.$bStr.'</a></div>';
    }
}

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

class InitializeProSearch
{
    private $strPlaceholder = '<!-- ### %PROSEARCH_SCRIPT_TAG% ### -->';

    public function setScriptPlaceholder(): void
    {
        $GLOBALS['TL_MOOTOOLS'][] = $this->strPlaceholder;
    }
}

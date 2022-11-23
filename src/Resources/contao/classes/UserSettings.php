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

use Contao\BackendUser;

class UserSettings
{
    private $blnInitialized = false;
    private $strPlaceholder = '<!-- ### %PROSEARCH_SCRIPT_TAG% ### -->';

    public function initializeSettings($strContent, $strTemplate)
    {
        if ('be_main' === $strTemplate && !$this->blnInitialized) {
            $objUser = BackendUser::getInstance();

            $arrSettings = [
                'enable' => true,
                'id' => $objUser->id,
                'shortcut' => $objUser->keyboard_shortcut ? $objUser->keyboard_shortcut : 'alt+m',
            ];

            if (isset($objUser->modules) && !empty($objUser->modules) && \is_array($objUser->modules)) {
                $arrSettings['enable'] = \in_array('prosearch_settings', $objUser->modules, true);
            }

            $strContent = str_replace($this->strPlaceholder, '<script>var UserSettings = '.json_encode($arrSettings).';</script>', $strContent);
            $this->blnInitialized = true;
        }

        return $strContent;
    }
}

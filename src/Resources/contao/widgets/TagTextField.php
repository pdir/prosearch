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

use Contao\Database;
use Contao\Environment;
use Contao\Input;

class TagTextField extends \Widget
{
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public function validator($varInput)
    {
        return parent::validator($varInput);
    }

    public function generate()
    {
        $strTags = Input::get('ps_tags');
        $strActionTag = Input::get('actionPSTag');
        $strRequestUri = Environment::get('requestUri');
        $strRequestUri = $this->removeRequestTokenFromUri($strRequestUri);

        if ($strActionTag && 'updateTags' === $strActionTag) {
            $this->updateTags($strTags);
        }

        if ($strActionTag && 'removeTags' === $strActionTag) {
            $this->removeTags($strTags);
        }

        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/prosearch/assets/vendor/mootagify.js|static';
        $GLOBALS['TL_CSS'][] = 'system/modules/prosearch/assets/css/mootagify-bootstrap.css|static';
        $GLOBALS['TL_CSS'][] = 'system/modules/prosearch/assets/css/mootagify.css|static';

        $objTags = Database::getInstance()->prepare('SELECT * FROM tl_prosearch_tags')->execute();
        $arrOptions = [''];

        while ($objTags->next()) {
            $arrOptions[] = $objTags->tagname;
        }

        $script = sprintf(
            '<script>'
            .'window.addEvent("domready", function(){
                var tagify = new mooTagify(document.id("tagWrap_%s"), null ,{
                    autoSuggest: true,
                    availableOptions: '.json_encode($arrOptions).'
                });
                tagify.addEvent("tagsUpdate", function(){
                    var tags = tagify.getTags();
                    document.id("ctrl_%s").set("value", tags.join());
                    new Request({url: "%s&actionPSTag=updateTags"}).get({"ps_tags": tags, "rt": Contao.request_token });
                });
                tagify.addEvent("tagRemove", function(tag){
                    var tags = tagify.getTags()
                    var deleted = tag;
                    document.id("ctrl_%s").set("value", tags.join());
                    new Request({url: "%s&actionPSTag=removeTags"}).get({ "ps_tags": deleted, "rt": Contao.request_token });
                });
            });'.'</script>',
            $this->strId,
            $this->strId,
            $strRequestUri,
            $this->strId,
            $strRequestUri
        );

        return sprintf(
            '<input type="hidden" id="ctrl_%s" name="%s" value="%s"><div id="tagWrap_%s" class="hide"> <div class="tag-wrapper"></div> <div class="tag-input"> <input type="text" id="listTags" class="tl_text" name="listTags" value="%s" placeholder="%s"> </div> <div class="clear"></div></div>'.$script.'',
            $this->strId,
            $this->strName,
            specialchars($this->varValue),
            $this->strId,
            specialchars($this->varValue),
            $GLOBALS['TL_LANG']['MSC']['TagTextField']['tag']
        );
    }

    public function updateTags($arrTags): void
    {
        if (!\is_array($arrTags)) {
            $this->sendRes();
        }

        $arrValues = $arrTags ?: [];
        $arrTagsExist = [];

        $tagsDB = Database::getInstance()->prepare('SELECT * FROM tl_prosearch_tags')->execute();

        while ($tagsDB->next()) {
            $arrTagsExist[] = $tagsDB->tagname;
        }

        foreach ($arrValues as $strTagName) {
            if (!\in_array($strTagName, $arrTagsExist, true)) {
                Database::getInstance()->prepare('INSERT INTO tl_prosearch_tags (tstamp,tagname) VALUES (?,?)')->execute(time(), $strTagName);
            }
        }

        $this->sendRes();
    }

    public function removeTags($strTag): void
    {
        if (!\is_string($strTag)) {
            $this->sendRes();
        }

        $strTagName = $strTag ?: '';
        $existInSearchDB = Database::getInstance()->prepare('SELECT * FROM tl_prosearch_data WHERE tags LIKE ? ORDER BY tstamp DESC LIMIT 10')->execute("%$strTagName%");

        if ($existInSearchDB->count() > 1) {
            $this->sendRes();
        }

        Database::getInstance()->prepare('DELETE FROM tl_prosearch_tags WHERE tagname = ?')->execute($strTagName);

        $this->sendRes();
    }

    public function sendRes(): void
    {
        header('Content-type: application/json');

        echo json_encode(['state' => '200']);

        exit;
    }

    private function removeRequestTokenFromUri($strRequest)
    {
        $arrRequestUri = explode('&', $strRequest);
        $arrTemps = [];

        foreach ($arrRequestUri as $strUriPart) {
            if ('rt' === substr($strUriPart, 0, 2)) {
                continue;
            }

            $arrTemps[] = $strUriPart;
        }

        return implode('&', $arrTemps);
    }
}

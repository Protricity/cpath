<?php
namespace CPath\Framework\API\Fragments;

use CPath\Config;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Util\HTMLRenderUtil;

class APIDebugFormFragment extends APIFormFragment{

    /**
     * @param IAPI $API
     * @param ITableTheme $Theme
     */
    public function __construct(IAPI $API, ITableTheme $Theme = null) {
        parent::__construct($API, $Theme);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('JSON', 'form-button-submit-json');
        $Util->button('XML', 'form-button-submit-xml');
        $Util->button('TEXT', 'form-button-submit-text');
        $Util->submit('POST', 'form-button-submit-post');
        $Util->button('Update URL', 'form-button-submit-post', array('onclick' => 'APIView.updateURL(this.form);'));
        $Util->button('DataResponse', 'form-button-submit-post', array('onclick' => 'jQuery(".apiview-response").toggle();'));
        if(false) {
        ?>
        <input type="button" value="JSON" class="form-button-submit-json"/>
        <input type="button" value="XML" class="form-button-submit-xml" />
        <input type="button" value="TEXT" class="form-button-submit-text" />
        <input type="submit" value="POST"/>
        <!--input type="button" value="JSON Object (POST)" onclick="APIView.submit('', this.form, 'json', 'POST', true);" /-->
        <input type="button" value="Update URL" onclick="APIView.updateURL(this.form);" />
        <input type="button" value="Response" onclick="jQuery('.apiview-response').toggle();" />
        <!--input id="btnCustomSubmit" type="button" value="Custom" onmousemove="jQuery('#spanCustom').fadeIn();" onclick="APIView.submit('', this.form, '', jQuery('#txtCustomMethod').val(), false, jQuery('#txtCustomText').val());" />
        <span id="spanCustom" style="display: none">
            <label>Accepts:
                <input  id="txtCustomText" type="text" value="*/*" size="4" />
            </label>
            <label>Method:
                <input id="txtCustomMethod" type="text" value="GET" size="4" />
            </label>
        </span-->
    <?php
        }
    }
}

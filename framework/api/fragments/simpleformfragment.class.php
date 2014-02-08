<?php
namespace CPath\Handlers\API\Fragments;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Handlers\Interfaces\IAttributes;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Handlers\Util\Attr;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Framework\Request\Interfaces\IRequest;

class SimpleFormFragment extends AbstractFormFragment{

    /**
     * @param ITableTheme $Theme
     */
    public function __construct(ITableTheme $Theme = null) {
        parent::__construct($Theme);
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderForm(IRequest $Request, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        $API = $this->getAPI();
        $Fields = $API->getFields();
        $Route = $API->loadRoute();

        $domainPath = Config::getDomainPath();
        $route = $Route->getPrefix();
        list($method, $path) = explode(' ', $route, 2);
        $path = rtrim($domainPath, '/') . $path;

        $Util = new HTMLRenderUtil($Request);

        $Attr->addClass('apiview-form');
        $Attr->add('enctype', 'multipart/form-data');
        $Attr->add('method', $method);
        $Attr->add('action', $path);

        $Util->formOpen($Attr);

        $Table = new TableThemeUtil($Request, $this->getTheme());
        $Table->renderStart(null, 'apiview-table'); // Describable::get($API)->getDescription()
        $Table->renderHeaderStart();
        $Table->renderTD('Description', 'table-field-description');
        $Table->renderTD('Value',       'table-field-input');

        if(!$Fields) {
            $Table->renderRowStart();
            $Table->renderTD('&nbsp;',      'table-field-description');
            $Table->renderTD('&nbsp;',      'table-field-input');
        } else foreach($Fields as $name=>$Field) {
            $desc = Describable::get($Field)->getDescription();;

            $Table->renderRowStart();
            $Table->renderTD($desc,     'table-field-description');
            $Table->renderDataStart(    'table-field-input');
            if(isset($_GET[$name]))
                $Field->setValue($_GET[$name]);
            $Field->render($Request);
        }

        $Table->renderFooterStart();
        $Attr = new Attr('table-field-footer-buttons', 'text-align: left');
        $Table->renderDataStart($Attr, 5, 0);
        $this->renderFormButtons($Request);
        $Table->renderEnd();

        $Util->formClose();
    }
}

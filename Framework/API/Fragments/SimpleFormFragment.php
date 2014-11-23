<?php
namespace CPath\Framework\API\Fragments;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Field\Util\FieldUtil;
use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLRenderUtil;
use CPath\Render\HTML\Theme\ITableTheme;
use CPath\Render\HTML\Theme\Util\TableThemeUtil;
use CPath\Request\IRequest;
use CPath\Route\IRouteMap;

class SimpleFormFragment extends AbstractFormFragment{

    /**
     * @param \CPath\Render\HTML\Theme\ITableTheme $Theme
     */
    public function __construct(ITableTheme $Theme = null) {
        parent::__construct($Theme);
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest inst for this render
     * @param IAttributes $Attr
     * @throws \Exception
     * @return void
     */
    function renderForm(IRequest $Request, IAttributes $Attr=null) {
        $Attr = Attr::fromClass($Attr);

        $API = $this->getAPI();
        $Fields = $API->getFields();
        if(!$API instanceof IRouteMap)
            throw new \Exception("API Not routable");
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

            $RenderField = new FieldUtil($Field);
            $RenderField->renderHTML($Request);
        }

        $Table->renderFooterStart();
        $Attr = new Attr('table-field-footer-buttons', 'text-align: left');
        $Table->renderDataStart($Attr, 5, 0);
        $this->renderFormButtons($Request);
        $Table->renderEnd();

        $Util->formClose();
    }
}

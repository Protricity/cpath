<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Theme\Util;

use CPath\Base;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Request\IRequest;
use CPath\Framework\Render\Theme\Interfaces\ITheme;

class SearchFormUtil implements IDescribable, ISupportHeaders {
    private $mTheme, $mResponse, $mAPI, $mDescriptor, $mDescribable;
    private $mForm;
    private $mResponseBox;

    public function __construct(PDOSearchResponse $Response, ITheme $Theme=null) {
        $Query = $Response->getQuery();
        $this->mDescriptor = $Query->getDescriptor();
        $this->mAPI = $this->mDescriptor->getAPI();
        $this->mTheme = $Theme;
        $this->mResponse = $Response;
        $this->mDescribable = Describable::get($this->mAPI);

        $this->mForm = new APIDebugFormFragment($this->mAPI);
        $this->mResponseBox = new APIResponseBoxFragment($Theme);
    }

    /**
     * Write all support headers used by this IView instance
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $this->mForm->writeHeaders($Head);

        if($this->mResponseBox instanceof ISupportHeaders)
            $this->mResponseBox->writeHeaders($Head);

        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\searchformutil.css', true);
        $Head->writeScript(__NAMESPACE__ . '\assets\searchformutil.js', true);
    }

    function getQuery() {
        return $this->mResponse->getQuery();
    }

    public function renderForm(IRequest $Request) {
        $this->mTheme->renderFragmentStart($Request, $this, 'search-form-util');
            $this->mForm->renderHTML($Request);
            $this->mTheme->renderSearchContent($Request, $this->mResponse, 'search-content');
            $this->mResponseBox->renderResponseBox($Request);
        $this->mTheme->renderFragmentEnd($Request);
    }

    /**
     * Get the Object Title
     * @return String description for this Object
     */
    function getTitle() { return $this->mDescribable->getTitle(); }

    /**
     * Get the Object Description
     * @return String description for this Object
     */
    function getDescription() { return $this->mDescribable->getDescription(); }


    /**
     * Implement __toString
     * @return String simple description for this Object
     */
    function __toString() { return (String)$this->mDescribable; }

}
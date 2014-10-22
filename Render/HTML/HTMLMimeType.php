<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\HTML;

use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

final class HTMLMimeType extends MimeType
{
    /** @var IHTMLTemplate */
    //private $mContainer = null;
    public function __construct($typeName='text/html', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }

//    public function setRenderContainer(IHTMLTemplate $Container) {
//        $this->mContainer = $Container;
//    }
//
//    public function getRenderContainer() {
//        return $this->mContainer;
//    }

//
//    public function renderInContainer(IRequest $Request, IRenderHTML $Render) {
//        $c = sizeof($this->mContainers);
//
//        foreach($this->mContainers as $i=>$Container)
//            if(isset($this->mContainers[$i+1]))
//                $Container->addContent($this->mContainers[$i+1]);
//
//        $this->mContainers[$c-1]->addContent($Render);
//        $this->mContainers[0]->renderHTML($Request);
//        $this->mContainers[$c-1]->removeContent($Render);
//
//        foreach($this->mContainers as $i=>$Container)
//            if(isset($this->mContainers[$i+1]))
//                $Container->removeContent($this->mContainers[$i+1]);
//    }
}


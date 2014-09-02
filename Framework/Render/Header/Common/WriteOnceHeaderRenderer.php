<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:29 PM
 */
namespace CPath\Framework\Render\Header\Common;

use CPath\Framework\Render\Header\Interfaces\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;

class WriteOnceHeaderRenderer implements IHeaderWriter
{
    private $mWrittenHeaders = array();

    /**
     * Write a header as raw html
     * @param String $html
     * @return \CPath\Framework\Render\Header\\CPath\Framework\Render\Header\Interfaces\IHeaderWriter return instance of self
     */
    function writeHTML($html) {
        echo RI::ni(), $html;
        return $this;
    }

    /**
     * Write a <script> header only the first time it's encountered
     * @param String $src the script url
     * @param bool $defer
     * @param null $charset
     * @return \CPath\Framework\Render\Header\\CPath\Framework\Render\Header\Interfaces\IHeaderWriter return instance of self
     */
    function writeScript($src, $defer = false, $charset = null) {
        if(!in_array($src, $this->mWrittenHeaders)) {
            echo RI::ni(), "<script src='", $src, "'";
            if($defer)
                echo " defer='defer'";
            if($charset)
                echo " charset='", $charset, "'";
            echo "></script>";
            $this->mWrittenHeaders[] = $src;
        }
        return $this;
    }

    /**
     * Write a <link type="text/css"> header only the first time it's encountered
     * @param String $href the stylesheet url
     * @return \CPath\Framework\Render\Header\\CPath\Framework\Render\Header\Interfaces\IHeaderWriter return instance of self
     */
    function writeStyleSheet($href) {
        if(!in_array($href, $this->mWrittenHeaders)) {
            echo RI::ni(), "<link rel='stylesheet' href='", $href, "' />";
            $this->mWrittenHeaders[] = $href;
        }
        return $this;
    }
}
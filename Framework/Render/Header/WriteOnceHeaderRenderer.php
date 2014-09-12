<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:29 PM
 */
namespace CPath\Framework\Render\Header;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;

class WriteOnceHeaderRenderer implements IHeaderWriter
{
    private $mWrittenHeaders = array();

    /**
     * Write a header as raw html
     * @param String $html
     * @return IHeaderWriter return instance of self
     */
    function writeHTML($html) {
        echo RI::ni(), $html;
        return $this;
    }

    /**
     * Write a <script> header only the first time it's encountered
     * @param String $scriptPath the script url
     * @param bool $defer
     * @param null $charset
     * @return IHeaderWriter return instance of self
     */
    function writeScript($scriptPath, $defer = false, $charset = null) {
        $scriptPath = str_replace('\\', '/', $scriptPath);
        if(!in_array($scriptPath, $this->mWrittenHeaders)) {
            echo RI::ni(), "<script src='", $scriptPath, "'";
            if($defer)
                echo " defer='defer'";
            if($charset)
                echo " charset='", $charset, "'";
            echo "></script>";
            $this->mWrittenHeaders[] = $scriptPath;
        }
        return $this;
    }

    /**
     * Write a <link type="text/css"> header only the first time it's encountered
     * @param String $styleSheetPath the stylesheet url
     * @return IHeaderWriter return instance of self
     */
    function writeStyleSheet($styleSheetPath) {
        $styleSheetPath = str_replace('\\', '/', $styleSheetPath);
        if(!in_array($styleSheetPath, $this->mWrittenHeaders)) {
            echo RI::ni(), "<link rel='stylesheet' href='", $styleSheetPath, "' />";
            $this->mWrittenHeaders[] = $styleSheetPath;
        }
        return $this;
    }
}
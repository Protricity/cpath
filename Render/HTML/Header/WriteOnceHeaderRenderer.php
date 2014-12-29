<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:29 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Render\Helpers\RenderIndents as RI;

class WriteOnceHeaderRenderer implements IHeaderWriter
{
    private $mWrittenHeaders = array();
	private $mReplace = array();

    public function __construct() {
        $fileName = dirname($_SERVER['SCRIPT_FILENAME']);
        $name = dirname($_SERVER['SCRIPT_NAME']);
        $this->mReplace[$name] = $fileName;
    }

    /**
     * Write a header as raw html
     * @param String $html
     * @return IHeaderWriter return inst of self
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
     * @return IHeaderWriter return inst of self
     */
    function writeScript($scriptPath, $defer = false, $charset = null) {
	    foreach($this->mReplace as $name => $filePath)
		    $scriptPath = str_replace($filePath, $name, $scriptPath);
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
     * @return IHeaderWriter return inst of self
     */
    function writeStyleSheet($styleSheetPath) {
	    foreach($this->mReplace as $name => $filePath)
		    $styleSheetPath = str_replace($filePath, $name, $styleSheetPath);
	    
        $styleSheetPath = str_replace('\\', '/', $styleSheetPath);
        if(!in_array($styleSheetPath, $this->mWrittenHeaders)) {
            echo RI::ni(), "<link rel='stylesheet' href='", $styleSheetPath, "' />";
            $this->mWrittenHeaders[] = $styleSheetPath;
        }
        return $this;
    }
}
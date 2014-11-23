<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:29 PM
 */
namespace CPath\Framework\Render\Header;

use CPath\Framework\Render\Util\RenderIndents as RI;

class WriteOnceHeaderRenderer implements IHeaderWriter
{
    private $mWrittenHeaders = array();
    private $mRootPath = null;

    public function __construct($rootPath = null) {

        //$autoloads = Autoloader::getLoaderPaths();

        if($rootPath === null) {
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            $rootPath = realpath(getcwd());
            $rootPath = substr($rootPath, 0, strlen($scriptPath));
        }
        $this->mRootPath = $rootPath;
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
        if(strpos($scriptPath, $this->mRootPath) === 0) {
            $scriptPath = substr($scriptPath, strlen($this->mRootPath));
        }
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
        if(strpos($styleSheetPath, $this->mRootPath) === 0) {
            $styleSheetPath = substr($styleSheetPath, strlen($this->mRootPath));
        }
        $styleSheetPath = str_replace('\\', '/', $styleSheetPath);
        if(!in_array($styleSheetPath, $this->mWrittenHeaders)) {
            echo RI::ni(), "<link rel='stylesheet' href='", $styleSheetPath, "' />";
            $this->mWrittenHeaders[] = $styleSheetPath;
        }
        return $this;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:04 PM
 */
namespace CPath\Request\MimeType;

abstract class MimeType implements IRequestedMimeType
{
    private $mName;
    private $mNext;
    private $mSent = false;

    public function __construct($typeName, IRequestedMimeType $nextMimeType=null) {
        $this->mName = $typeName;
        $this->mNext = $nextMimeType;
    }

    /**
     * Get the Mime type as a string
     * @return String
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Get next available mimetype or null if no more mimetypes were requested
     * @return IRequestedMimeType
     */
    function getNextMimeType() {
        return $this->mNext;
    }

//    protected function headersSent() {
//        return $this->mSent || headers_sent();
//    }

    /**
     * Send response headers for this mime type
     * @param int $code HTTP response code
     * @param String $message response message
     * @return bool returns true if the headers were sent, false otherwise
     */
//    function sendHeaders($code = 200, $message = 'OK') {
//        if($this->headersSent())
//            return false;
//
//        header("HTTP/1.1 " . $code . " " . preg_replace('/[^\w -]/', '', $message));
//        header("Content-Type: " . $this->mName);
//
//        $this->mSent = true;
//        return true;
//    }

    // Static


    static function select() {
        static $MimeType = null;
        if ($MimeType) return $MimeType;

        $accepts = 'text/html';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = $_SERVER['HTTP_ACCEPT'];
        } else if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value)
                if ($key == 'Accept')
                    $accepts = $value;
        }

        $types = array();
        foreach (explode(',', $accepts) as $type) {
            list($type) = explode(';', $type, 2);
            $type = trim($type);
            switch (strtolower($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $types[] = new \CPath\Render\JSON\JSONMimeType($type);
                    break;
                case 'application/xml':
                case 'text/xml':
                    $types[] = new \CPath\Render\XML\XMLMimeType($type);
                    break;
                case 'text/html':
                case 'application/xhtml+xml':
                    $types[] = new \CPath\Render\HTML\HTMLMimeType($type);
                    break;
                case 'text/plain':
                    $types[] = new \CPath\Render\Text\TextMimeType($type);
                    break;
                default:
                    $types[] = new UnknownMimeType($type);
            }
        }

        $MimeType = $types;
        return $MimeType;
    }
}
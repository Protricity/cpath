<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 8:02 PM
 */
namespace CPath\Request\Web;

use CPath\Render\Text\TextMimeType;
use CPath\Request\Log\ILogListener;

class CLIWebRequest extends WebRequest
{
    public function __construct($path = null, $args = array()) {
        $flags = 0;
        if(isset($args['v']) || isset($args['verbose']))
            $flags |= ILogListener::VERBOSE;

        parent::__construct('CLI', $path, $args, new TextMimeType($flags));
    }
}
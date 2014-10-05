<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 8:02 PM
 */
namespace CPath\Request\Web;

use CPath\Render\Text\TextMimeType;
use CPath\Request\Form\IFormRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\RequestException;

class CLIWebRequest extends WebRequest implements IFormRequest
{
    public function __construct($path = null, $args = array()) {
        $flags = 0;
        if(isset($args['v']) || isset($args['verbose']))
            $flags |= ILogListener::VERBOSE;

        parent::__construct('CLI', $path, $args, new TextMimeType($flags));
    }

    /**
     * Get a request value by parameter name or null if not found
     * @param string $fieldName the parameter name
     * @param string $description [optional] description for this prompt
     * @param int $flags use ::PARAM_REQUIRED for required fields
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
    function getFormValue($fieldName, $description = null, $flags = 0) {
        return $this->getParamValue($fieldName, $description, $flags);
    }
}
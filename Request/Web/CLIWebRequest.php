<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 8:02 PM
 */
namespace CPath\Request\Web;

use CPath\Render\Text\TextMimeType;
use CPath\Request\CLI\CommandString;
use CPath\Request\Form\IFormRequest;
use CPath\Request\Log\ILogListener;

class CLIWebRequest extends WebRequest implements IFormRequest
{
    public function __construct($path = null, $args = array()) {
		$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	    $query = urldecode($query);
	    $args = CommandString::parseArgs($query);

        $flags = 0;
        if(isset($args['v']) || isset($args['verbose']))
            $flags |= ILogListener::VERBOSE;

        parent::__construct('CLI', $path, $args, new TextMimeType($flags));
    }

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		// TODO: unfinished
		return $this[$fieldName];
	}

//
//	/**
//	 * Return a request value
//	 * @param String|IRequestParameter $Parameter string or inst
//	 * @internal param null|String $description
//	 * @return mixed the validated parameter value
//	 */
//	function getValue(IRequestParameter $Parameter) {
//		if(!$Parameter instanceof IRequestParameter)
//			$Parameter = new Parameter($Parameter, $description);
//
//		$this->addParam($Parameter);
//
//		$value =
//			$this->getArgumentValue($Parameter->getName()) ?:
//				$this->getRequestValue($Parameter->getName());
//
//		return $Parameter->validateParameter($this, $value);
//
//	    if($value = $this->getArgumentValue($fieldName))
//		    return $value;
//
//        return $this->getValue($fieldName, $description, $flags);
//    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:14 PM
 */
namespace CPath\Request\Web;

use CPath\Describable\IDescribable;

class WebFormRequest extends WebRequest
{
    private $mValueSource = null;


    public function __construct($method, $path = null, $params = array()) {
//        if (!$path)
//            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        parent::__construct($method, $path, $params);
    }

    protected function getParamValue($paramName) {
        $values = $this->getAllValues();
        if(!empty($values[$paramName]))
            return $values[$paramName];

        if($value = parent::getParamValue($paramName))
            return $value;

        return null;
    }

    protected function getAllValues() {
        if ($this->mValueSource !== null)
            return $this->mValueSource;

        if ($this->getHeader('Content-Type') === 'application/json') {
            $input = file_get_contents('php://input');
            $this->mValueSource = json_decode($input, true);
            return $this->mValueSource;
        }

        return $this->mValueSource = $_POST;
    }
}
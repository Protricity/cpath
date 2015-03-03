<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 8:15 PM
 */
namespace CPath\Render\JSON;

use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;


class JSONMapRenderer extends AbstractMapRenderer
{
    const DELIMIT = ', ';
	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

    public function __clone() {
        parent::__clone();
    }

    protected function renderString($string) {
        echo json_encode($string);
    }

    protected function renderNull() {
        echo '""';
    }

//    protected function renderDelimiter() {
//        echo self::DELIMIT;
//    }

	protected function renderNamedValue($name, $value) {
		echo json_encode($name), ':';
		$ret = parent::renderNamedValue($name, $value);

		return $ret;
	}

	protected function renderValue($value) {
		$ret = parent::renderValue($value);

		return $ret;
	}


	protected function renderStart($isArray) {
		if ($isArray) {
			echo '[';

		} else {
			echo '{';
		}
	}

	protected function renderEnd($isArray) {
		if ($isArray === false) {
			echo '}';

		} else if ($isArray === true) {
			echo ']';

		} else {
			echo '{}';
		}
	}
}
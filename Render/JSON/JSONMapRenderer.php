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
    private $count = 0;
	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

    public function __clone() {
        $this->count = 0;
        parent::__clone();
    }

    protected function renderString($string) {
        echo json_encode($string);
    }

    protected function renderNull() {
        echo '""';
    }

	protected function renderNamedValue($name, $value) {
        if ($this->count > 0) {
            echo self::DELIMIT;
        }
		echo json_encode($name), ':';
		$ret = parent::renderNamedValue($name, $value);

        $this->count++;
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
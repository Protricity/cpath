<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/13/2015
 * Time: 10:51 AM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Schema\PDO\PDOSelectBuilder;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Validation\IRequestValidation;

class HTMLPDOSearchTableBody extends HTMLPDOQueryTableBody implements IRequestValidation
{
	private $searchColumn;

	public function __construct(PDOSelectBuilder $Query, Array $columnNames = array(), $elmName=null) {
		parent::__construct($Query, $columnNames, $elmName);
	}

	public function addSearchColumn($columnName, $keyName) {
		$this->searchColumn[$keyName] = $columnName;
		if(!isset($this->columns[$keyName]))
			$this->addColumn($columnName, $keyName);
		return $this;
	}

	/**
	 * Validate the request
	 * @param IRequest $Request
	 * @throw Exception if validation failed
	 * @return array|void optionally returns an associative array of modified field names and values
	 */
	function validateRequest(IRequest $Request) {
		foreach($this->searchColumn as $keyName => $columnName) {
			if(!empty($Request['search-' . $keyName])) {
				$value = $Request['search-' . $keyName];
				$this->Query->where($columnName, $value);
			}
		}
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {

		parent::renderHTML($Request, $Attr, $Parent);

		$elmName = $this->getElementType('tfoot');

		echo RI::ni(), "<", $elmName, ">";
		echo RI::ai(1);

		echo RI::ni(), "<tr>";
		echo RI::ai(1);

		foreach ($this->getColumns() as $title => $name) {
			echo RI::ni(), "<td>";
			if(array_search($name, $this->searchColumn)) {
				$size = strlen($name);
				echo "<input name='search-{$name}' placeholder='Search {$title}' size='16' class='input search transparent'";
				if(!empty($Request['search-' . $name])) {
					$value = $Request['search-' . $name];
					echo " value='", $value, "'";
				}
				echo "/>";

			}
			echo "</td>";
		}

		echo RI::ai(-1);
		echo RI::ni(), "</tr>";

		echo RI::ai(-1);
		echo RI::ni(), "</", $elmName, ">";
	}

}
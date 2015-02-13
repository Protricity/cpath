<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/13/2015
 * Time: 11:44 AM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Map\CallbackKeyMapper;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Schema\PDO\PDOSelectBuilder;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\IHTMLElement;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLPDOQueryTableBody implements IHTMLElement
{
	protected $Query;
	protected $columns;
	protected $mElmentName;

	public function __construct(PDOSelectBuilder $Query, Array $columnNames = array(), $elmName=null) {
		$this->Query   = $Query;
		$this->columns = $columnNames;
		$this->mElmentName = $elmName;
	}

	public function addColumn($columnName, $keyName) {
		$this->columns[$keyName] = $columnName;
		return $this;
	}

	protected function getColumns() {
		$columns = $this->columns;
		if (!$columns) {
			$row = $this->Query->fetch();
			$this->Query->rewind();
			if ($row instanceof IKeyMap) {
				$row->mapKeys(
					new CallbackKeyMapper(
						function ($key, $value) use (&$columns) {
							$columns[ucwords(preg_replace('/[_-]/', ' ', $key))] = $key;
						}
					)
				);
			} else {
				foreach ((array)$row as $key => $value)
					$columns[ucwords(preg_replace('/[_-]/', ' ', $key))] = $key;
			}
			$this->columns = $columns;
		}

		return $columns;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$elmName = $this->getElementType('thead');

		echo RI::ni(), "<", $elmName, ">";
		echo RI::ai(1);

		echo RI::ni(), "<tr>";
		echo RI::ai(1);

		foreach ($this->getColumns() as $title => $name) {
			echo RI::ni(), "<th>", $title, "</th>";
		}

		echo RI::ai(-1);
		echo RI::ni(), "</tr>";

		echo RI::ai(-1);
		echo RI::ni(), "</", $elmName, ">";
	}

	/**
	 * Return element parent or null
	 * @return IHTMLContainer|null
	 */
	function getParent() {
		return null;
	}

	/**
	 * Get HTMLElement node type
	 * @param string $default
	 * @return String
	 */
	function getElementType($default='tbody') {
		return $this->mElmentName ?: $default;
	}

}
<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 12:00 AM
 */
namespace CPath\Render\HTML\Pagination;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;


class HTMLPagination implements IRenderHTML
{
	private $row_count;
	private $current_page;
	private $total;

	/**
	 * @param $row_count
	 * @param int $current_page
	 * @param null $total
	 */
	public function __construct($row_count, $current_page=0, $total=null) {
		$this->row_count = $row_count;
		$this->current_page = $current_page;
		$this->total = $total;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$url = null;
		foreach($Request as $key => $value)

			if($key !== 'page' && is_scalar($value))
				$url = ($url ? $url . '&' : '?') . $key . '=' . $value;
		$url = ($url ? $url . '&' : '?') . 'page=';
		$url = $Request->getDomainPath() . ltrim($Request->getPath(), '/') . $url;

		echo RI::ni(), "<span class='pages'>";
		echo RI::ai(1);
		echo RI::ni(), "<span class='page-first'><a href='", $url, 0, "'>first</a></span>";

		$pages = array();
		for($i=$this->current_page - 1; $i>=0; $i-= floor($i/2) ?: 1)
			$pages[] = $i;

		sort($pages);
		foreach($pages as $i)
			echo RI::ni(), "<span class='page'><a href='", $url, $i, "'>", $i, "</a></span>";

		echo RI::ni(), "<span class='page-current'><a href='", $url, $this->current_page, "'>(", $this->current_page, ")</a></span>";

		$next = $this->current_page + 1;
		echo RI::ni(), "<span class='page-next'><a href='", $url, $next, "'>next</a></span>";

		if($this->total) {
			$totalPages = $this->total / $this->row_count;

			for($i=$this->current_page + 1; $i < $totalPages; $i+= $i - $this->current_page)
				echo RI::ni(), "<span class='page'><a href='", $url, $i, "'>", $i, "</a></span>";

			$last = $this->total / $this->row_count;
			echo RI::ni(), "<span class='page-last'><a href='", $url, $last, "'>last</a></span>";

		}

		echo RI::ai(-1);
		echo RI::ni(), "</span>";
	}
}
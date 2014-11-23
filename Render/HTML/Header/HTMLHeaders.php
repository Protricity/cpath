<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 8:17 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Request\IRequest;

class HTMLHeaders implements IHTMLSupportHeaders
{
	private $mHeaders = array();
	public function __construct($header, $_header=null) {
		$this->mHeaders = is_array($header) ? $header : func_get_args();
	}

	public function addHeader($header) {
		$this->mHeaders[] = $header;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		foreach($this->mHeaders as $header) {
			if($header instanceof IHTMLSupportHeaders) {
				$header->writeHeaders($Request, $Head);
			} else {
				$ext = pathinfo($header, PATHINFO_EXTENSION);
				switch(strtolower($ext)) {
					case 'css': $Head->writeStyleSheet($header); break;
					case 'js': $Head->writeScript($header); break;
					default:
						$Head->writeHTML($header);
						break;
				}
			}
		}
	}
}
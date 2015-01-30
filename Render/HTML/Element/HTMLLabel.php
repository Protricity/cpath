<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:36 AM
 */
namespace CPath\Render\HTML\Element;

class HTMLLabel extends HTMLElement
{
	/**
	 * @param String|\CPath\Render\HTML\Attribute\IAttributes $_content
	 */
    public function __construct($_content=null) {
        parent::__construct('label');

	    for($i=0;$i<func_num_args();$i++)
		    $this->addAll(func_get_arg($i));
    }

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
//		if ($value instanceof IHTMLElement) {
//			switch (strtolower($value->getElementType())) {
//				case 'input':
//					if ($name = $value->getAttribute('name'))
//						$this->setAttribute('for', $name);
//			}
//		}

		parent::offsetSet($offset, $value);
	}

//	protected function renderContentItem(IRequest $Request, $index, IRenderHTML $Content, IAttributes $ContentAttr = null) {
////		if ($Content instanceof IDescribable)
////			echo  RI::ni(), '<span>' . $Content->getDescription() . '</span>';
//
//		if ($Content instanceof IValidation) {
//			try {
//				$Content->validate($Request);
//			} catch (\Exception $ex) {
//				echo  RI::ni(), '<div class="error">' . $ex->getMessage() . '</div>';
//			}
//		}
//
//		parent::renderContentItem($Request, $index, $Content, $ContentAttr);
//	}

//	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
//		if($this->mText) {
//			if(!$this->hasFlag(self::FLAG_SKIP_NEWLINE))
//				echo RI::ni();
//			echo  '<span>' . $this->mText . '</span>';
//		}
//
//		parent::renderContent($Request, $ContentAttr, $Parent);
//	}

}



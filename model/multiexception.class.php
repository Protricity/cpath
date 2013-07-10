<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;

use CPath\Interfaces\IResponse;

class MultiException extends ResponseException implements \Countable{
    private $mEx = array();
    public function add($ex) {
        if($ex instanceof \Exception)
            $ex = $ex->getMessage();
        $this->message = ($this->message ? $this->message."\n" : "") . $ex;
        $this->mEx[] = $ex;
    }

    public function count()
    {
        return count($this->mEx);
    }

    function &getData()
    {
        $data = parent::getData();
        $data['errors'] = $this->mEx;
        return $data;
    }
}
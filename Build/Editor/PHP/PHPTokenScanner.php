<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/23/14
 * Time: 10:26 PM
 */
namespace CPath\Build\Editor\PHP;

use CPath\Build\Editor;

class PHPTokenScanner implements IPHPWritableSource
{
    const T_SCANNER_INSTANCE = 4321;
    const T_SCANNER_STRING = 4322;
    private $mTokens;
    private $mPos = 0;
    private $mSource;

    public function __construct(Array $tokens, IPHPWritableSource $Source) {
        $this->mTokens = $tokens;
        $this->mSource = $Source;
    }

    public function write() {
        $this->mSource->write();
    }

    public function getPos() {
        return $this->mPos;
    }

    public function next() {
        if (isset($this->mTokens[$this->mPos]))
            return $this->mTokens[$this->mPos++];
        return null;
    }

    public function scan($scanTokens) {
        if(!is_array($scanTokens))
            $scanTokens = func_get_args();
        $pos = $this->mPos;
        while ($next = $this->next()) {
            $token = is_array($next) ? $next[0] : $next;
            if (in_array($token, $scanTokens))
                return $next;
        }
        $this->mPos = $pos;
        return null;
    }

    /**
     * Create a source chunk and return the inst
     * @param $start
     * @param $finish
     * @return array
     */
    public function getTokens($start = 0, $finish = null) {
        $chunkTokens = array();
        for($i=$start; $i<$finish; $i++) {
            $chunkTokens[] = $this->mTokens[$i];
        }
        return $chunkTokens;
    }

    public function replaceTokens(Array $newTokens, $start=0, $finish=null) {
        $tokens = array();

        for($i=0;$i<$start; $i++)
            $tokens[] = $this->mTokens[$i];

        foreach($newTokens as $token)
            $tokens[] = $token;

        if($finish) {
            $c = sizeof($this->mTokens);
            for($i=$finish; $i<$c; $i++)
                $tokens[] = $this->mTokens[$i];
        }

        $this->mTokens = $tokens;
    }

    /**
     * Create a source chunk and return the inst
     * @param $start
     * @param $finish
     * @return PHPTokenScanner
     */
    public function createChunk($start, $finish) {
        $chunkTokens = $this->getTokens($start, $finish);
        $Chunk = new PHPTokenScanner($chunkTokens, $this);
        $newTokens = array();
        $newTokens[] = array(self::T_SCANNER_INSTANCE, $Chunk);
        $this->replaceTokens($newTokens, $start, $finish);
        $this->mPos = $start + sizeof($newTokens);
        return $Chunk;
    }

    public function reset() {
        $this->mPos = 0;
    }

    /**
     * Generate a string based on tokens
     * @return String
     */
    function getSourceString() {
        $src = '';
        foreach ($this->mTokens as $token)
            if ($token instanceof Editor\PHP\PHPTokenScanner)
                $src .= $token->getSourceString();
            else
                $src .= is_array($token) ? $token[1] : $token;
        return $src;
    }

    function __toString() {
        return $this->getSourceString();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 2:18 PM
 */
namespace CPath\Request\Log;

use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Map\SequenceMapCallback;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\RenderCallback;
use CPath\Request\IRequest;

final class StaticLogger implements ILogListener, ISequenceMap, IRenderHTML, IHTMLSupportHeaders
{
    const CSS_CLASS = 'static-logger';

    private static $Log = array();
    /** @var ILogListener[] */
    private static $Listeners = array();

    static function start(IRequest $Request) {
        static $started = false;
        if($started)
            return;
        $Request->addLogListener(new StaticLogger);
        $started = true;
    }

    static function hasLog() {
        return sizeof(self::$Log) > 0;
    }

    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        foreach (self::$Listeners as $Listener)
            $Listener->log($msg, $flags);

        self::$Log[] = array($msg, $flags);
    }

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
        foreach (self::$Listeners as $Listener)
            $Listener->logEx($ex, $flags);

        self::$Log[] = array($ex, $flags);
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @param bool $catchUp
     * @return void
     */
    function addLogListener(ILogListener $Listener, $catchUp = false) {
        self::$Listeners[] = $Listener;

        if ($catchUp && self::$Log) {
            foreach (self::$Log as $log) {
                if ($log[0] instanceof \Exception)
                    $Listener->logEx($log[0], $log[1]);
                else
                    $Listener->log($log[0], $log[1]);
            }
        }
    }

    /**
     * Map sequential data to the map
     * @param ISequenceMapper $Map
     * @internal param \CPath\Request\IRequest $Request
     * @return mixed
     */
    function mapSequence(ISequenceMapper $Map) {
        foreach(self::$Log as $log)
            $Map->mapNext($log[0], $log[1]);
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '/assets/static-logger.js');
        $Head->writeStyleSheet(__DIR__ . '/assets/static-logger.css');
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Div = new HTMLElement('div', $Attr);
        $Div->addClass(self::CSS_CLASS);

        $Render = new RenderCallback(function(IRequest $Request, IAttributes $Attr=null) {

            $Log = new StaticLogger();
            $Log->mapSequence(new SequenceMapCallback(function($msg, $flags) use ($Request) {

                if($msg instanceof \Exception) {
                    $msg = $msg->getMessage();
                }
                $Div = new HTMLElement('div', null, $msg);
                if($flags & ILogListener::VERBOSE)
                    $Div->addClass('verbose');
                if($flags & ILogListener::ERROR)
                    $Div->addClass('error');
                $Div->renderHTML($Request);
            }));
        });

        $Div->addContent($Render);
        $Div->renderHTML($Request);
    }
}


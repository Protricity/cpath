<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;

use CPath\Base;
use CPath\Cache\Cache;
use CPath\Config;

class CrashLog {

    const EMAIL_DELAY = 3600;

    const CACHE_TOKEN = ':crash';

    const STATE_NONE = 0;
    const STATE_STARTED = 1;
    const STATE_COMPLETE = 2;
    const STATE_ERROR = 3;

    const PARAM_STATE = 0;
    const PARAM_REQUEST = 1;
    const PARAM_ERROR = 2;
    const PARAM_EXCEPTION = 3;
    const PARAM_LAST_EMAIL = 4;
    const PARAM_URL = 5;

    private $mEmail;

    private function __construct($email, $catchExceptions=false) {

        $this->mEmail = $email;
        $config = $this->getConfig();
        if($config[self::PARAM_STATE] == self::STATE_STARTED)
            $this->reportException(new \Exception("Last instance did not shut down"), $config);

        $config[self::PARAM_STATE] = self::STATE_STARTED;
        $config[self::PARAM_REQUEST] = json_encode(Base::getRequest()->getDataPath());
        if(isset($_SERVER['REQUEST_URI']))
            $config[self::PARAM_URL] = $_SERVER['REQUEST_URI'];

        Cache::get()->store(__CLASS__ . self::CACHE_TOKEN, $config);

        register_shutdown_function(__CLASS__ . '::onShutdown');
        if($catchExceptions)
            set_exception_handler(__CLASS__ . '::onException');
        set_error_handler(__CLASS__ . '::onError');
    }

    private function processShutdown() {
        $config = $this->getConfig();

        $error = error_get_last();
        if (in_array($error['type'], array(E_PARSE, E_ERROR)) && $config[self::PARAM_STATE] != self::STATE_ERROR) {
            $config[self::PARAM_STATE] = self::STATE_ERROR;
            $config[self::PARAM_ERROR] = $error;
            $this->reportException(new \Exception("Shutdown caught an error: " . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']), $config);
        } else {
            $config[self::PARAM_STATE] = self::STATE_COMPLETE;
        }
        $this->storeConfig($config);
    }

    private function processException(\Exception $ex) {
        $config = $this->getConfig();

        $this->reportException($ex, $config);

        $config[self::PARAM_STATE] = self::STATE_ERROR;
        $config[self::PARAM_EXCEPTION] = $ex->getMessage();

        $this->storeConfig($config);
        echo $ex;
    }

    private function processError($num, $str, $file, $line, $context = null) {
        $config = $this->getConfig();

        $this->reportException(new \Exception($str . " ($num)\n" . $file . ':' . $line), $config);

        $config[self::PARAM_STATE] = self::STATE_ERROR;
        $config[self::PARAM_ERROR] = array_slice(func_get_args(), 0, 4);
        $this->storeConfig($config);
    }

    /**
     * Report an exception
     * @param \Exception $ex
     * @param array $config
     * @return bool true if an email was sent, otherwise false
     */
    private function reportException(\Exception $ex, Array &$config) {
        $path = $this->getConfigPath();
        $text = "\n" . date('[d.m.Y h:i:s]') . " - " . $config[self::PARAM_URL]
            . "\n" . $config[self::PARAM_URL]
            . "\n" . $config[self::PARAM_REQUEST]
            . "\n" . $ex . "\n";
        file_put_contents($path, $text, FILE_APPEND);

        $last = $config[self::PARAM_LAST_EMAIL];
        if(!$last || ($last < time() - self::EMAIL_DELAY)) {
            $headers = 'From: ateam@newaer.com' . "\r\n" .
                'Reply-To: ateam@newaer.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            @mail($this->mEmail, $ex->getMessage(), $text, $headers);
            $config[self::PARAM_LAST_EMAIL] = time();
            return true;
        }
        return false;
    }

    /**
     * Return the .profile file full path
     * @return string build config full path
     */
    private function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Config::getGenPath().'crash.log';
    }

    private function getConfig() {
        $config = Cache::get()->fetch(__CLASS__ . self::CACHE_TOKEN) ?: array();
        return $config + array(NULL, NULL, NULL, NULL, NULL, NULL);
    }

    private function storeConfig($config) {
        foreach($config as $k=>$v)
            if($v === null)
                unset($config[$k]);
        Cache::get()->store(__CLASS__ . self::CACHE_TOKEN, $config);
    }


    // Static

    /** @var CrashLog  */
    private static $mInst;

    static function onError($num, $str, $file, $line, $context = null) {
        self::$mInst->processError($num, $str, $file, $line, $context);
        return false;
    }

    static function onException($Exception) {
        self::$mInst->processException($Exception);
        return false;
    }

    static function onShutdown() {
        self::$mInst->processShutdown();
    }

    static function register($email, $catchExceptions=false) {
        self::$mInst = new self($email, $catchExceptions);
    }
}
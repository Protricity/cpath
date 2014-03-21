<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Scripts;
use CPath\Base;
use CPath\Describable\IDescribable;
use CPath\Exceptions\BuildException;
use CPath\Framework\Api\Exceptions\APIException;
use CPath\Framework\Api\Field\Collection\FieldCollection;
use CPath\Framework\Api\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\Api\Field\Field;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Util\APIRenderUtil;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\Route\Render\IDestination;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\SimpleResponse;
use CPath\Framework\Route\Builders\RouteBuilder;
use CPath\Log;

class Install implements IDestination, IBuildable, IAPI {

    const FIELD_NO_PROMPT = 'y';

    private $mNoPrompt = false;


    /**
     * Get all API Fields
     * @return IField[]|IFieldCollection
     */
    function getFields() {
        return new FieldCollection(array(
            new Field(self::FIELD_NO_PROMPT, "Use default values and skip prompts"),
        ));
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() { return "Installation script for CPath"; }


    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function renderDestination(IRequest $Request) {
        $Util = new APIRenderUtil($this);
        $Util->renderDestination($Request);
    }


    public function isNoPrompt() { return $this->mNoPrompt; }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     * @throws APIException if no config file could be installed
     */
    function execute(IRequest $Request, $args)
    {
        Log::u(__CLASS__, "Installing Config File");

        if($Request[self::FIELD_NO_PROMPT]) {
            $this->mNoPrompt = true;
            Log::u(__CLASS__, "Installing with defaults...");
        }

        $path = Base::getBasePath();
        $targetPath = $path . 'Config.php';

        if(file_exists($targetPath))
            return new SimpleResponse("Config file already exists: " . $targetPath);

        if(file_exists($p = $path . 'Config.default.php')) {
            if(!copy($p, $targetPath))
                throw new APIException("Could not copy ($p) to ($targetPath)");
            return new SimpleResponse("Copied config from: " . $p);
        }
        Log::u(__CLASS__, "Default config file not found: " . $p);

        if(file_exists($p = __DIR__ . '/assets/config.default.php')) {
            if(!copy($p, $targetPath))
                throw new APIException("Could not copy ($p) to ($targetPath)");
            return new SimpleResponse("Copied config from: " . $p);
        }
        Log::u(__CLASS__, "Default config file not found: " . $p);

        throw new APIException("Could not find a default config to install");
    }

    /**
     * Build this class
     * @throws BuildException if an exception occurred
     */
    static function buildClass() {
        RouteBuilder::buildRoute('CLI /install', new Install());
    }
}

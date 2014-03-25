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
use CPath\Framework\API\Exceptions\APIException;
use CPath\Framework\API\Field\Collection\FieldCollection;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\SimpleResponse;
use CPath\Framework\Route\Builders\RouteBuilder;
use CPath\Handlers\Views\APIView;
use CPath\Log;

class Install implements IRenderAggregate, IBuildable, IAPI {

    const FIELD_NO_PROMPT = 'y';

    private $mNoPrompt = false;


    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
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
     * Render this route destination
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function getRenderer(IRequest $Request) {
        $Util = new APIView($this);
        return $Util->getRenderer($Request);
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

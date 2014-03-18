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
use CPath\Framework\Api\Exceptions\APIException;
use CPath\Framework\Api\Field\Field;
use CPath\Framework\Api\Types\AbstractAPI;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\SimpleResponse;
use CPath\Log;

class Install extends AbstractAPI {

    const ROUTE_PATH = '/install';  // Allow manual install from command line: 'php index.php install'
    const ROUTE_METHOD = 'CLI';    // CLI only
    const ROUTE_API_VIEW_TOKEN = false;   // Add an APIView route entry for this API

    private $mNoPrompt = false;

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupAPI() {
        $this->addField(new Field('no-prompt', "Use default values and skip prompts"), 'y');
    }

    public function isNoPrompt() { return $this->mNoPrompt; }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     * @throws APIException if no config file could be installed
     */
    function execute(IRequest $Request)
    {
        Log::u(__CLASS__, "Installing Config File");

        if($Request['no-prompt']) {
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
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() { return "Installation script for CPath"; }

    /**
     * Return an instance of the class for building and other tasks
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {}
}

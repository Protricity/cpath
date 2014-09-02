<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build\API;

use CPath\Base;
use CPath\Compile;
use CPath\Config;
use CPath\Describable\IDescribable;
use CPath\Exceptions\BuildException;
use CPath\Framework\API\Field\Collection\FieldCollection;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\Build\IBuilder;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\Route\Builders\RouteBuilder;
use CPath\Framework\Render\Layout\Common\API\APIView;
use CPath\Log;

class Build implements IRenderAggregate, IAPI, IBuildable {

    //const ROUTE_PATH = '/build';    // Allow manual building from command line: 'php index.php build'
    //const ROUTE_METHOD = 'CLI';    // CLI only
    //const ROUTE_API_VIEW_TOKEN = false;   // Add an APIView route entry for this API

    const ROUTE_IGNORE_FILES = '.buildignore';
    const ROUTE_IGNORE_DIR = '.git|.idea';

    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        return new FieldCollection(array(
            new Field('v', "Display verbose messages"),
            new Field('s', "Skip broken files"),
            new Field('f', "Filter built files by wildcard *?"),
        ));
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the request instance for this render which contains the request and args
     * @internal param Array $args additional arguments for this execution
     * @return DataResponse the api call response with data, message, and status
     */
    final function execute(IRequest $Request) {
        static $built = false;
        if($built)
            return new DataResponse(false, "Build can only occur once per execution. Skipping Build...");
        $built = true;

        if(!empty($Request['v']))
            Log::setDefaultLevel(4);
        if(!empty($Request['s']))
            $this->mSkipBroken = true;
        if(!empty($Request['f']))
            $this->mFilter = $Request['f'];

        $Response = new DataResponse(false, "Starting Build");
        $exCount = Build::buildAll(true);
        if($exCount)
            return $Response
                ->update(false, "Build Failed: {$exCount} Exception(s) Occurred");
        return $Response
            ->update(true, "Build Complete");
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Build All classes";
    }

    /** @var IBuilder[] $mBuilders */
    private static $mBuilders = array();


    private $mClassFiles = array();

    /** @var \ReflectionClass[] */
    private $mClasses = array();
    private $mBuildConfig = NULL;
    private $mForce = false;
    private $mSkipBroken = false;
    private $mFilter = false;
    private $mBrokenFiles = array();

    /**
     * Return the build config full path
     * @return string build config full path
     */
    private function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Config::getGenPath().'build.gen.php';
    }

    /**
     * Load build config data for a class
     * @param null $class optional class to load data for
     * @return mixed build config data
     */
    private function &loadConfig($class=NULL) {
        if($this->mBuildConfig === NULL) {
            $path = $this->getConfigPath();
            $config = array();
            if(file_exists($path))
                include ($path);
            $this->mBuildConfig = $config;
        }
        if(!$class) return $this->mBuildConfig;
        if(is_object($class)) $class = get_class($class);
        if(!isset($this->mBuildConfig[$class])) $this->mBuildConfig[$class] = array();
        return $this->mBuildConfig[$class];
    }

    /**
     * Load build config data for a class
     * @param $class string class to load data for
     * @param null $key if provided, only return data for this key
     * @return mixed build config data
     */
    public function &getConfig($class, $key=NULL) {
        $Config =& $this->loadConfig($class);
        if($key) return $Config[$key];
        return $Config;
    }

    /**
     * Set build config data for a class
     * @param string $class class to load data for
     * @param string $key data key
     * @param string $val data value
     * @param boolean $commit data value
     */
    public function setConfig($class, $key, $val, $commit=false) {
        $Config =& $this->loadConfig($class);
        $Config[$key] = $val;
        if($commit)
            $this->commitConfig();
    }

    /**
     * Commit the build config to the file
     */
    public function commitConfig() {
        $config =& $this->loadConfig();
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = $this->getConfigPath();
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $php);
    }

    /**
     * Returns true if the current build should be forced
     * @return bool whether or not to force build
     */
    public function force() { return $this->mForce; }

    public function buildDomainPath() {
        return 'http://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname()).'/';
    }

    /**
     * Build all classes
     */
    protected function buildAll($force=false) {
        Log::v(__CLASS__, "Starting Builds");
        if(!Config::$BuildEnabled) {
            Log::e(__CLASS__, "Building is not allowed. Config::\$BuildEnabled==false");
            return 0;
        }

        $this->mBrokenFiles = $this->getConfig(__CLASS__, 'brokenFiles') ?: array();
        if($this->mSkipBroken) {
            if($lastFile = $this->getConfig(__CLASS__, 'lastFile')) {
                if(!in_array($lastFile, $this->mBrokenFiles)) {
                    $this->mBrokenFiles[] = $lastFile;
                    $this->setConfig(__CLASS__, 'brokenFiles', $this->mBrokenFiles);
                    Log::v(__CLASS__, "Adding broken file to skip list: {$lastFile}");
                }
            }
            if($this->mBrokenFiles) {
                Log::v(__CLASS__, "Skipping (%s) broken files(es)", sizeof($this->mBrokenFiles));
                foreach($this->mBrokenFiles as $classFile)
                    Log::v2(__CLASS__, "Skipping broken file: {$classFile}");
            }
        } else {
            if($this->mBrokenFiles) {
                $this->mBrokenFiles = array();
                $this->setConfig(__CLASS__, 'brokenFiles', $this->mBrokenFiles);
                Log::v(__CLASS__, "Clearing (%d) broken files(es)", sizeof($this->mBrokenFiles));
            }
        }
        $this->commitConfig();

        $this->mForce = $force;
        $exCount = $this->findClassFiles(Base::getBasePath(), '');
        $this->findClasses();
        $exCount += $this->buildClasses();
        /** @var $Class \ReflectionClass */
        $this->setConfig(__CLASS__, 'lastFile', NULL, true);
        /** @var $Builder \CPath\Framework\Build\IBuilder */
        foreach(self::$mBuilders as $Builder)
            $Builder->buildComplete();
        self::$mBuilders = array();

        $v = ++ Compile::$BuildInc;
        Compile::commit();
//        $v = Base::getConfig('build.inc', 0);
//        Base::commitConfig('build.inc', ++$v);

        Log::v(__CLASS__, "All Builds Complete (inc={$v})");
        return $exCount;
    }

    private function findClasses() {
        $classes = array_merge(get_declared_classes(), get_declared_interfaces());
        foreach($classes as $className) {
            $Class = new \ReflectionClass($className);
            $classFile = $Class->getFileName();
            if(!isset($this->mClassFiles[$classFile])){
                Log::v2(__CLASS__, "Skipping non-framework class '{$Class->getName()}' at {$classFile}");
                continue;
            }
            if($this->mBrokenFiles && in_array($classFile, $this->mBrokenFiles)) {
                Log::v2(__CLASS__, "Skipping broken class '{$Class->getName()}'");
                continue;
            }

            $name = strtr($className, '\\', '/');
            $classFile2 = realpath(Base::getBasePath() . $name . '.php');
            if($classFile !== $classFile2) {
                Log::v2(__CLASS__, "Skipping secondary class '{$Class->getName()}' in $classFile");
                continue;
            }


            /* RENAME

            $newClassFile = dirname($classFile) . '/' . basename($className) . '.php';
            Log::v(__CLASS__, "Coppied class file to " . $newClassFile);
            copy($classFile, $newClassFile);

            $newFolder = dirname($newClassFile);
            $folderName = basename($newFolder);
            $baseClassName = basename(dirname($className));
            if($folderName !== $baseClassName) {
                $newFolder2 = dirname($newFolder) . '/' . $baseClassName;
                rename($newFolder, $newFolder2);
                Log::v(__CLASS__, "Renamed $newFolder to $newFolder2");
            }
*/

            if($Class->getConstant('BUILD_IGNORE')) {
            }
//            if($Class->isAbstract()) {
//                Log::v2(__CLASS__, "Ignoring Abstract Class '{$className}' in '{$classFile}'");
//                continue;
//            }

            $ns = dirname(__NAMESPACE__);
            if($Class->implementsInterface($ns . "\\IBuildable")) {

                if($Class->getConstant('BUILD_IGNORE')===true) {
                    Log::v2(__CLASS__, "Ignoring Class '{$className}' marked with BUILD_IGNORE===true");
                    continue;
                } else {
//                    if($Class->implementsInterface($ns . "\\IBuilder")) {
//                        Log::v(__CLASS__, "Found Builder Class: {$className}");
//                        //call_user_func(array($Class->getName(), 'build'), $Class);
//                        static::$mBuilders[] = $Class->getMethod('createBuildableInstance')->invoke(null);
//                    }
                    Log::v2(__CLASS__, "Found Class '{$Class->getName()}'");
                    $this->mClasses[] = $Class;
                    continue;
                }
            }

            Log::v2(__CLASS__, "Ignoring Class '{$className}' in '{$classFile}'");
            continue;
        }
    }

    private function buildClasses() {
        $exCount = 0;
        foreach($this->mClasses as $Class) {
            $BuildMethod = $Class->getMethod('buildClass');
            if($BuildMethod->isAbstract()) {
                Log::v2(__CLASS__, "Ignoring abstract method found in '{$Class->getName()}'");
                continue;
            }

            Log::v2(__CLASS__, "Building '{$Class->getName()}'");
            try {
                $BuildMethod->invoke(null);
            } catch (\Exception $ex) {
                $exCount++;
                Log::ex($Class->getName(), $ex);
            }

//            if(!$Buildable) {
//                Log::v2(__CLASS__, "No Buildable instance returned for '{$Class->getName()}'");
//                $this->mClasses[] = $Class;
//                continue;
//            }
//
//            if(!$Buildable instanceof IBuildable){
//                Log::e(__CLASS__, "Buildable instance returned does not implement IBuildable for '{$Class->getName()}'");
//                $exCount++;
//                continue;
//            }

            //foreach($this->mBuilders as $Builder) try {
            //    $Builder->buildClass($Buildable);
        }
        return $exCount;
    }
    /**
     * Find all classes by folder
     * @param $path string the class path
     * @param $dirClass string the class namespace
     * @throws \Exception if a build fails
     * @return int The number of exceptions that occurred
     */
    private function findClassFiles($path, $dirClass) {
        $exCount = 0;
        Log::v2(__CLASS__, "Scanning '{$path}'");
        $pathName = basename($path);

        foreach(explode('|', static::ROUTE_IGNORE_DIR) as $dir)
            if($dir == $pathName) {
                Log::v2(__CLASS__, "Found '{$dir}'. Ignoring Directory '{$path}'");
                return $exCount;
            }

        foreach(explode('|', static::ROUTE_IGNORE_FILES) as $file)
            if(file_exists($path . '/' . $file)) {
                Log::v2(__CLASS__, "Found File '{$file}'. Ignoring Directory '{$path}'");
                return $exCount;
            }

        foreach(scandir($path) as $file) {
            if(in_array($file, array('.', '..')))
                continue;
            $filePath = realpath($path . '/' . $file);
            if(is_dir($filePath)) {
                $exCount += $this->findClassFiles($filePath, $dirClass .'\\'. ucfirst($file));
                continue;
            }
            if(substr($file, -4) !== '.php'){
                Log::v2(__CLASS__, "Skipping non-php file: {$filePath}");
                continue;
            }

            //Log::v(__CLASS__, "Coppied class file to " . $filePath . '.old');
            //copy($filePath, $filePath . '.old');

            //$name = substr($file, 0, strlen($file) - 10);
            //$class = $dirClass . '\\' . ucfirst($name);

            if($this->mFilter
                && !fnmatch($this->mFilter, $filePath, FNM_NOESCAPE)) {
                //&& strpos($filePath, __DIR__) !== 0) {
                Log::v2(__CLASS__, "Skipping filtered file (" . $this->mFilter . "): {$filePath}");
                continue;
            }

            if($this->mBrokenFiles && in_array($filePath, $this->mBrokenFiles)) {
                Log::v(__CLASS__, "Skipping broken file: {$filePath}");
                continue;
            }

            $this->setConfig(__CLASS__, 'lastFile', $filePath, true);

            // Content

            $isClass = false;
            $handle = fopen($filePath, 'r');
            while (($buffer = fgets($handle)) !== false) {
                if (preg_match('/(^|\s+)(class|interface)\s+/i', $buffer)) {
                    $isClass = true;
                    break;
                }
            }
            fclose($handle);

            if(!$isClass) {
                Log::v(__CLASS__, "Skipping non-class php file: {$filePath}");
                continue;
            }

            try{
                require_once($filePath);
            } catch (\Exception $ex) {
                //$this->setConfig(__CLASS__, 'lastFile', NULL, true);
                $exCount++;
                Log::ex("Exception occurred while loading {$filePath}", $ex);
                continue;
            }
            $this->setConfig(__CLASS__, 'lastFile', NULL);
            $this->mClassFiles[$filePath] = true;
            Log::v2(__CLASS__, "Found Class file: {$filePath}");
//            $Class = new \ReflectionClass($class);
//            if($Class === NULL) // TODO: can this be null?
//                throw new \Exception("Class '{$class}' not found in '{$filePath}'");
//
//            if($Class->getConstant('BUILD_IGNORE')) {
//                Log::v2(__CLASS__, "Ignoring Class '{$class}' in '{$filePath}'");
//                continue;
//            }
//            if($Class->isAbstract()) {
//                Log::v2(__CLASS__, "Ignoring Abstract Class '{$class}' in '{$filePath}'");
//                continue;
//            }
//
//            if($Class->implementsInterface(__NAMESPACE__."\\Interfaces\\IBuilder")) {
//                Log::v(__CLASS__, "Found Builder Class: {$class}");
//                //call_user_func(array($Class->getName(), 'build'), $Class);
//                static::$mBuilders[] = $Class->newInstance();
//            }
        }
        return $exCount;
    }


    // Statics

    /**
     * Build this class
     * @throws BuildException if an exception occurred
     */
    static function buildClass() {
        RouteBuilder::buildRoute('ANY /build', new Build());
    }

    static function get() {
        static $Inst = null;
        return $Inst ?: $Inst = new Build();
    }

    static function registerBuilder(IBuilder $Builder) {
        foreach(self::$mBuilders as $Builder2)
            if($Builder2 === $Builder)
                return;
        self::$mBuilders[] = $Builder;
    }

    /**
     * Render this route destination
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function getRenderer(IRequest $Request) {
        $RenderUtil = new APIView($this);
        return $RenderUtil->getRenderer($Request);
    }

}
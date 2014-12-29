<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/23/14
 * Time: 10:25 PM
 */
namespace CPath\Build\Editor\PHP;

use CPath\UnitTest\ITestable;
use CPath\UnitTest\IUnitTestRequest;

class PHPMethodEditor implements ITestable
{

    private $mSource;
    private $mClassName;
    private $mMethodName;
    private $mModifiers;

    public function __construct(PHPTokenScanner $MethodSource, $className, $methodName, $modifiers=array())
    {
        $this->mClassName = $className;
        $this->mSource = $MethodSource;
        $this->mMethodName = $methodName;
        $this->mModifiers = $modifiers;
    }

    public function getClassName()
    {
        return $this->mClassName;
    }

    public function getMethodName()
    {
        return $this->mMethodName;
    }

    public function replaceMethodSource($newBody) {
        $tokens = array();
        $tokens[] = array(PHPTokenScanner::T_SCANNER_STRING, $newBody);
        $this->mSource->replaceTokens($tokens);
    }

    public function getMethodSource() {
        return $this->mSource->getSourceString();
    }

    public function write() {
        $this->mSource->write();
    }
    // Static

    static function fromMethod(\ReflectionMethod $Method) {
        return self::fromMethodName($Method->getDeclaringClass()->getName(), $Method->getName());
    }

    /**
     * @param $className
     * @param $methodName
     * @return \CPath\Build\Editor\PHP\PHPMethodEditor
     */
    static function fromMethodName($className, $methodName) {
        $Class = new \ReflectionClass($className);

        $FileEditor = new PHPFileEditor($Class->getFileName());
        $ClassEditor = $FileEditor->getClassEditor($className);
        $MethodEditor = $ClassEditor->getMethodEditor($methodName);

        return $MethodEditor;
    }

    /**
     * Perform a unit test
     * @param IUnitTestRequest $Test the unit test request inst for this test session
     * @return void
     * @test --disable 0
     * Note: Use doctag 'test' with '--disable 1' to have this ITestable class skipped during a build
     */
    static function handleStaticUnitTest(IUnitTestRequest $Test) {
        $SRC1 = "\n\t\techo 'im working #%s';";
        $SRC2 = "\n\t\techo 'im not working #%s';";
        $SRC3 = $SRC1.$SRC2."\n\t\techo 'im maybe working #%s';";

        $Editor = self::fromMethodName(__CLASS__, 'testReplaceSrc');
        for($i=0; $i<=rand(3,23); $i++) {
            $Editor->replaceMethodSource(sprintf($SRC3, $i, $i, $i) . "\n\t");
            $Editor->write();
        }
    }

    public function testReplaceSrc() {
		echo 'im working #11';
		echo 'im not working #11';
		echo 'im maybe working #11';
	}

}
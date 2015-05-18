<?php

namespace tests\PHPCTags\Acceptance;

final class KindsTest extends AcceptanceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $sourceCode =<<<'EOS'
<?php
namespace KindsExampleNamespace;
class KindsExampleClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;

    public function publicMethod()
    {
    }

    protected function protectedMethod()
    {
    }

    private function privateMethod()
    {
    }
}

function kindsExampleFunction()
{
}

define('CONSTANT_1', 1);
const CONSTANT_2 = 2;

$var = 'test value';

interface KindsExampleInterface
{
}
EOS;

        if (version_compare('5.4.0', PHP_VERSION, '<=')) {
            $sourceCode .= <<<EOS
trait KindsExampleTrait
{
}
EOS;
        }

        $this->givenSourceFile('KindsExample.php', $sourceCode);
    }

    /**
     * @test
     */
    public function itSupportsClassKindParameter()
    {
        $this->runPHPCtagsWithKinds('c');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);

        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'KindsExampleClass',
            self::KIND_CLASS,
            3,
            'namespace:KindsExampleNamespace'
        );
    }

    /**
     * @test
     */
    public function itSupportsPropertyKindParameter()
    {
        $this->runPHPCtagsWithKinds('p');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(3);

        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'publicProperty',
            self::KIND_PROPERTY,
            5,
            'class:KindsExampleNamespace\KindsExampleClass',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'protectedProperty',
            self::KIND_PROPERTY,
            6,
            'class:KindsExampleNamespace\KindsExampleClass',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'privateProperty',
            self::KIND_PROPERTY,
            7,
            'class:KindsExampleNamespace\KindsExampleClass',
            'private'
        );
    }

    /**
     * @test
     */
    public function itSupportsMethodKindParameter()
    {
        $this->runPHPCtagsWithKinds('m');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(3);

        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'publicMethod',
            self::KIND_METHOD,
            9,
            'class:KindsExampleNamespace\KindsExampleClass',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'protectedMethod',
            self::KIND_METHOD,
            13,
            'class:KindsExampleNamespace\KindsExampleClass',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'privateMethod',
            self::KIND_METHOD,
            17,
            'class:KindsExampleNamespace\KindsExampleClass',
            'private'
        );
    }

    /**
     * @test
     */
    public function itSupportsFunctionKindParameter()
    {
        $this->runPHPCtagsWithKinds('f');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);

        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'kindsExampleFunction',
            self::KIND_FUNCTION,
            22,
            'namespace:KindsExampleNamespace'
        );
    }

    /**
     * @test
     */
    public function itSupportsConstantKindsParameter()
    {
        $this->runPHPCtagsWithKinds('d');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'CONSTANT_1',
            self::KIND_CONSTANT,
            26,
            'namespace:KindsExampleNamespace'
        );
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'CONSTANT_2',
            self::KIND_CONSTANT,
            27,
            'namespace:KindsExampleNamespace'
        );
    }

    /**
     * @test
     */
    public function itSupportsVariableKindsParameter()
    {
        $this->runPHPCtagsWithKinds('v');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'var',
            self::KIND_VARIABLE,
            29,
            'namespace:KindsExampleNamespace'
        );
    }

    /**
     * @test
     */
    public function itSupportsNamespaceKindsParameter()
    {
        $this->runPHPCtagsWithKinds('n');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'KindsExampleNamespace',
            self::KIND_NAMESPACE,
            2
        );
    }

    /**
     * @test
     */
    public function itSupportsInterfaceKindsParameter()
    {
        $this->runPHPCtagsWithKinds('i');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'KindsExampleInterface',
            self::KIND_INTERFACE,
            31,
            'namespace:KindsExampleNamespace'
        );
    }

    /**
     * @test
     */
    public function itSupportsTraitKindsParameter()
    {
        if (version_compare('5.4.0', PHP_VERSION, '>')) {
            $this->markTestSkipped('Traits were not introduced until 5.4');
        }

        $this->runPHPCtagsWithKinds('t');

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'KindsExample.php',
            'KindsExampleTrait',
            self::KIND_TRAIT,
            33,
            'namespace:KindsExampleNamespace'
        );
    }
}

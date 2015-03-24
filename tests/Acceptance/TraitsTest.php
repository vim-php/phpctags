<?php

namespace tests\PHPCTags\Acceptance;

final class TraitsTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagForTopLevelTrait()
    {
        if (version_compare('5.4.0', PHP_VERSION, '>')) {
            $this->markTestSkipped('Traits were not introduced until 5.4');
        }

        $this->givenSourceFile('TopLevelTraitExample.php', <<<'EOS'
<?php

trait TestTrait
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
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(7);
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'TestTrait',
            self::KIND_TRAIT,
            3
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'publicProperty',
            self::KIND_PROPERTY,
            5,
            'trait:TestTrait',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'protectedProperty',
            self::KIND_PROPERTY,
            6,
            'trait:TestTrait',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'privateProperty',
            self::KIND_PROPERTY,
            7,
            'trait:TestTrait',
            'private'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'publicMethod',
            self::KIND_METHOD,
            9,
            'trait:TestTrait',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'protectedMethod',
            self::KIND_METHOD,
            13,
            'trait:TestTrait',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelTraitExample.php',
            'privateMethod',
            self::KIND_METHOD,
            17,
            'trait:TestTrait',
            'private'
        );
    }

    /**
     * @test
     */
    public function itAddsNamespacesToTraitTags()
    {
        if (version_compare('5.4.0', PHP_VERSION, '>')) {
            $this->markTestSkipped('Traits were not introduced until 5.4');
        }

        $this->givenSourceFile('MultiLevelNamespace.php', <<<'EOS'
<?php

namespace Level1\Level2;

trait TestTrait
{
    private $testProperty = 22;

    function setProperty($value)
    {
        $this->testProperty = $value;
    }
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->markTestIncomplete('Surely $this->varname shouldn\'t tag varname');
        $this->assertNumberOfTagsInTagsFileIs(4);
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'Level1\Level2',
            self::KIND_NAMESPACE,
            3
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'TestTrait',
            self::KIND_CLASS,
            5,
            'namespace:Level1\Level2'
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'testProperty',
            self::KIND_PROPERTY,
            7,
            'trait:Level1\Level2\TestTrait',
            'private'
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'setProperty',
            self::KIND_METHOD,
            9,
            'trait:Level1\Level2\TestTrait',
            'public'
        );
    }
}

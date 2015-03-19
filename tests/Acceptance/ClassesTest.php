<?php

namespace tests\PHPCTags\Acceptance;

final class ClassesTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagForTopLevelClass()
    {
        $this->givenSourceFile('TopLevelClassExample.php', <<<'EOS'
<?php

class TestClass
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
            'TopLevelClassExample.php',
            'TestClass',
            self::KIND_CLASS,
            3
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'publicProperty',
            self::KIND_PROPERTY,
            5,
            'class:TestClass',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'protectedProperty',
            self::KIND_PROPERTY,
            6,
            'class:TestClass',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'privateProperty',
            self::KIND_PROPERTY,
            7,
            'class:TestClass',
            'private'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'publicMethod',
            self::KIND_METHOD,
            9,
            'class:TestClass',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'protectedMethod',
            self::KIND_METHOD,
            13,
            'class:TestClass',
            'protected'
        );
        $this->assertTagsFileContainsTag(
            'TopLevelClassExample.php',
            'privateMethod',
            self::KIND_METHOD,
            17,
            'class:TestClass',
            'private'
        );
    }

    /**
     * @test
     */
    public function itAddsNamespacesToClassTags()
    {
        $this->givenSourceFile('MultiLevelNamespace.php', <<<'EOS'
<?php

namespace Level1\Level2;

class TestClass
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
            'TestClass',
            self::KIND_CLASS,
            5,
            'namespace:Level1\Level2'
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'testProperty',
            self::KIND_PROPERTY,
            7,
            'class:Level1\Level2\TestClass',
            'private'
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'setProperty',
            self::KIND_METHOD,
            9,
            'class:Level1\Level2\TestClass',
            'public'
        );
    }

    /**
     * @test
     * @group bugfix3
     */
    public function itTagsMagicMethods()
    {
        $this->givenSourceFile('DbConnectionUserDecorator.php', <<<'EOS'
<?php
class DbConnectionUserDecorator {
    public function __set($key, $value) {
        $this->conn->$key = $value;
    }
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'DbConnectionUserDecorator.php',
            'DbConnectionUserDecorator',
            self::KIND_CLASS,
            2
        );
        $this->assertTagsFileContainsTag(
            'DbConnectionUserDecorator.php',
            '__set',
            self::KIND_METHOD,
            3,
            'class:DbConnectionUserDecorator',
            'public'
        );
    }

    /**
     * @test
     * @group bugfix7
     */
    public function itTagsClassInsideConditional()
    {
        $this->givenSourceFile('MultiLevelNamespace.php', <<<'EOS'
<?php
if (!class_exists('MyClass')) {
    class MyClass {
    }
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'MyClass',
            self::KIND_CLASS,
            3
        );
    }
}

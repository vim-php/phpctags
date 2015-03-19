<?php

namespace tests\PHPCTags\Acceptance;

final class InterfacesTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagForTopLevelInterface()
    {
        $this->givenSourceFile('InterfaceExample.php', <<<'EOS'
<?php

interface TestInterface
{
    public $publicProperty;

    public function publicMethod();
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(3);
        $this->assertTagsFileContainsTag(
            'InterfaceExample.php',
            'TestInterface',
            self::KIND_INTERFACE,
            3
        );
        $this->assertTagsFileContainsTag(
            'InterfaceExample.php',
            'publicProperty',
            self::KIND_PROPERTY,
            5,
            'interface:TestInterface',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'InterfaceExample.php',
            'publicMethod',
            self::KIND_METHOD,
            7,
            'interface:TestInterface',
            'public'
        );
    }

    /**
     * @test
     */
    public function itAddsNamespacesToInterfaceTags()
    {
        $this->givenSourceFile('MultiLevelNamespace.php', <<<'EOS'
<?php

namespace Level1\Level2;

interface TestInterface
{
    public $testProperty;

    function setProperty($value);
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(4);
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'Level1\Level2',
            self::KIND_NAMESPACE,
            3
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'TestInterface',
            self::KIND_INTERFACE,
            5,
            'namespace:Level1\Level2'
        );
        $this->markTestIncomplete('Interface tag scopes are not fully qualified yet.');
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'testProperty',
            self::KIND_PROPERTY,
            7,
            'interface:Level1\Level2\TestInterface',
            'public'
        );
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'setProperty',
            self::KIND_METHOD,
            9,
            'interface:Level1\Level2\TestInterface',
            'public'
        );
    }
}

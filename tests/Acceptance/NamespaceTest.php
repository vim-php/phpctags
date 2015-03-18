<?php

namespace tests\PHPCTags\Acceptance;

final class NamespaceTest extends AcceptanceTestCase
{
    public function testItAddsTagForNamespacedVariable()
    {
        $this->givenSourceFile('NamespacedVariableExample.php', <<<'EOS'
<?php

namespace TestNamespace;

$var = 'test value';
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'NamespacedVariableExample.php',
            'TestNamespace',
            self::KIND_NAMESPACE,
            3
        );
        $this->assertTagsFileContainsTag(
            'NamespacedVariableExample.php',
            'var',
            self::KIND_VARIABLE,
            5,
            'namespace:TestNamespace'
        );
    }
}

How to write a test case
========================

We are using [PHPUnit][] for testing.

[PHPUnit]:https://github.com/sebastianbergmann/phpunit/

This directory contains test cases for phpctags. The structure is shown as
below.

```
tests/
    |--examples/
    |   |-- <example_identifier>.example.php
    |   |-- <example_identifier>.example.define.php
    |   |-- ...
    |--testcases/
    |   |-- <testcase_identifier>.testcase.php
    |   |-- ...
    |--bootstrap.php
    |--PHPCtagsTest.php
    |--PHPCtagsTestCase.php
````

`examples/` directory holds the example file for testing named as
`<example_identifier>.example.php` and its token definition file named
as `<example_identifier>.example.define.php`. The `<example_identifier>`
should be unique and only contians characters in the range of `[0-9a-zA-Z_]`,
especially should not contain a dot.

File `<example_identifiler>.example.define.php` contains only one function
named as `e_<example_identifier>_define` which returns an array contains
information about tokens in the corresponding example file. This array consists
of a set of arrays ordered by the occurrence order of the tokens in the example
file. Each of these arrays consits of the following keys.

* `name`

    Required, name of the token

* `line`

    Required, line number of the token

* `kind`

    Required, kind descriptor of the token in one character.
    For a full definition kind descriptor, please see `PHPCtags::$mKinds`.

* `scope`

    Optional, scope definition of the token

* `access`

    Optional, access restriction for the token

For example, assuming we have the following code in an example file named as
`foo.example.php`.

```
<?php
class Foo {
    public function bar() {
    }
}
```

Then, our `foo.example.define.php` should contain the following code which
reflect the token information about the example code above and will be used
to generate the expected result.

```
<?php
function e_foo_define()
{
    return array(
        array(
            'name'=>'Foo',
            'line'=>'2',
            'kind'=>'c',
        ),
        array(
            'name'=>'bar',
            'line'=>'3',
            'kind'=>'m',
            'scope'=>'class:Foo',
            'access'=>'public',
        ),
    );
}
```

After finishing adding the example files, it is the time to add a test case for
this example. All test cases are located at `testcases/` directory and named
as `<testcase_identifier>.<testcase_description>.testcase.php`. All test case
classes should extend from the basic abstract class `PHPCtagsTestCase` and be
named as `t_<testcase_identifier>`. Only `$mExample` property must be assigned
in the `__construt` function of the test case class. The other two properties
`$mFormat` and `$mOptions` have default value predifined in the `__construct`
function of the base `PHPCtagsTestCase` class which could be easily overwrited.

Here is the test case class for the previous example and should be stored in
file `test_foo.testcase.php`.

```
<?php
class t_test_foo extends PHPCtagsTestCase {

    public function __construct()
    {
        parent::__construct();
        $this->mExample = 'foo';
    }

}
```

There is no naming restriction for `<example_identifier>` and
`<testcase_identifier>`. Generally, the identifier for a bug fix reported in
the issue list should be named as `bugfix_<issue number>`.

We are done now. Now the `PHPCtagsTest` class will automatically find these
test cases and use them to feed the `testExport()` method by a means called
__[Parameterized Test][]__.

[Parameterized Test]:http://xunitpatterns.com/Parameterized%20Test.html

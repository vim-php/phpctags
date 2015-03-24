<?php
function e_bugfix_0004_define()
{
    return array(
        array(
            'name'=>'Bar',
            'kind'=>'n',
            'line'=> 2,
            'scope'=>'',
            'access'=>''
        ),
        array(
            'name'=>'Foo',
            'kind'=>'c',
            'line'=>'4',
            'scope'=>'namespace:Bar',
            'access'=>'',
        ),
    );
}

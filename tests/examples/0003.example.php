<?php
try {
    $ctags = new PHPCtags();
    $result = $ctags->export($file, $options);
} catch (Exception $e) {
    die("phpctags: {$e->getMessage()}");
}

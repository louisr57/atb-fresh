<?php

use Faker\Core\Barcode;
use Illuminate\Support\Collection;
use Psy\TabCompletion\Matcher\FunctionsMatcher;

$collection = new Collection();
$collection->mapToGroups($_ENV);

$foo = new Foo;
$foo->bar();
class Foo {
    public function bar($bar='Bob') {
        $collection = new Collection();
        return $bar;
    }
    public function baz($bar='Phil') {
        return $bar;
    }
}

<?php

use VeeWee\Reflecta\Iso\Iso;
use VeeWee\Xml\Dom\Document;
use function Psl\Type\string;
use function VeeWee\Reflecta\Lens\index;
use function VeeWee\Reflecta\Reflect\instantiate;
use function VeeWee\Reflecta\Lens\optional;
use function VeeWee\Reflecta\Lens\path;
use function VeeWee\Reflecta\Lens\properties;
use function VeeWee\Reflecta\Lens\property;
use function VeeWee\Xml\Dom\Locator\Node\value;

require_once __DIR__.'/vendor/autoload.php';

$arr = ['a' => 1, 'b' => 2, 'c' => 3];
var_dump(index('c')->set($arr, 4));exit;




$base64Iso = new Iso(
    base64_encode(...),
    base64_decode(...)
);
$itemNodeIso = new Iso(
    fn (string $data): string => '<item>'.$data.'</item>',
    fn (string $xml): string => Document::fromXmlString($xml)->locate(
        static fn (DOMDocument $document) => value($document->documentElement, string())
    )
);
$base64ItemNodeIso = $base64Iso->compose($itemNodeIso);


echo $to = $base64ItemNodeIso->to('hello').PHP_EOL;
// > <item>aGVsbG8=</item>
echo $base64ItemNodeIso->from($to).PHP_EOL;
// > hello



class Item {
    public string $value;
}

$itemValueLens = property('value');

$itemIso = new Iso(
    static fn (Item $item): string => '<item value="'.$item->value.'" />',
    static fn (string $xml): Item => $itemValueLens->set(
        instantiate(Item::class),
        Document::fromXmlString($xml)->locate(
            static fn (DOMDocument $doc) => $doc->documentElement->getAttribute('value')
        )
    )
);

$item = new Item();
$item->value = 'hello';
echo $xml = $itemIso->to($item).PHP_EOL;
// > <item value="hello" />
print_r($itemIso->from($xml));
// > Item Object
// > (
// >   [value] => hello
// > )


var_dump(properties()->get($item));
var_dump(properties()->set($item, ['value' => 'world']));
exit;


/*

$iso = new Iso(
    fn(array $s) => ['from' => $s['from']],
    fn(array $s) => ['to' => $s['to']]
);

var_dump(
    $iso->from(['to' => 1]),
    $iso->to(['from' => 1])
);

$lens = $iso->asLens();
var_dump(
    $lens->get(['from' => 1]),
    $lens->set(null, ['to' => 2])
);
//var_dump();
//exit;


*/



class Foo
{
}

class Bar
{
    /** @var Foo */
    public $foo;

    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }
}

class Baz
{
    /** @var Bar|null */
    public $bar;

    public function __construct(?Bar $bar)
    {
        $this->bar = $bar;
    }
}
/*
$barBaz = optional(property('bar'));
var_dump($barBaz->get(new Baz(null)));
var_dump($barBaz->get($set = new Baz(new Bar(new Foo()))));


var_dump($barBaz->set(
    $set,
    Psl\Option\none()
));
exit;




var_dump($set, $barBaz->get($set));
exit;
*/




/*$fooBar = property('foo');
$barBaz = property('bar');
$fooBaz = $barBaz->compose($fooBar);*/

$fooBaz = path(property('bar'), optional(property('foo')));


$foo = new Foo();
$bar = new Bar($foo);
$baz = new Baz(null);

var_dump($fooBaz->get($baz));
var_dump($fooBaz->set($baz, new Foo()));


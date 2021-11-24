<?php


	namespace MehrItEasyJsonTest\Cases\Parse;


	use MehrIt\EasyJson\Exception\JsonException;
	use MehrIt\EasyJson\Exception\JsonSyntaxException;
	use MehrIt\EasyJson\Parse\JsonParser;
	use MehrItEasyJsonTest\Cases\TestCase;
	use stdClass;

	class JsonParserTest extends TestCase
	{

		public function testValue_string() {

			$out = null;

			JsonParser::fromString("\"asd \\\"string\\\"\"")
				->value([], $out)
				->parse();

			$this->assertSame("asd \"string\"", $out);
		}

		public function testValue_string_rootLevel() {

			$out = null;

			JsonParser::fromString("\"this\\tis a\\n\\\"string\\\" \"")
				->value([], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inArray_first() {

			$out = null;

			JsonParser::fromString("[\"this\\tis a\\n\\\"string\\\" \", 15, 19]")
				->value(['0'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inArray_middle() {

			$out = null;

			JsonParser::fromString("[15, \"this\\tis a\\n\\\"string\\\" \", 19]")
				->value(['1'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inArray_last() {

			$out = null;

			JsonParser::fromString("[15, 19, \"this\\tis a\\n\\\"string\\\" \"]")
				->value(['2'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[15, 19, \"this\\tis a\\n\\\"string\\\" \"]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":\"this\\tis a\\n\\\"string\\\" \", \"b\":15, \"c\": 19}")
				->value(['a'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":\"this\\tis a\\n\\\"string\\\" \", \"c\": 19}")
				->value(['b'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":19, \"c\": \"this\\tis a\\n\\\"string\\\" \"}")
				->value(['c'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_string_inObject_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\": 15, \"b\":19, \"c\": \"this\\tis a\\n\\\"string\\\" \"}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame("this\tis a\n\"string\" ", $out);
		}

		public function testValue_integer_rootLevel() {

			$out = null;

			JsonParser::fromString("-5")
				->value([], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_rootLevel_numbersAsString() {

			$out = null;

			JsonParser::fromString("-5.30")
				->setNumbersAsString(true)
				->value([], $out)
				->parse();

			$this->assertSame('-5.3', $out);
		}

		public function testValue_integer_rootLevel_numbersAsString_reduceExponent() {

			$out = null;

			JsonParser::fromString("-5.30e2")
				->setNumbersAsString(true)
				->value([], $out)
				->parse();

			$this->assertSame('-530', $out);
		}

		public function testValue_integer_inArray_first() {

			$out = null;

			JsonParser::fromString("[-5, 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inArray_middle() {

			$out = null;

			JsonParser::fromString("[19, -5, 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inArray_last() {

			$out = null;

			JsonParser::fromString("[19, 32, -5]")
				->value(['2'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[19, 32, -5]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":-5, \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": -5, \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":-5}")
				->value(['c'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_integer_inObject_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":-5}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame(-5, $out);
		}

		public function testValue_true_rootLevel() {

			$out = null;

			JsonParser::fromString("true")
				->value([], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inArray_first() {

			$out = null;

			JsonParser::fromString("[true, 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inArray_middle() {

			$out = null;

			JsonParser::fromString("[19, true, 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inArray_last() {

			$out = null;

			JsonParser::fromString("[19, 32, true]")
				->value(['2'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[19, 32, true]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":true, \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": true, \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":true}")
				->value(['c'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_true_inObject_last_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":true}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame(true, $out);
		}

		public function testValue_false_rootLevel() {

			$out = null;

			JsonParser::fromString("false")
				->value([], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inArray_first() {

			$out = null;

			JsonParser::fromString("[false, 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inArray_middle() {

			$out = null;

			JsonParser::fromString("[19, false, 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inArray_last() {

			$out = null;

			JsonParser::fromString("[19, 32, false]")
				->value(['2'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[19, 32, false]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":false, \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": false, \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":false}")
				->value(['c'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_false_inObject_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":false}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame(false, $out);
		}

		public function testValue_null_rootLevel() {

			$out = 1;

			JsonParser::fromString("null")
				->value([], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inArray_first() {

			$out = 1;

			JsonParser::fromString("[null, 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inArray_middle() {

			$out = 1;

			JsonParser::fromString("[19, null, 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inArray_last() {

			$out = 1;

			JsonParser::fromString("[19, 32, null]")
				->value(['2'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inArray_nested() {

			$out = 1;

			JsonParser::fromString("[[19, 32, null]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inObject_first() {

			$out = 1;

			JsonParser::fromString("{\"a\":null, \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inObject_middle() {

			$out = 1;

			JsonParser::fromString("{\"a\":19, \"b\": null, \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inObject_last() {

			$out = 1;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":null}")
				->value(['c'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_null_inObject_nested() {

			$out = 1;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":null}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame(null, $out);
		}

		public function testValue_array_rootLevel() {

			$out = null;

			JsonParser::fromString("[1,2,3]")
				->value([], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_rootLevel_empty() {

			$out = null;

			JsonParser::fromString("[]")
				->value([], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inArray_first() {

			$out = null;

			JsonParser::fromString("[[1,2,3], 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inArray_first_empty() {

			$out = null;

			JsonParser::fromString("[[], 19, 32]")
				->value(['0'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inArray_middle() {

			$out = null;

			JsonParser::fromString("[19, [], 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inArray_middle_empty() {

			$out = null;

			JsonParser::fromString("[19, [1,2,3], 32]")
				->value(['1'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inArray_last() {

			$out = null;

			JsonParser::fromString("[19, 32, []]")
				->value(['2'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inArray_last_empty() {

			$out = null;

			JsonParser::fromString("[19, 32, [1,2,3]]")
				->value(['2'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[19, 32, []]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inArray_nested_empty() {

			$out = null;

			JsonParser::fromString("[[19, 32, [1,2,3]]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":[1,2,3], \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inObject_first_empty() {

			$out = null;

			JsonParser::fromString("{\"a\":[], \"b\": 19, \"c\":32}")
				->value(['a'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": [1,2,3], \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inObject_middle_empty() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": [], \"c\":32}")
				->value(['b'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":[]}")
				->value(['c'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inObject_last_empty() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\": 32, \"c\":[1,2,3]}")
				->value(['c'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_array_inObject_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":[]}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_array_inObject_nested_empty() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\":19, \"b\": 32, \"c\":[1,2,3]}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame([1, 2, 3], $out);
		}

		public function testValue_object_withZeroValue() {

			$out = null;

			JsonParser::fromString("{\"a\":0}")
				->value([], $out)
				->parse();

			$this->assertSame(['a' => 0], $out);
		}

		public function testValue_object_rootLevel() {

			$out = null;

			JsonParser::fromString("{\"a\":19}")
				->value([], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_rootLevel_empty() {

			$out = null;

			JsonParser::fromString("{}")
				->value([], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inArray_first() {

			$out = null;

			JsonParser::fromString("[{\"a\":19}]")
				->value(['0'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inArray_first_empty() {

			$out = null;

			JsonParser::fromString("[{}]")
				->value(['0'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inArray_middle() {

			$out = null;

			JsonParser::fromString("[15, {}, 19]")
				->value(['1'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inArray_middle_empty() {

			$out = null;

			JsonParser::fromString("[15, {\"a\":19}, 19]")
				->value(['1'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inArray_last() {

			$out = null;

			JsonParser::fromString("[15, 19, {\"a\":19}]")
				->value(['2'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inArray_last_empty() {

			$out = null;

			JsonParser::fromString("[15, 19, {}]")
				->value(['2'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inArray_nested() {

			$out = null;

			JsonParser::fromString("[[15, 19, {\"a\":19}]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inArray_nested_empty() {

			$out = null;

			JsonParser::fromString("[[15, 19, {}]]")
				->value(['0', '2'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inObject_first() {

			$out = null;

			JsonParser::fromString("{\"a\":{\"a\":19}, \"b\":15, \"c\": 19}")
				->value(['a'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inObject_first_empty() {

			$out = null;

			JsonParser::fromString("{\"a\":{}, \"b\":15, \"c\": 19}")
				->value(['a'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inObject_middle() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":{\"a\":19}, \"c\": 19}")
				->value(['b'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inObject_middle_empty() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":{}, \"c\": 19}")
				->value(['b'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inObject_last() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":19, \"c\": {\"a\":19}}")
				->value(['c'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inObject_last_empty() {

			$out = null;

			JsonParser::fromString("{\"a\": 15, \"b\":19, \"c\": {}}")
				->value(['c'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_inObject_nested() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\": 15, \"b\":19, \"c\": {\"a\":19}}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame(['a' => 19], $out);
		}

		public function testValue_object_inObject_nested_empty() {

			$out = null;

			JsonParser::fromString("{\"x\":{\"a\": 15, \"b\":19, \"c\": {}}}")
				->value(['x', 'c'], $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testValue_object_notAssociative() {

			$out = null;

			JsonParser::fromString("{\"a\":19}")
				->value([], $out, false)
				->parse();

			$this->assertEquals((object)['a' => 19], $out);
		}

		public function testValue_object_notAssociative_empty() {

			$out = null;

			JsonParser::fromString("{}")
				->value([], $out, false)
				->parse();

			$this->assertEquals(new stdClass(), $out);
		}

		public function testValue_object_keyWithDot() {

			$out1 = null;
			$out2 = null;

			JsonParser::fromString("{\"a.5\":19}")
				->value(['a.5'], $out1)
				->value(['a', '5'], $out2)
				->parse();

			$this->assertEquals(19, $out1);
			$this->assertEquals(null, $out2);
		}


		public function testValue_multipleCallbacks() {

			$outRoot = null;
			$outA    = null;
			$outA0   = null;
			$outA2B  = null;

			JsonParser::fromString("{\"a\": [ 13, null, { \"b\": \"54\"}, false ]}")
				->value(['a', '2', 'b'], $outA2B)
				->value(['a', '0'], $outA0)
				->value(['a'], $outA)
				->value([], $outRoot)
				->parse();


			$this->assertSame(['a' => [13, null, ['b' => '54'], false]], $outRoot);
			$this->assertSame([13, null, ['b' => '54'], false], $outA);
			$this->assertSame(13, $outA0);
			$this->assertSame("54", $outA2B);
		}

		public function testValue_multipleCallbacks_stringPathGiven() {

			$outRoot = null;
			$outA    = null;
			$outA0   = null;
			$outAb   = null;

			JsonParser::fromString("{\"a\": [ 13, null, { \"b\": \"54\"}, false ]}")
				->value('a.2.b', $outAb)
				->value('a.0', $outA0)
				->value('a', $outA)
				->value('', $outRoot)
				->parse();


			$this->assertSame(['a' => [13, null, ['b' => '54'], false]], $outRoot);
			$this->assertSame([13, null, ['b' => '54'], false], $outA);
			$this->assertSame("54", $outAb);
			$this->assertSame(13, $outA0);
		}

		public function testValue_customPathSeparator() {

			$out = null;

			JsonParser::fromString("[{\"a\":19}]")
				->value('0>a', $out, false, '>')
				->parse();

			$this->assertEquals(19, $out);
		}


		public function testCollectItemValues_object_root() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\":true}")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testCollectItemValues_object_root_empty() {

			$out = null;

			JsonParser::fromString("{}")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testCollectItemValues_object_root_withNested() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\":[1, {\"c\": null}]}")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame(['a' => 19, 'b' => [1, ['c' => null]]], $out);
		}

		public function testCollectItemValues_object_root_withNested_notAssociative() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\":[1, {\"c\": null}]}")
				->collectItemValues('', $out, false)
				->parse();

			$this->assertEquals(['a' => 19, 'b' => [1, (object)['c' => null]]], $out);
		}

		public function testCollectItemValues_object_inArray() {

			$out = null;

			JsonParser::fromString("[{\"a\":19, \"b\":true}]")
				->collectItemValues('0', $out)
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testCollectItemValues_object_inObject() {

			$out = null;

			JsonParser::fromString("{\"x\": {\"a\":19, \"b\":true}}")
				->collectItemValues('x', $out)
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testCollectItemValues_array_root() {

			$out = null;

			JsonParser::fromString("[19, true]")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testCollectItemValues_array_root_empty() {

			$out = null;

			JsonParser::fromString("[]")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame([], $out);
		}

		public function testCollectItemValues_array_root_withNested() {

			$out = null;

			JsonParser::fromString("[19, [1, {\"c\": null}]]")
				->collectItemValues('', $out)
				->parse();

			$this->assertSame([19, [1, ['c' => null]]], $out);
		}

		public function testCollectItemValues_array_inArray() {

			$out = null;

			JsonParser::fromString("[[19, true]]")
				->collectItemValues('0', $out)
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testCollectItemValues_array_inObject() {

			$out = null;

			JsonParser::fromString("{\"x\": [19, true]}")
				->collectItemValues('x', $out)
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testEachItemValue_object_root() {

			$out = [];

			JsonParser::fromString("{\"a\":19, \"b\":true}")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testEachItemValue_object_root_empty() {

			$out = null;

			JsonParser::fromString("{}")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(null, $out);
		}

		public function testEachItemValue_object_root_withNested() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\":[1, {\"c\": null}]}")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(['a' => 19, 'b' => [1, ['c' => null]]], $out);
		}

		public function testEachItemValue_object_root_withNested_notAssociative() {

			$out = null;

			JsonParser::fromString("{\"a\":19, \"b\":[1, {\"c\": null}]}")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				}, false)
				->parse();

			$this->assertEquals(['a' => 19, 'b' => [1, (object)['c' => null]]], $out);
		}

		public function testEachItemValue_object_inArray() {

			$out = null;

			JsonParser::fromString("[{\"a\":19, \"b\":true}]")
				->eachItemValue('0', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testEachItemValue_object_inObject() {

			$out = null;

			JsonParser::fromString("{\"x\": {\"a\":19, \"b\":true}}")
				->eachItemValue('x', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(['a' => 19, 'b' => true], $out);
		}

		public function testEachItemValue_array_root() {

			$out = null;

			JsonParser::fromString("[19, true]")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testEachItemValue_array_root_empty() {

			$out = null;

			JsonParser::fromString("[]")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame(null, $out);
		}

		public function testEachItemValue_array_root_withNested() {

			$out = null;

			JsonParser::fromString("[19, [1, {\"c\": null}]]")
				->eachItemValue('', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame([19, [1, ['c' => null]]], $out);
		}

		public function testEachItemValue_array_inArray() {

			$out = null;

			JsonParser::fromString("[[19, true]]")
				->eachItemValue('0', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testEachItemValue_array_inObject() {

			$out = null;

			JsonParser::fromString("{\"x\": [19, true]}")
				->eachItemValue('x', function ($value, $key) use (&$out) {
					$out[$key] = $value;
				})
				->parse();

			$this->assertSame([19, true], $out);
		}

		public function testCollection() {

			$parser = JsonParser::fromString("{\"x\": [19, true]}");

			$parser->collection('x', function ($p) use ($parser) {
				$this->assertSame($parser, $p);
			})
				->parse();
		}

		public function testCollection_nestedPath() {

			$parser = JsonParser::fromString("{\"a\":{\"x\": [19, true]}}");

			$parser->collection('a.x', function ($p) use ($parser) {
				$this->assertSame($parser, $p);
			})
				->parse();
		}

		public function testCollection_withConsume() {

			$parser = JsonParser::fromString("{\"a\":{\"x\": [19, true, 5], \"y\":4}}");

			$parser
				->collection('a.x', function ($p) use ($parser) {
					$this->assertSame($parser, $p);

					$out0 = null;
					$out1 = null;
					$out2 = null;

					$parser
						->value('0', $out0)
						->value('1', $out1)
						->consume()
						->value('2', $out2);

					$this->assertSame(19, $out0);
					$this->assertSame(true, $out1);
					$this->assertSame(null, $out2);
				})
				->value('a.y', $aY)
				->parse();

			$this->assertSame(4, $aY);
		}

		public function testEachItem_object_root() {

			$parser = JsonParser::fromString("{\"x\": 1, \"y\": 2, \"z\": 3}");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame(['x', 'y', 'z'], $keys);
		}

		public function testEachItem_object_root_empty() {

			$parser = JsonParser::fromString("{}");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([], $keys);
		}

		public function testEachItem_object_root_withNested() {

			$parser = JsonParser::fromString("{\"x\": {\"a\": [1, {\"z\": 15}]}, \"y\": 2, \"z\": 3}");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame(['x', 'y', 'z'], $keys);
		}

		public function testEachItem_object_inArray() {

			$parser = JsonParser::fromString("[{\"x\": 1, \"y\": 2, \"z\": 3}]");

			$keys = [];

			$parser->eachItem('0', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame(['x', 'y', 'z'], $keys);
		}

		public function testEachItem_object_inObject() {

			$parser = JsonParser::fromString("{\"z\": {\"x\": 1, \"y\": 2, \"z\": 3}}");

			$keys = [];

			$parser->eachItem('z', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame(['x', 'y', 'z'], $keys);
		}

		public function testEachItem_array_root() {

			$parser = JsonParser::fromString("[5, 6, 7]");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([0, 1, 2], $keys);
		}

		public function testEachItem_array_root_empty() {

			$parser = JsonParser::fromString("[]");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([], $keys);
		}

		public function testEachItem_array_root_withNested() {

			$parser = JsonParser::fromString("[{\"a\": [1, {\"z\": 15}]}, 2, 3]");

			$keys = [];

			$parser->eachItem('', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([0, 1, 2], $keys);
		}

		public function testEachItem_array_inArray() {

			$parser = JsonParser::fromString("[[5, 6, 7]]");

			$keys = [];

			$parser->eachItem('0', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([0, 1, 2], $keys);
		}

		public function testEachItem_array_inObject() {

			$parser = JsonParser::fromString("{\"z\": [5, 6, 7]}");

			$keys = [];

			$parser->eachItem('z', function ($p, $k) use ($parser, &$keys) {
				$this->assertSame($parser, $p);
				$keys[] = $k;
			})
				->parse();

			$this->assertSame([0, 1, 2], $keys);
		}

		public function testParseLargeStrings() {

			$data = [
				'x' => 1,
				'y' => str_repeat('a', 5 * 1024 * 1024),
				'z' => true
			];


			JsonParser::fromString(json_encode($data))
				->value('x', $x)
				->value('y', $y)
				->value('z', $z)
				->parse();

			$this->assertSame($data['x'], $x);
			$this->assertSame($data['y'], $y);
			$this->assertSame($data['z'], $z);
		}

		public function testParseExceedBufferSize() {

			$data = [
				'x' => 1,
				'y' => str_repeat('a', 2 * 1024 * 1024),
				'z' => true
			];

			$this->expectException(JsonException::class);
			$this->expectExceptionMessageMatches('/JSON parser buffer size/');
			

			JsonParser::fromString(json_encode($data))
				->setBufferMaxSize(1024 * 1024 + 1)
				->parse();
		}

		public function testFromResource() {

			$data = [
				'x' => 1,
				'y' => null,
				'z' => true
			];

			$tmpFile = tmpfile();

			fwrite($tmpFile, json_encode($data));
			rewind($tmpFile);


			(new JsonParser($tmpFile))
				->value('', $out)
				->parse();

			$this->assertSame($data, $out);
		}
		
		public function testFromFile() {

			$data = [
				'x' => 1,
				'y' => null,
				'z' => true
			];

			$tmpFile = tempnam(sys_get_temp_dir(), 'phpunit-easy-json');

			try {
				file_put_contents($tmpFile, json_encode($data));


				(new JsonParser($tmpFile))
					->value('', $out)
					->parse();

				$this->assertSame($data, $out);
			}
			finally {
				@unlink($tmpFile);
			}
		}

		public function testParse_emptyDocument() {

			$this->expectException(JsonSyntaxException::class);
			$this->expectExceptionMessageMatches('/Unexpected end of JSON document/');

			JsonParser::fromString("")
				->parse();
		}

		public function testParse_emptyDocument_onlyWhitespace() {

			$this->expectException(JsonSyntaxException::class);
			$this->expectExceptionMessageMatches('/Unexpected end of JSON document/');

			JsonParser::fromString(" \t")
				->parse();
		}

		public function testParse_additionalContent() {

			$this->expectException(JsonSyntaxException::class);
			$this->expectExceptionMessageMatches('/Unexpected content after JSON root element/');

			JsonParser::fromString("\"\"g")
				->parse();
		}
	}
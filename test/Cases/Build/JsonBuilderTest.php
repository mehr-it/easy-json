<?php

	namespace MehrItEasyJsonTest\Cases\Build;

	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\JsonFragment;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use MehrIt\EasyJson\Exception\JsonException;
	use MehrItEasyJsonTest\Cases\TestCase;
	use stdClass;

	class JsonBuilderTest extends TestCase
	{
		public function testWrite_basicValues() {

			$values = [
				null,
				true,
				false,
				0,
				12345,
				-12345,
				0.0,
				123.45,
				-123.45,
				'a b c',
				"a\rb\nc",
				"\x0",
				[],
				[1, 2, 3],
				['a', 'b', 'c'],
				json_encode(json_decode("{}")),
				(object)null,
				new stdClass(),
				['x' => 1, 'y' => 2, 'z' => 3],
				['x' => 'a', 'y' => 'b', 'z' => 'c'],
				[0 => 1, 2 => 2, 3 => 3],
				(object)['x' => 1, 'y' => 2, 'z' => 3],
				(object)['a', 'b', 'c'],
			];

			foreach ($values as $currValue) {
				$this->assertSame(json_encode($currValue, JSON_THROW_ON_ERROR), (new JsonBuilder())->write($currValue)->getBuffer());
			}
		}

		public function testWrite_generator() {
			$gen = function () {
				yield from ['a', 'b', 'c'];
			};

			$this->assertSame(json_encode(['a', 'b', 'c'], JSON_THROW_ON_ERROR), (new JsonBuilder())->write($gen())->getBuffer());
		}

		public function testWrite_jsonFragment() {

			$this->assertSame('the fragment', (new JsonBuilder())->write(new JsonFragment('the fragment'))->getBuffer());
		}

		public function testWrite_closure() {

			$clos = function (JsonBuilder $builder) {
				$builder->write('a');
			};

			$this->assertSame('"a"', (new JsonBuilder())->write($clos)->getBuffer());
		}

		public function testWrite_closureNotWritingAnything() {

			$clos = function (JsonBuilder $builder) {
			};

			$this->expectException(JsonException::class);

			(new JsonBuilder())->write($clos);

		}


		public function testWrite_jsonSerializable() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('serializeJson')
				->with($builder)
				->willReturnCallback(function (JsonBuilder $builder) {
					$builder->write('a');
				});

			$this->assertSame('"a"', $builder->write($ser)->getBuffer());
		}

		public function testWrite_jsonSerializable_notWritingData() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('serializeJson')
				->with($builder)
				->willReturnCallback(function (JsonBuilder $builder) {
				});

			$this->expectException(JsonException::class);

			$builder->write($ser);
		}

		public function testWrite_nativeJsonSerializable() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(\JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('jsonSerialize')
				->willReturnCallback(function () {
					return 'a';
				});

			$this->assertSame('"a"', $builder->write($ser)->getBuffer());
		}

		public function testWrite_arrayWithJsonSerializable() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('serializeJson')
				->with($builder)
				->willReturnCallback(function (JsonBuilder $builder) {
					$builder->write('a');
				});

			$this->assertSame('["a"]', $builder->write([$ser])->getBuffer());
		}

		public function testWrite_invalidUtf8Sequence() {

			$this->expectException(JsonException::class);

			(new JsonBuilder())->write("\xa0\xa1");
		}

		public function testWriteAsArray_sequential() {

			$this->assertSame('["a"]', (new JsonBuilder())->writeAsArray(['a'])->getBuffer());
		}

		public function testWriteAsArray_assoc() {

			$this->assertSame('["a"]', (new JsonBuilder())->writeAsArray(['z' => 'a'])->getBuffer());
		}

		public function testWriteAsArray_assocWithJsonSerializable() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('serializeJson')
				->with($builder)
				->willReturnCallback(function (JsonBuilder $builder) {
					$builder->write('a');
				});

			$this->assertSame('["a"]', $builder->writeAsArray(['z' => $ser])->getBuffer());
		}

		public function testWriteAsObject_sequential() {

			$this->assertSame('{"0":"a"}', (new JsonBuilder())->writeAsObject(['a'])->getBuffer());
		}

		public function testWriteAsObject_assoc() {

			$this->assertSame('{"z":"a"}', (new JsonBuilder())->writeAsObject(['z' => 'a'])->getBuffer());
		}

		public function testWriteAsObject_withJsonSerializable() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('serializeJson')
				->with($builder)
				->willReturnCallback(function (JsonBuilder $builder) {
					$builder->write('a');
				});

			$this->assertSame('{"z":"a"}', $builder->writeAsObject(['z' => $ser])->getBuffer());
		}

		public function testWrite_toResource() {

			$res = fopen('php://memory', 'w+');

			(new JsonBuilder($res))->write('a');

			rewind($res);

			$this->assertSame('"a"', stream_get_contents($res));

		}

		public function testWrite_toFile() {

			$tmpFile = tempnam(sys_get_temp_dir(), 'phpunit-easy-json');

			try {
				(new JsonBuilder($tmpFile))->write('a');

				$this->assertSame('"a"', file_get_contents($tmpFile));
			}
			finally {
				@unlink($tmpFile);
			}
		}
	}
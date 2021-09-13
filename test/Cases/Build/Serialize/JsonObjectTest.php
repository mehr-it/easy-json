<?php

	namespace MehrItEasyJsonTest\Cases\Build\Serialize;

	use JsonSerializable;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\Serialize\JsonObject;
	use MehrItEasyJsonTest\Cases\TestCase;

	class JsonObjectTest extends TestCase
	{
		public function testSerializeJson_null() {

			$o = new JsonObject(null);

			$this->assertSame('{}', (new JsonBuilder())->write($o)->getBuffer());

		}

		public function testSerializeJson_sequential() {

			$o = new JsonObject(['a', 'b']);

			$this->assertSame('{"0":"a","1":"b"}', (new JsonBuilder())->write($o)->getBuffer());

		}

		public function testSerializeJson_assoc() {

			$o = new JsonObject(['x' => 'a', 'y' => 'b']);

			$this->assertSame('{"x":"a","y":"b"}', (new JsonBuilder())->write($o)->getBuffer());

		}
		
		public function testSerializeJson_closure() {

			$o = new JsonObject(function() {
				return ['x' => 'a', 'y' => 'b'];
			});

			$this->assertSame('{"x":"a","y":"b"}', (new JsonBuilder())->write($o)->getBuffer());

		}

		public function testSerializeJson_object() {

			$o = new JsonObject((object)['x' => 'a', 'y' => 'b']);

			$this->assertSame('{"x":"a","y":"b"}', (new JsonBuilder())->write($o)->getBuffer());

		}

		public function testSerializeJson_nativeJsonSerializable_returningScalar() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('jsonSerialize')
				->willReturnCallback(function () {
					return 'a';
				});

			$o = new JsonObject($ser);

			$this->assertSame('{"0":"a"}', $builder->write($o)->getBuffer());

		}

		public function testSerializeJson_nativeJsonSerializable_returningNull() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('jsonSerialize')
				->willReturnCallback(function () {
					return null;
				});

			$o = new JsonObject($ser);

			$this->assertSame('{}', $builder->write($o)->getBuffer());

		}

		public function testSerializeJson_nativeJsonSerializable_returningSequential() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('jsonSerialize')
				->willReturnCallback(function () {
					return ['a', 'b'];
				});

			$o = new JsonObject($ser);

			$this->assertSame('{"0":"a","1":"b"}', $builder->write($o)->getBuffer());

		}

		public function testSerializeJson_nativeJsonSerializable_returningAssoc() {

			$builder = new JsonBuilder();

			$ser = $this->getMockBuilder(JsonSerializable::class)->getMock();
			$ser
				->expects($this->once())
				->method('jsonSerialize')
				->willReturnCallback(function () {
					return ['x' => 'a', 'y' => 'b'];
				});

			$o = new JsonObject($ser);

			$this->assertSame('{"x":"a","y":"b"}', $builder->write($o)->getBuffer());

		}
	}
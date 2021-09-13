<?php

	namespace MehrItEasyJsonTest\Cases\Build\Serialize;

	use JsonSerializable;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\Serialize\JsonArray;
	use MehrItEasyJsonTest\Cases\TestCase;

	class JsonArrayTest extends TestCase
	{

		public function testSerializeJson_null() {
			
			$o = new JsonArray(null);

			$this->assertSame('[]', (new JsonBuilder())->write($o)->getBuffer());
			
		}
		
		public function testSerializeJson_sequential() {
			
			$o = new JsonArray(['a', 'b']);

			$this->assertSame('["a","b"]', (new JsonBuilder())->write($o)->getBuffer());
			
		}
		
		public function testSerializeJson_assoc() {
			
			$o = new JsonArray(['x' => 'a', 'y' => 'b']);

			$this->assertSame('["a","b"]', (new JsonBuilder())->write($o)->getBuffer());
			
		}
		
		public function testSerializeJson_closure() {
			
			$o = new JsonArray(function() {
				return ['x' => 'a', 'y' => 'b'];
			});

			$this->assertSame('["a","b"]', (new JsonBuilder())->write($o)->getBuffer());
			
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

			$o = new JsonArray($ser);

			$this->assertSame('["a"]', $builder->write($o)->getBuffer());
			
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

			$o = new JsonArray($ser);

			$this->assertSame('[]', $builder->write($o)->getBuffer());
			
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

			$o = new JsonArray($ser);

			$this->assertSame('["a","b"]', $builder->write($o)->getBuffer());
			
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

			$o = new JsonArray($ser);

			$this->assertSame('["a","b"]', $builder->write($o)->getBuffer());
			
		}
		
	}
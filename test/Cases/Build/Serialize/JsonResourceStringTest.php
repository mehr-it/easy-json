<?php

	namespace MehrItEasyJsonTest\Cases\Build\Serialize;

	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\Serialize\JsonResourceString;
	use MehrItEasyJsonTest\Cases\TestCase;

	class JsonResourceStringTest extends TestCase
	{

		protected function resourceFromString(string $value) {
			
			$res = fopen('php://memory', 'w+');
			
			fwrite($res, $value);
			rewind($res);
			
			return $res;			
		}
		
		public function testSerializeJson() {

			$res = $this->resourceFromString('abc');
			
			$o = new JsonResourceString($res);

			$this->assertSame(json_encode('abc', JSON_THROW_ON_ERROR), (new JsonBuilder())->write($o)->getBuffer());
		}
		
		public function testSerializeJson_emptyString() {

			$res = $this->resourceFromString('');
			
			$o = new JsonResourceString($res);

			$this->assertSame(json_encode('', JSON_THROW_ON_ERROR), (new JsonBuilder())->write($o)->getBuffer());
		}
		
		public function testSerializeJson_base64() {

			$res = $this->resourceFromString('abc');
			
			$o = new JsonResourceString($res, true);

			$this->assertSame(json_encode(base64_encode('abc'), JSON_THROW_ON_ERROR), (new JsonBuilder())->write($o)->getBuffer());
		}

		public function testSerializeJson_multipleChunks() {

			$res = $this->resourceFromString('abc');

			$o = new JsonResourceString($res, false, 2);

			$this->assertSame(json_encode('abc', JSON_THROW_ON_ERROR), (new JsonBuilder())->write($o)->getBuffer());
		}

		public function testSerializeJson_base64_multipleChunks() {

			$res = $this->resourceFromString('abc');

			$o = new JsonResourceString($res, true, 2);

			$this->assertSame(json_encode(base64_encode('abc'), JSON_THROW_ON_ERROR), (new JsonBuilder())->write($o)->getBuffer());
		}
	}
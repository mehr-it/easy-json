<?php

	namespace MehrItEasyJsonTest\Cases\Build\Serialize;

	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\Serialize\JsonNumber;
	use MehrItEasyJsonTest\Cases\TestCase;

	class JsonNumberTest extends TestCase
	{

		public function testSerializeJson() {
			
			$values = [
				[true, '1'],
				[false, '0'],
				[0, '0'],
				[1234, '1234'],
				[-1234, '-1234'],
				[0.0, '0'],
				[12.34, '12.34'],
				[-12.34, '-12.34'],
				['0', '0'],
				['1234', '1234'],
				['-1234', '-1234'],
				['+1234', '1234'],
				['0.0', '0'],
				['12.34', '12.34'],
				['+12.34', '12.34'],
				['-12.34', '-12.34'],
			];
			
			foreach($values as $curr) {
				$this->assertSame($curr[1], (new JsonBuilder())->write(new JsonNumber($curr[0]))->getBuffer());
			}
			
		}
		
	}
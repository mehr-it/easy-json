<?php


	namespace MehrItEasyJsonTest\Cases\Util;


	use MehrIt\EasyJson\Util\Numbers;
	use MehrItEasyJsonTest\Cases\TestCase;

	class NumbersTest extends TestCase
	{

		public function testReduceExponent() {
			
			$this->assertSame('0', Numbers::reduceExponent('0'));
			$this->assertSame('-0', Numbers::reduceExponent('-0'));
			$this->assertSame('1', Numbers::reduceExponent('1'));
			$this->assertSame('-1', Numbers::reduceExponent('-1'));
			$this->assertSame('1', Numbers::reduceExponent('01'));
			$this->assertSame('-1', Numbers::reduceExponent('-01'));
			$this->assertSame('12', Numbers::reduceExponent('12'));
			$this->assertSame('-12', Numbers::reduceExponent('-12'));
			$this->assertSame('12', Numbers::reduceExponent('012'));
			$this->assertSame('-12', Numbers::reduceExponent('-012'));
			$this->assertSame('0', Numbers::reduceExponent('0.0'));
			$this->assertSame('1.2', Numbers::reduceExponent('1.2'));
			$this->assertSame('-1.2', Numbers::reduceExponent('-1.2'));
			$this->assertSame('1.234', Numbers::reduceExponent('1.234'));
			$this->assertSame('-1.234', Numbers::reduceExponent('-1.234'));
			$this->assertSame('12.345', Numbers::reduceExponent('12.345'));
			$this->assertSame('-12.345', Numbers::reduceExponent('-12.345'));
			$this->assertSame('1', Numbers::reduceExponent('1.0'));
			$this->assertSame('-1', Numbers::reduceExponent('-1.0'));
			$this->assertSame('1.01', Numbers::reduceExponent('1.01'));
			$this->assertSame('-1.01', Numbers::reduceExponent('-1.01'));
			$this->assertSame('1.2', Numbers::reduceExponent('1.20'));
			$this->assertSame('-1.2', Numbers::reduceExponent('-1.20'));


			$this->assertSame('0.1', Numbers::reduceExponent('0.1e0'));
			$this->assertSame('0.1', Numbers::reduceExponent('0.1e+0'));
			$this->assertSame('0.1', Numbers::reduceExponent('0.1e-0'));

			$this->assertSame('1', Numbers::reduceExponent('0.1e1'));
			$this->assertSame('1', Numbers::reduceExponent('0.1e+1'));
			$this->assertSame('0.01', Numbers::reduceExponent('0.1e-1'));

			$this->assertSame('10', Numbers::reduceExponent('0.1e2'));
			$this->assertSame('10', Numbers::reduceExponent('0.1e+2'));
			$this->assertSame('0.001', Numbers::reduceExponent('0.1e-2'));
			
			
			
			$this->assertSame('1', Numbers::reduceExponent('1e0'));
			$this->assertSame('1', Numbers::reduceExponent('1e+0'));
			$this->assertSame('1', Numbers::reduceExponent('1e-0'));
			
			$this->assertSame('10', Numbers::reduceExponent('1e1'));
			$this->assertSame('10', Numbers::reduceExponent('1e+1'));
			$this->assertSame('0.1', Numbers::reduceExponent('1e-1'));
			
			$this->assertSame('100', Numbers::reduceExponent('1e2'));
			$this->assertSame('100', Numbers::reduceExponent('1e+2'));
			$this->assertSame('0.01', Numbers::reduceExponent('1e-2'));
			
			
			
			$this->assertSame('1.2', Numbers::reduceExponent('1.2e0'));
			$this->assertSame('1.2', Numbers::reduceExponent('1.2e+0'));
			$this->assertSame('1.2', Numbers::reduceExponent('1.2e-0'));
			
			$this->assertSame('12', Numbers::reduceExponent('1.2e1'));
			$this->assertSame('12', Numbers::reduceExponent('1.2e+1'));
			$this->assertSame('0.12', Numbers::reduceExponent('1.2e-1'));
			
			$this->assertSame('120', Numbers::reduceExponent('1.2e2'));
			$this->assertSame('120', Numbers::reduceExponent('1.2e+2'));
			$this->assertSame('0.012', Numbers::reduceExponent('1.2e-2'));
			
			
			
			$this->assertSame('51', Numbers::reduceExponent('51e0'));
			$this->assertSame('51', Numbers::reduceExponent('51e+0'));
			$this->assertSame('51', Numbers::reduceExponent('51e-0'));
			
			$this->assertSame('510', Numbers::reduceExponent('51e1'));
			$this->assertSame('510', Numbers::reduceExponent('51e+1'));
			$this->assertSame('5.1', Numbers::reduceExponent('51e-1'));
			
			$this->assertSame('5100', Numbers::reduceExponent('51e2'));
			$this->assertSame('5100', Numbers::reduceExponent('51e+2'));
			$this->assertSame('0.51', Numbers::reduceExponent('51e-2'));
			
			
			
			$this->assertSame('51.2', Numbers::reduceExponent('51.2e0'));
			$this->assertSame('51.2', Numbers::reduceExponent('51.2e+0'));
			$this->assertSame('51.2', Numbers::reduceExponent('51.2e-0'));
			
			$this->assertSame('512', Numbers::reduceExponent('51.2e1'));
			$this->assertSame('512', Numbers::reduceExponent('51.2e+1'));
			$this->assertSame('5.12', Numbers::reduceExponent('51.2e-1'));
			
			$this->assertSame('5120', Numbers::reduceExponent('51.2e2'));
			$this->assertSame('5120', Numbers::reduceExponent('51.2e+2'));
			$this->assertSame('0.512', Numbers::reduceExponent('51.2e-2'));
			
			
			$this->assertSame('123.456', Numbers::reduceExponent('123.456e0'));
			$this->assertSame('123.456', Numbers::reduceExponent('123.456e+0'));
			$this->assertSame('123.456', Numbers::reduceExponent('123.456e-0'));
			
			$this->assertSame('1234.56', Numbers::reduceExponent('123.456e1'));
			$this->assertSame('1234.56', Numbers::reduceExponent('123.456e+1'));
			$this->assertSame('12.3456', Numbers::reduceExponent('123.456e-1'));
			
			$this->assertSame('12345.6', Numbers::reduceExponent('123.456e2'));
			$this->assertSame('12345.6', Numbers::reduceExponent('123.456e+2'));
			$this->assertSame('1.23456', Numbers::reduceExponent('123.456e-2'));
			
			$this->assertSame('123456', Numbers::reduceExponent('123.456e3'));
			$this->assertSame('123456', Numbers::reduceExponent('123.456e+3'));
			$this->assertSame('0.123456', Numbers::reduceExponent('123.456e-3'));
			
			$this->assertSame('1234560', Numbers::reduceExponent('123.456e4'));
			$this->assertSame('1234560', Numbers::reduceExponent('123.456e+4'));
			$this->assertSame('0.0123456', Numbers::reduceExponent('123.456e-4'));
			
			$this->assertSame('12345600', Numbers::reduceExponent('123.456e5'));
			$this->assertSame('12345600', Numbers::reduceExponent('123.456e+5'));
			$this->assertSame('0.00123456', Numbers::reduceExponent('123.456e-5'));

		}
		
	}
<?php

	namespace MehrIt\EasyJson\Build\Serialize;

	use InvalidArgumentException;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\JsonFragment;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use MehrIt\PhpDecimals\Decimals;

	class JsonNumber implements JsonSerializable
	{
		/**
		 * @var string
		 */
		protected $value;

		/**
		 * Creates a new instance
		 * @param string|float|integer|bool $value The value
		 */
		public function __construct($value) {
			
			switch(gettype($value)) {
				case 'boolean':
					$this->value = ($value ? '1' : '0');
					break;
					
				case 'integer':
					$this->value = (string)$value;
					break;
					
				case 'double':
				case 'string':
					$this->value = Decimals::parse($value);
					break;
					
				default:
					throw new InvalidArgumentException('Expected string, number or boolean, but got ' . gettype($value));
			}
		}

		/**
		 * Gets the value
		 * @return string 
		 */
		public function getValue(): string {
			return $this->value;
		}
		
		/**
		 * @inheritDoc
		 */
		public function serializeJson(JsonBuilder $builder) {
			$builder->write(new JsonFragment($this->value));
		}


	}
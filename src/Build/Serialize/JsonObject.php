<?php

	namespace MehrIt\EasyJson\Build\Serialize;

	use Closure;
	use InvalidArgumentException;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use RuntimeException;

	class JsonObject implements JsonSerializable
	{
		/**
		 * @var object|null 
		 */
		protected $value;

		/**
		 * Creates a new instance
		 * @param object|array|null $value The value
		 */
		public function __construct($value) {
			
			if (!is_object($value) && !is_iterable($value) && $value !== null)
				throw new InvalidArgumentException('Expected object, iterable or null, but got ' . gettype($value));
			
			$this->value = $value;
		}

		/**
		 * Gets the value
		 * @return object|null
		 */
		public function getValue() {
			return $this->value;
		}


		/**
		 * @inheritDoc 
		 */
		public function serializeJson(JsonBuilder $builder) {
			
			$value = $this->value;

			// allow generator
			if ($value instanceof Closure) {
				$value = $value();

				if (!is_iterable($value))
					throw new RuntimeException('Callback must return an iterable, got ' . gettype($value));
			}
			
			// Convert \JsonSerializable to array. Then we will convert the result as object. 
			if ($value instanceof \JsonSerializable) 
				$value = (array)$value->jsonSerialize();
			
			
			if ($value === null || $value === []) {
				$builder->writeAsObject([]);
			}
			else if (is_iterable($value)) {
				$builder->writeAsObject($value);
			}
			else {
				$builder->write($value);
			}
		}


	}
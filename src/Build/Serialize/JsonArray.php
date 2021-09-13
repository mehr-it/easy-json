<?php

	namespace MehrIt\EasyJson\Build\Serialize;

	use Closure;
	use InvalidArgumentException;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use RuntimeException;
	use Traversable;

	class JsonArray implements JsonSerializable
	{
		/**
		 * @var \JsonSerializable|array|Traversable|null
		 */
		protected $value;

		/**
		 * Creates a new instance
		 * @param \JsonSerializable|\Closure|array|Traversable|null $value The value
		 */
		public function __construct($value) {

			if (!($value instanceof \JsonSerializable) && !is_iterable($value) && $value !== null && !($value instanceof Closure))
				throw new InvalidArgumentException('Expected \JsonSerializable, iterable or null, but got ' . gettype($value));

			$this->value = $value;
		}

		/**
		 * Gets the value
		 * @return array|\JsonSerializable|Traversable|null
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

			if ($value === null)
				$value = [];

			$builder->writeAsArray($value);
		}


	}
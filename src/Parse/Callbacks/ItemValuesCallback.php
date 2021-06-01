<?php


	namespace MehrIt\EasyJson\Parse\Callbacks;


	use MehrIt\EasyJson\Exception\JsonException;
	use MehrIt\EasyJson\Parse\JsonParser;
	use stdClass;

	class ItemValuesCallback extends AbstractCallback
	{
		const STATE_NOT_NESTED = 0;
		const STATE_ARRAY = 1;
		const STATE_OBJECT_KEY = 2;
		const STATE_OBJECT_FIELD = 3;

		/**
		 * @var callable
		 */
		protected $handler;

		/**
		 * @var bool
		 */
		protected $associative;

		/**
		 * @var array
		 */
		protected $value = [];

		/**
		 * @var int
		 */
		protected $state = self::STATE_NOT_NESTED;

		/**
		 * @var int
		 */
		protected $outIndex = 0;


		/**
		 * Creates a new instance
		 * @param array $path The path
		 * @param callable $handler The handler
		 * @param bool $associative True if to return objects as associative arrays
		 */
		public function __construct(array $path, callable $handler, bool $associative) {

			parent::__construct($path);

			$this->handler     = $handler;
			$this->associative = $associative;
		}

		/**
		 * @inheritDoc
		 */
		public function invoke(JsonParser $parser, int $elementType, $elementValue) {

			switch ($elementType) {
				case JsonParser::ELEMENT_STRING:
				case JsonParser::ELEMENT_NUMBER:
				case JsonParser::ELEMENT_NULL:
				case JsonParser::ELEMENT_BOOLEAN:
					$this->pushValue($elementValue);
					break;

				case JsonParser::ELEMENT_OBJECT_START:
					
					if ($this->state !== self::STATE_NOT_NESTED) {
						// nested object
						$this->value[] = $this->state;
						$this->value[] = $this->associative ? [] : new stdClass();
					}
						
					$this->state = self::STATE_OBJECT_KEY;
					break;

				case JsonParser::ELEMENT_ARRAY_START:

					if ($this->state !== self::STATE_NOT_NESTED) {
						// nested array

						$this->value[] = $this->state;
						$this->value[] = [];
					}

					$this->state = self::STATE_ARRAY;
					break;

				case JsonParser::ELEMENT_OBJECT_END:
				case JsonParser::ELEMENT_ARRAY_END:
					
					if ($this->state !== self::STATE_NOT_NESTED) {
						// pop the value
						$value = array_pop($this->value);

						// restore state (which is now at the top of the value stack)
						$this->state = array_pop($this->value);

						// push array/object as value
						$this->pushValue($value);
					}

					break;

			}

		}

		/**
		 * @inheritDoc
		 */
		public function captureNested(): bool {
			// be recursive when in object or array to catch nested values
			return $this->state !== self::STATE_NOT_NESTED;
		}


		/**
		 * Pushes the given value to the value stack
		 * @param mixed $value The value
		 */
		protected function pushValue($value): void {

			switch ($this->state) {

				case self::STATE_ARRAY:

					if (count($this->value)) {
						// append to nested array
						$this->value[array_key_last($this->value)][] = $value;
					}
					else {
						// output
						$this->invokeCallback($value, $this->outIndex++);
					}


					break;

				case self::STATE_OBJECT_KEY:

					// check for key name restrictions when serializing to objects
					if (!$this->associative && $value === '' || ($value[0] ?? null) === "\0")
						throw new JsonException("Object key must not be empty or start with zero byte when associative mode is disabled.");

					// put the object key to stock top for later usage					
					$this->value[] = $value;

					$this->state = self::STATE_OBJECT_FIELD;
					break;

				case self::STATE_OBJECT_FIELD:

					// pop the target key
					$key = array_pop($this->value);

					if (count($this->value)) {
						// append to nested object

						if ($this->associative)
							$this->value[array_key_last($this->value)][$key] = $value;
						else
							$this->value[array_key_last($this->value)]->{$key} = $value;
					}
					else {
						// output
						$this->invokeCallback($value, $key);

					}

					$this->state = self::STATE_OBJECT_KEY;
			}
		}

		/**
		 * Outputs the captured value
		 */
		protected function invokeCallback($value, $key): void {
			call_user_func($this->handler, $value, $key);
		}
	}
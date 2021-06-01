<?php


	namespace MehrIt\EasyJson\Parse\Callbacks;


	use MehrIt\EasyJson\Parse\JsonParser;

	class ItemsCallback extends AbstractCallback
	{
		const STATE_NOT_NESTED = 0;
		const STATE_ARRAY = 1;
		const STATE_OBJECT_KEY = 2;
		const STATE_OBJECT_VALUE = 3;
		const STATE_ARRAY_DEEP = 4;
		const STATE_OBJECT_DEEP = 5;

		/**
		 * @var callable
		 */
		protected $handler;
		

		/**
		 * @var array
		 */
		protected $value = [];

		/**
		 * @var int
		 */
		protected $state = self::STATE_NOT_NESTED;

		protected $arrayIndex = -1;
		
		protected $objectKey = null;


		/**
		 * Creates a new instance
		 * @param array $path The path
		 * @param callable $handler The handler
		 * @param bool $associative True if to return objects as associative arrays
		 */
		public function __construct(array $path, callable $handler) {

			parent::__construct($path);

			$this->handler     = $handler;
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
					switch ($this->state) {	
						case self::STATE_ARRAY:
							++$this->arrayIndex;
							$this->invokeCallback($parser);
							break;

						case self::STATE_OBJECT_KEY:
							$this->objectKey = $elementValue;
							$this->state = self::STATE_OBJECT_VALUE;
							break;
							
						case self::STATE_OBJECT_VALUE:
							$this->invokeCallback($parser);
							
							$this->state = self::STATE_OBJECT_KEY;
							break;	
					}
					break;

				case JsonParser::ELEMENT_OBJECT_START:
					switch ($this->state) {
						case self::STATE_NOT_NESTED:
							$this->state = self::STATE_OBJECT_KEY;

							break;

						case self::STATE_OBJECT_VALUE:
							$this->invokeCallback($parser);

							$this->state = self::STATE_OBJECT_DEEP;
							break;
							
						case self::STATE_ARRAY:
							++$this->arrayIndex;
							$this->invokeCallback($parser);

							$this->state = self::STATE_ARRAY_DEEP;
							break;

					}


					break;
					
				case JsonParser::ELEMENT_ARRAY_START:
					
					switch($this->state) {
						case self::STATE_NOT_NESTED:
							$this->state = self::STATE_ARRAY;
							
							break;
							
						case self::STATE_ARRAY:
							++$this->arrayIndex;
							$this->invokeCallback($parser);
							
							$this->state = self::STATE_ARRAY_DEEP;
							break;
							
						case self::STATE_OBJECT_VALUE:
							$this->invokeCallback($parser);
							
							$this->state = self::STATE_OBJECT_DEEP;
							break;
							
					}

					
					break;

				case JsonParser::ELEMENT_OBJECT_END:
				case JsonParser::ELEMENT_ARRAY_END:
					switch($this->state) {
						case self::STATE_OBJECT_DEEP:
							$this->state = self::STATE_OBJECT_KEY;
							break;
						case self::STATE_ARRAY_DEEP:
							$this->state = self::STATE_ARRAY;
							break;
					}
					break;

			}

		}

		/**
		 * @inheritDoc
		 */
		public function captureNested(): bool {
			
			// be recursive when in at item level
			switch($this->state) {
				case self::STATE_OBJECT_KEY:
				case self::STATE_OBJECT_VALUE:
				case self::STATE_ARRAY:
					return true;
				default:
					return false;
			}
		}
		

		/**
		 * Outputs the captured value
		 */
		protected function invokeCallback(JsonParser $parser): void {
			
			switch($this->state) {
				case self::STATE_ARRAY:
					call_user_func($this->handler, $parser, $this->arrayIndex);
					break;
					
				case self::STATE_OBJECT_VALUE:
					call_user_func($this->handler, $parser, $this->objectKey);
					break;
					
			}
			
		}

	}
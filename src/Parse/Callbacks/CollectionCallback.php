<?php


	namespace MehrIt\EasyJson\Parse\Callbacks;


	use MehrIt\EasyJson\Parse\JsonParser;

	class CollectionCallback extends AbstractCallback
	{
		/**
		 * @var callable
		 */
		protected $handler;

		/**
		 * Creates a new instance
		 * @param array $path The path 
		 * @param callable $handler The handler
		 */
		public function __construct(array $path, callable $handler) {
			
			parent::__construct($path);
			
			$this->handler = $handler;
		}


		/**
		 * @inheritDoc
		 */
		public function captureNested(): bool {
			return false;
		}

		/**
		 * @inheritDoc
		 */
		public function invoke(JsonParser $parser, int $elementType, $elementValue) {
			switch ($elementType) {
				case JsonParser::ELEMENT_OBJECT_START:
				case JsonParser::ELEMENT_ARRAY_START:
					call_user_func($this->handler, $parser);
					
			}
		}

	}
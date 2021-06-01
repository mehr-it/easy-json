<?php


	namespace MehrIt\EasyJson\Parse;


	interface JsonParserCallback
	{
		/**
		 * Gets the path to call this callback for. Empty if to call for root level elements
		 * @return string[]|null The path to call this callback for or null
		 */
		public function path(): array;

		/**
		 * Returns if the callback wants to be invoked for nested elements
		 * @return bool True if the callback wants to be invoked for nested elements. Else false.
		 */
		public function captureNested(): bool;


		/**
		 * Called to invoke the parser callback
		 * @param JsonParser $parser The parser instance
		 * @param int $elementType The element type
		 * @param mixed $elementValue The element value
		 */
		public function invoke(JsonParser $parser, int $elementType, $elementValue);
		
	}
<?php

	namespace MehrIt\EasyJson\Build;

	/**
	 * Represents a fragment of a JSON document
	 */
	class JsonFragment
	{

		/**
		 * @var string
		 */
		protected $json;

		/**
		 * Creates a new JSON fragment
		 * @param string $json The JSON
		 */
		public function __construct(string $json) {
			$this->json = $json;
		}

		/**
		 * Gets the JSON
		 * @return string The JSON
		 */
		public function getJson(): string {
			return $this->json;
		}
		
	}
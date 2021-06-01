<?php


	namespace MehrIt\EasyJson\Parse\Callbacks;


	use MehrIt\EasyJson\Parse\JsonParserCallback;

	abstract class AbstractCallback implements JsonParserCallback
	{

		/**
		 * @var string[]
		 */
		protected $path;
		

		/**
		 * Creates a new instance
		 * @param string[] $path The path
		 */
		public function __construct(array $path) {
			$this->path = $path;
		}

		/**
		 * @inheritDoc
		 */
		public function path(): array {
			return $this->path;
		}

	}
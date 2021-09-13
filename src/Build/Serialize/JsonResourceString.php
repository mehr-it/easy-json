<?php

	namespace MehrIt\EasyJson\Build\Serialize;

	use InvalidArgumentException;
	use MehrIt\EasyJson\Build\JsonBuilder;
	use MehrIt\EasyJson\Build\JsonFragment;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use RuntimeException;

	class JsonResourceString implements JsonSerializable
	{

		/**
		 * @var resource
		 */
		protected $resource;

		/**
		 * @var int
		 */
		protected $chunkSize;

		/**
		 * @var bool
		 */
		protected $base64;

		/**
		 * Creates a new instance
		 * @param resource $resource The resource
		 * @param bool $base64 True if to encode as Base64
		 * @param int $chunkSize The size of the chunks to read from the resource. Default: 1 MB
		 */
		public function __construct($resource, bool $base64 = false, int $chunkSize = 1048576) {

			if (!is_resource($resource))
				throw new InvalidArgumentException('Expected a resource as first parameter, got ' . gettype($resource));

			$this->resource  = $resource;
			$this->chunkSize = $chunkSize;
			$this->base64    = $base64;
		}

		/**
		 * Gets the resource
		 * @return resource The resource
		 */
		public function getResource() {
			return $this->resource;
		}


		/**
		 * @inheritDoc
		 */
		public function serializeJson(JsonBuilder $builder) {

			$chunkSize = $this->chunkSize;
			$resource  = $this->resource;

			if (!is_resource($resource))
				throw new RuntimeException('The resource was closed meanwhile');


			$builder->write(new JsonFragment('"'));


			// add base64 encode
			if ($this->base64) {
				$filter = stream_filter_append($resource, 'convert.base64-encode', STREAM_FILTER_READ);

				try {
					// append base64 data
					while (($chunk = fread($resource, $chunkSize)) !== false) {

						$builder->write(new JsonFragment($chunk));

						if (feof($resource))
							break;
					}
				}
				finally {
					if (!empty($filter))
						stream_filter_remove($filter);
				}
			}
			else {
				// encode resource content as JSON string
				while (($chunk = fread($resource, $chunkSize)) !== false) {

					$builder->write(new JsonFragment(substr(json_encode($chunk, JSON_THROW_ON_ERROR), 1, -1)));

					if (feof($resource))
						break;
				}
			}

			$builder->write(new JsonFragment('"'));

			return $this;
		}


	}
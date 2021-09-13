<?php

	namespace MehrIt\EasyJson\Build;

	use Closure;
	use InvalidArgumentException;
	use MehrIt\EasyJson\Contracts\JsonSerializable;
	use MehrIt\EasyJson\Exception\JsonException;
	use RuntimeException;

	class JsonBuilder
	{
		
		/**
		 * @var resource
		 */
		protected $fh;

		/**
		 * @var int 
		 */
		protected $bytesWritten = 0;

		/**
		 * @var bool 
		 */
		protected $usingMemory = false;

		/**
		 * Creates a new instance
		 * @param string|resource|null $target The file name or an open resource to write the JSON to. If null is given, the JSON is written to memory and can be obtained using getBuffer(). 
		 */
		public function __construct($target = null) {

			if (is_string($target)) {

				$target = fopen($target, 'w');
				if ($target === false)
					throw new RuntimeException("Failed to open \"{$target}\" for writing.");
			}
			else if ($target === null) {
				$target = fopen('php://memory', 'w+');
				$this->usingMemory = true;
			}

			if (!is_resource($target))
				throw new InvalidArgumentException("Expected filename or open resource");

			$this->fh = $target;
		}

		/**
		 * @inheritDoc
		 */
		public function __destruct() {
			if ($this->usingMemory && is_resource($this->fh))
				fclose($this->fh);
		}


		/**
		 * Returns the number of bytes written so far.
		 * @return int The number fo bytes
		 */
		public function bytesWritten(): int {
			return $this->bytesWritten;
		}

		/**
		 * Gets the JSON when writing to memory. If not writing to memory this method will throw an error.
		 * @return string The JSON
		 */
		public function getBuffer(): string {
			if (!$this->usingMemory)
				throw new RuntimeException('This method is only allowed when writing in memory mode.');
			
			rewind($this->fh);
			
			return stream_get_contents($this->fh);
		}

		/**
		 * Writes the given data as JSON
		 * @param mixed $data The data
		 * @return $this
		 */
		public function write($data): JsonBuilder {
			
			try {
				switch (gettype($data)) {
					case 'boolean':
					case 'integer':
					case 'double':
					case 'string':
						$json = json_encode($data, JSON_THROW_ON_ERROR);
						break;

					case 'NULL':
						$json = 'null';
						break;
						
					case 'array':
						if ($data === [] || array_keys($data) == range(0,  count($data) - 1)) {
							// sequential array
							$this->writeAsArray($data);
						}
						else {
							// assoc array
							$this->writeAsObject($data);
						}
						return $this;

					

					case 'object':

						if ($data instanceof JsonFragment) {
							$json = $data->getJson();
						}
						else if ($data instanceof Closure) {
							$bytesBefore = $this->bytesWritten;
							
							$data($this);

							/** @noinspection PhpConditionAlreadyCheckedInspection */
							if ($bytesBefore === $this->bytesWritten)
								throw new \JsonException('Given closure did not write any data. This is not allowed because it might break the generated JSON.');
							
							return $this;
						}
						else if ($data instanceof JsonSerializable) {

							$bytesBefore = $this->bytesWritten;
							
							$data->serializeJson($this);

							/** @noinspection PhpConditionAlreadyCheckedInspection */
							if ($bytesBefore === $this->bytesWritten)
								throw new \JsonException('Given \JsonSerializable did not write any data. This is not allowed because it might break the generated JSON.');
							
							return $this;
						}
						else if (is_iterable($data)) {
							$this->writeAsArray($data);

							return $this;
						}
						else {
							$json = json_encode($data, JSON_THROW_ON_ERROR);
						}

						break;

					default:
						throw new InvalidArgumentException("Cannot convert value of type \"" . gettype($data) . "\" to JSON.");
				}
			}
			catch(\JsonException $ex) {
				throw new JsonException($ex->getMessage(), $ex->getCode(), $ex);
			}
			
			$this->writeRaw($json);
			
			return $this;
		}

		/**
		 * Writes the given data as object
		 * @param iterable $data The data
		 */
		public function writeAsObject(iterable $data): JsonBuilder {
			$this->writeRaw('{');

			$isFirst = true;
			foreach ($data as $key => $item) {

				if (!$isFirst)
					$this->writeRaw(',');
				else
					$isFirst = false;

				// key and ":"
				try {
					$this->writeRaw(json_encode((string)$key, JSON_THROW_ON_ERROR) . ':');
				}
				catch (\JsonException $ex) {
					throw new JsonException($ex->getMessage(), $ex->getCode(), $ex);
				}

				// value
				$this->write($item);
			}

			$this->writeRaw('}');
			
			return $this;
		}

		/**
		 * Writes the given data as array
		 * @param iterable $data The data
		 */
		public function writeAsArray(iterable $data): JsonBuilder {
			$this->writeRaw('[');

			$isFirst = true;
			foreach ($data as $item) {

				if (!$isFirst)
					$this->writeRaw(',');
				else
					$isFirst = false;

				$this->write($item);
			}

			$this->writeRaw(']');
			
			return $this;
		}

		/**
		 * Writes the given JSON string to the target
		 * @param string $json The JSON string
		 */
		protected function writeRaw(string $json) {
			$res = fwrite($this->fh, $json);
			
			if ($res === false)
				throw new JsonException('Failed to write ' . strlen($json) . ' bytes to target resource.');
			
			$this->bytesWritten += $res;
		}
	}
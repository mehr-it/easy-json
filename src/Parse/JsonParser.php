<?php


	namespace MehrIt\EasyJson\Parse;


	use InvalidArgumentException;
	use MehrIt\EasyJson\Exception\JsonException;
	use MehrIt\EasyJson\Exception\JsonSyntaxException;
	use MehrIt\EasyJson\Parse\Callbacks\ItemsCallback;
	use MehrIt\EasyJson\Parse\Callbacks\ItemValuesCallback;
	use MehrIt\EasyJson\Parse\Callbacks\CollectionCallback;
	use MehrIt\EasyJson\Parse\Callbacks\ValueCallback;
	use MehrIt\EasyJson\Util\Numbers;
	use RuntimeException;

	class JsonParser
	{
		use StringifiesPaths;

		const WHITESPACE_TOKENS = " \r\n\t";

		const CHUNK_SIZE = 1048576;        // 1 MB

		const ELEMENT_OBJECT_START = 1;
		const ELEMENT_OBJECT_END = 2;
		const ELEMENT_ARRAY_START = 3;
		const ELEMENT_ARRAY_END = 4;
		const ELEMENT_STRING = 5;
		const ELEMENT_NUMBER = 6;
		const ELEMENT_BOOLEAN = 7;
		const ELEMENT_NULL = 8;

		const STATE_ROOT = 1;
		const STATE_ARRAY_ITEM = 2;
		const STATE_OBJECT_KEY = 3;
		const STATE_OBJECT_DELIMITER = 4;
		const STATE_OBJECT_VALUE = 5;


		/**
		 * @var resource
		 */
		protected $fh;

		protected $bufferMaxSize = 10485760; // 10MB

		protected $buffer = '';

		protected $numbersAsString = false;


		protected $bytesRead = 0;

		protected $lineOffsets = [0 => 0];

		protected $clearPreviousBufferLineBreaks = false;

		protected $stateStack = [];

		
		protected $anyOutput = false;
		
		protected $depth = 0;

		protected $path = [];

		/**
		 * @var JsonParserCallback[][]
		 */
		protected $callbacks = [[]];


		/**
		 * Creates a new JSON parser instance parsing JSON from given string
		 * @param string $json The JSON
		 * @param string $tempUri The URI for creating a temporary stream
		 * @return JsonParser The parser instance
		 */
		public static function fromString(string $json, string $tempUri = 'php://memory'): JsonParser {

			// we create a temporary stream, write XML to it and rewind
			$res = fopen($tempUri, 'w+');
			if (fwrite($res, $json) === false)
				throw new RuntimeException("Could not write JSON to temporary \"$tempUri\"");
			if (!rewind($res))
				throw new RuntimeException("Could not rewind temporary JSON stream \"$tempUri\"");

			return new static($res);
		}

		/**
		 * Creates a new instance
		 * @param string|resource $source The file name or an open resource to read the JSON from.
		 */
		public function __construct($source) {

			if (is_string($source)) {

				$source = fopen($source, 'r');
				if ($source === false)
					throw new RuntimeException("Failed to open \"{$source}\" for reading.");
			}

			if (!is_resource($source))
				throw new InvalidArgumentException("Expected filename or open resource");

			$this->fh = $source;
		}

		/**
		 * Gets the maximum internal buffer size in bytes
		 * @return int The maximum internal buffer size in bytes
		 */
		public function getBufferMaxSize(): int {
			return $this->bufferMaxSize;
		}

		/**
		 * Sets the maximum internal buffer size. If a scalar value is parsed it must not exceed this size. However this
		 * also protects from malicious documents eating all your memory. Default is 10MB.
		 * @param int $bufferMaxSize The maximum buffer size in bytes.
		 * @return JsonParser
		 */
		public function setBufferMaxSize(int $bufferMaxSize): JsonParser {
			
			if ($bufferMaxSize < self::CHUNK_SIZE)
				throw new InvalidArgumentException('Maximum buffer size must be at least ' . self::CHUNK_SIZE);
			
			$this->bufferMaxSize = $bufferMaxSize;
			
			return $this;
		}

		/**
		 * Gets if numbers are returned as string
		 * @return bool True if returned as string. Else false.
		 */
		public function isNumbersAsString(): bool {
			return $this->numbersAsString;
		}

		/**
		 * Sets if to return numbers as bcmath compatible string. Exponents are always reduced.
		 * @param bool $numbersAsString True if to return numbers as string. Else false.
		 * @return JsonParser
		 */
		public function setNumbersAsString(bool $numbersAsString): JsonParser {
			$this->numbersAsString = $numbersAsString;
			
			return $this;
		}
		
		
		/**
		 * Parses the rest of the document
		 * @return $this
		 */
		public function parse(): JsonParser {
		

			if (empty($this->stateStack))
				$this->stateStack[] = ['state' => self::STATE_ROOT];

			
			while (true) {
				if (!$this->parseElement())
					break;
			}
			
			return $this;
		}

		/**
		 * Consumes any data until the current element has been parsed completely. 
		 * @return $this
		 */
		public function consume(): JsonParser {
			if (empty($this->stateStack))
				$this->stateStack[] = ['state' => self::STATE_ROOT];
			
			$startDepth = $this->depth;

			while (true) {
				
				if (!$this->parseElement()) 
					break;

				if ($this->depth < $startDepth)
					break;
			}
			
			return $this;
		}

		/**
		 * Adds a callback which is invoked when the parser reaches an array or object matching the given path
		 * @param string|string[] $path The path
		 * @param callable $callback The callback to invoke before inner elements are parsed. Receives the parser instance as first argument.
		 * @param string $pathSeparator The path separator when a string is given as path
		 * @return $this
		 */
		public function collection($path, callable $callback, string $pathSeparator = '.'): JsonParser {

			$this->addCallback(new CollectionCallback(
				$this->makeAbsPath($path, $pathSeparator),
				$callback
			));

			return $this;
		}


		/**
		 * Invokes the given callback for each item of the element matching the given path
		 * @param string[]|string $path The path
		 * @param callable $callback The callback to invoke. Receives the parser instance and the item key (for objects) or index (for arrays)
		 * @param string $pathSeparator The path separator when a string is given as path
		 * @return $this
		 */
		public function eachItem($path, callable $callback, string $pathSeparator = '.'): JsonParser {

			$this->addCallback(new ItemsCallback(
				$this->makeAbsPath($path, $pathSeparator),
				$callback
			));

			return $this;
		}

		/**
		 * Invokes the given callback for each item value of the element matching the given path
		 * @param string[]|string $path The path
		 * @param callable $callback The callback to invoke. Receives the value and the key (for objects) or index (for arrays)
		 * @param bool $associative False if to return objects instead of associative arrays
		 * @param string $pathSeparator The path separator when a string is given as path
		 * @return $this
		 */
		public function eachItemValue($path, callable $callback, bool $associative = true, string $pathSeparator = '.'): JsonParser {

			$this->addCallback(new ItemValuesCallback(
				$this->makeAbsPath($path, $pathSeparator),
				$callback,
				$associative
			));

			return $this;
		}

		/**
		 * Collects the items values of the element matching the given path
		 * @param $path string[]|string $path The path
		 * @param array $out Will hold the item values when parsing of the matching element is complete
		 * @param bool $associative
		 * @param string $pathSeparator
		 * @return $this
		 */
		public function collectItemValues($path, &$out, bool $associative = true, string $pathSeparator = '.'): JsonParser {

			$out = [];

			return $this->eachItemValue($path, function ($value, $key) use (&$out) {

				$out[$key] = $value;

			}, $associative, $pathSeparator);

		}


		/**
		 * Parses the value of the element matching the given path
		 * @param string|string[] $path The path
		 * @param mixed $out Will hold the value when parsing of first the element is complete
		 * @param bool $associative False if to return objects instead of associative arrays
		 * @param string $pathSeparator The path separator when a string is given as path
		 * @return $this
		 */
		public function value($path, &$out, bool $associative = true, string $pathSeparator = '.'): JsonParser {
			
			$this->addCallback(new ValueCallback(
				$this->makeAbsPath($path, $pathSeparator),
				function ($value) use (&$out) {
					$out = $value;
				},
				$associative
			));

			return $this;

		}


		/**
		 * Adds a new callback
		 * @param JsonParserCallback $callback The callback
		 */
		protected function addCallback(JsonParserCallback $callback) {

			$currCallbacks = &$this->callbacks[array_key_last($this->callbacks)];

			$key = count($currCallbacks) + 1;
			if ($callback->path() != $this->path)
				$key = -$key;

			$currCallbacks[$key] = $callback;
		}

		/**
		 * Pushes a new path segment
		 * @param string $segment The path segment
		 */
		protected function pushPath(string $segment) {

			$this->path[] = $segment;

			$level = ++$this->depth;

			$newCallbacks = [];
			$i            = 0; 
			foreach (end($this->callbacks) as $currCallback) {

				if ($currCallback->captureNested()) {
					// always keep recursive callbacks

					$newCallbacks[++$i] = $currCallback;
				}
				else {
					$currPath = $currCallback->path();
					
					

					if ((string)($currPath[$level - 1] ?? '') === $segment) {
						// keep callback, when path matches so far

						if (count($currPath) > $level) {
							// the callback matches a deeper path => we indicate that by a negative key
							
							$newCallbacks[-(++$i)] = $currCallback;
						}
						else {
							// the callback is matching the path exactly
							
							$newCallbacks[++$i] = $currCallback;
						}

					}

				}

			}

			$this->callbacks[] = $newCallbacks;
		}

		/**
		 * Pops the the last path segment off
		 * @return string|null The popped segment
		 */
		protected function popPath(): ?string {

			--$this->depth;

			array_pop($this->callbacks);
			
			
			return array_pop($this->path);
			
		}


		/**
		 * Outputs the given element
		 * @param int $element The element type
		 * @param mixed $value The value
		 */
		protected function out(int $element, $value = null) {


			$this->anyOutput = true;

			$cb = $this->callbacks[array_key_last($this->callbacks)];

			foreach ($cb as $key => $currCallback) {

				// Callbacks which should be invoked for the current level have a positive array key.
				// The callbacks which are just carried for deeper levels have a negative key and must
				// not be invoked.
				if ($key > 0) {
					$currCallback->invoke($this, $element, $value);
				}

			}

		}

		/**
		 * Parses an element (object, array or scalar)
		 */
		protected function parseElement(): bool {

			// consume whitespace
			[$ws, $next] = $this->readOnly(self::WHITESPACE_TOKENS, true);

			$stateData = &$this->stateStack[array_key_last($this->stateStack)];
			$state = $stateData['state'];

			// check for any additional content after root and throw error
			if ($state === self::STATE_ROOT && $this->anyOutput) {
				if ($next !== false && $next !== null)
					$this->throwSyntaxException('Unexpected content after JSON root element', $ws . $next);
			}
			

			switch ($next) {

				case '{':
					// start of object

					switch ($state) {
						case self::STATE_OBJECT_KEY:
							$this->throwSyntaxException('Expected object key', $next);
							break;

						case self::STATE_OBJECT_DELIMITER:
							$this->throwSyntaxException('Expected ":"', $next);
							break;

						/** @noinspection PhpMissingBreakStatementInspection */
						case self::STATE_ARRAY_ITEM:
							// remove previous array index from path (if any) and add current
							if ($stateData['index'] > -1)
								$this->popPath();
							$this->pushPath(++$stateData['index']);
							
							
						default:
							$stateData['expectNext'] = false;

							$this->stateStack[] = ['state' => self::STATE_OBJECT_KEY];
							
							$this->out(self::ELEMENT_OBJECT_START);
					}

					break;

				case '[':
					
					
					// start of array
					switch ($state) {
						case self::STATE_OBJECT_KEY:
							$this->throwSyntaxException('Expected object key', $next);
							break;

						case self::STATE_OBJECT_DELIMITER:
							$this->throwSyntaxException('Expected ":"', $next);
							break;

						/** @noinspection PhpMissingBreakStatementInspection */ 
						case self::STATE_ARRAY_ITEM:
							// remove previous array index from path (if any) and add current
							if ($stateData['index'] > -1)
								$this->popPath();
							$this->pushPath(++$stateData['index']);

						default:
							$stateData['expectNext'] = false;
							
							$this->stateStack[] = ['state' => self::STATE_ARRAY_ITEM, 'index' => -1];
							
							$this->out(self::ELEMENT_ARRAY_START);
							
					}


					break;

				case ':':

					// a colon is only expected after an object key
					switch ($state) {
						case self::STATE_OBJECT_DELIMITER:
							array_pop($this->stateStack);
							$this->stateStack[] = ['state' => self::STATE_OBJECT_VALUE, 'expectNext' => true];
							break;

						default:

							$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
					}


					break;

				case ',':

					// a separator is expected after object value or array item
					switch ($state) {
						case self::STATE_OBJECT_VALUE:
							$this->popPath();

							// expect a new object key
							array_pop($this->stateStack);
							$this->stateStack[] = ['state' => self::STATE_OBJECT_KEY, 'expectNext' => true];
							break;

						case self::STATE_ARRAY_ITEM:
							if ($stateData['expectNext'] ?? null) {
								$this->throwSyntaxException("Expected next item, but none exists.", $next);
							}

							// expect next element
							$stateData['expectNext'] = true;
							break;

						default:
							$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
					}
					break;

				case '}':
					switch ($state) {
						
						case self::STATE_OBJECT_VALUE:
								
							if (!($stateData['expectNext'] ?? null)) {
								$this->popPath();

								// pop state
								array_pop($this->stateStack);

								// array end
								$this->out(self::ELEMENT_OBJECT_END);
								break;
							}
							else {
								$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
							}
							break;
							
						case self::STATE_OBJECT_KEY:
							// end of array => stop parsing
							if (!($stateData['expectNext'] ?? null)) {

								// pop state
								array_pop($this->stateStack);

								// array end
								$this->out(self::ELEMENT_OBJECT_END);
								break;
							}
							else {
								$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
							}
							break;

						default:
							$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
					}
					break;

				case ']':

					switch ($state) {
						/** @noinspection PhpMissingBreakStatementInspection */
						case self::STATE_ARRAY_ITEM:
							
							// end of array => stop parsing
							if (!($stateData['expectNext'] ?? null)) {
								// remove previous array index from path (if any) 
								if ($stateData['index'] > -1)
									$this->popPath();

								// pop state
								array_pop($this->stateStack);

								// array end
								$this->out(self::ELEMENT_ARRAY_END);
								break;
							}

						default:
							$this->throwSyntaxException("Unexpected \"{$next}\"", $next);
					}

					break;

				case false:
					// end of file
					
					// are we at root level again => otherwise this is unexpected
					if ($state != self::STATE_ROOT || !$this->anyOutput)
						$this->throwUnexpectedEndOfFileException();
					
					return false;

				default:
					$stateData['expectNext'] = false;

					switch ($state) {
						case self::STATE_OBJECT_KEY:
							if ($next !== '"')
								$this->throwSyntaxException('Expected object key', $next);

							// parse key
							$currKey = $this->parseString();


							$this->out(self::ELEMENT_STRING, $currKey);
							$this->pushPath($currKey);

							// expect colon
							array_pop($this->stateStack);
							$this->stateStack[] = ['state' => self::STATE_OBJECT_DELIMITER];


							break;

						case self::STATE_OBJECT_DELIMITER:
							$this->throwSyntaxException('Expected ":"', $next);
							break;

						/** @noinspection PhpMissingBreakStatementInspection */
						case self::STATE_ARRAY_ITEM:
							// remove previous array index from path (if any) and add current
							if ($stateData['index'] > -1)
								$this->popPath();

							$this->pushPath(++$stateData['index']);
							

						default:
							$this->parseScalar($next);
					}


			}

			// indicate that linebreaks of previous buffers are not needed anymore
			// because all corresponding data has been processed without error 
			$this->clearPreviousBufferLineBreaks = true;

			return true;
		}

		/**
		 * Parses a scalar value
		 * @param string $firstChar The first char of the scalar which has already been parsed
		 */
		protected function parseScalar(string $firstChar): void {


			switch ($firstChar) {

				case '"':
					// start of string
					$this->out(self::ELEMENT_STRING, $this->parseString());
					break;

				default:
					// start of number, boolean or null

					[$token,] = $this->readOnly('0123456789eE+-.trufalsnll', false);

					// the first char belongs to the value
					$token = "{$firstChar}{$token}";

					switch ($token) {
						case 'true':
							$this->out(self::ELEMENT_BOOLEAN, true);
							break;

						case 'false':
							$this->out(self::ELEMENT_BOOLEAN, false);
							break;

						case 'null':
							$this->out(self::ELEMENT_NULL, null);
							break;


						default:

							if ($this->numbersAsString) {

								// The number should be returned as string. Anyhow we reduce the exponent. 								
								try {
									$number = Numbers::reduceExponent($token);
								}
								catch (InvalidArgumentException $ex) {
									$this->throwSyntaxException('Invalid number', $token);
								}
							}
							else {

								// use JSON decode to parse the number to a native type
								$number = @json_decode($token);
								if (json_last_error() !== JSON_ERROR_NONE) {
									$this->throwSyntaxException('Invalid number', $token);
								}
							}

							$this->out(self::ELEMENT_NUMBER, $number);

							break;

					}
			}
		}

		/**
		 * Parses a string at the current position. The starting " must already have been consumed.
		 * @return string The string
		 */
		protected function parseString(): string {

			$str = '';

			$escaped = false;
			while (true) {
				[$chunk, $token] = $this->readUntil("\"\\");

				// reset escaped, if there is another char within
				if ($chunk !== '')
					$escaped = false;

				$str .= $chunk;
				switch ($token) {

					case '"':
						if ($escaped)
							$str .= "\"";
						else
							break 2;

						break;

					case '\\':
						$str .= '\\';

						if (!$escaped)
							$escaped = true;
						break;

					case false:
						$this->throwUnexpectedEndOfFileException();
				}
			}

			// Use JSON decode to unescape the string. This is much faster and simpler than implementing our own
			// logic here
			$result = @json_decode("\"{$str}\"");
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->throwSyntaxException('Invalid JSON string', "\"{$str}\"");
			}

			return $result;
		}

		/**
		 * Reads until another token than the specified tokens is found
		 * @param string $tokens The tokens
		 * @param bool $readNext True if to also read the following token
		 * @return string[]|false[]|null[] The string which consists only of the given tokens. If no other token was found until EOF, the second return value will be false.
		 */
		protected function readOnly(string $tokens, bool $readNext) {

			$consumed = '';

			while (($chunk = $this->read()) !== false) {

				$bytesMatching = strspn($chunk, $tokens);


				if ($bytesMatching === strlen($chunk)) {
					// only tokens in chunk

					$consumed .= $chunk;

					// reset buffer, so we can read the next chunk
					$this->buffer = '';
				}
				else {
					// other token in chunk

					if ($readNext) {

						// put the data after the tokens and next char back to the buffer
						$this->buffer = substr($chunk, $bytesMatching + 1);


						// return tokens and the next char
						return [
							$consumed . substr($chunk, 0, $bytesMatching),
							$chunk[$bytesMatching]
						];
					}
					else {
						// put the data after the tokens back to the buffer
						$this->buffer = substr($chunk, $bytesMatching);


						// return the data
						return [
							$consumed . substr($chunk, 0, $bytesMatching),
							null
						];
					}
				}
			}

			return [$consumed, false];
		}

		/**
		 * Reads until any of the given tokens is found.
		 * @param string $tokens The tokens
		 * @return string[]|false[] The string before the token and the token which was found. If no token was found until EOF, the second return value will be false.
		 */
		protected function readUntil(string $tokens): array {

			$consumed = '';

			while (($chunk = $this->read()) !== false) {

				$bytesBeforeToken = strcspn($chunk, $tokens);

				if ($bytesBeforeToken === strlen($chunk)) {
					// no token in chunk

					$consumed .= $chunk;

					if (strlen($consumed) > $this->bufferMaxSize)
						throw new JsonException("JSON parser buffer size of {$this->bufferMaxSize} bytes exhausted. Try to increase the buffer size of parsing large string fields.");

					// reset buffer, so we can read the next chunk
					$this->buffer = '';
				}
				else {
					// token in chunk

					// put the data after the token back to the buffer
					$this->buffer = substr($chunk, $bytesBeforeToken + 1);

					// return the data until the token and the found token
					return [
						$consumed . substr($chunk, 0, $bytesBeforeToken),
						$chunk[$bytesBeforeToken]
					];
				}

			}

			return [$consumed, false];
		}

		/**
		 * Reads data from the internal buffer. If the buffer is empty, it will be filled from source
		 * @return false|string The data or false on EOF
		 */
		protected function read() {

			$buffer = $this->buffer;
			if ($buffer !== '')
				return $buffer;

			if (!is_resource($this->fh))
				throw new JsonException("JSON source has been closed");

			$data = fread($this->fh, self::CHUNK_SIZE);
			if ($data === false) {
				if (feof($this->fh))
					return false;
				else
					throw new JsonException("Failed to read from JSON source after {$this->bytesRead} bytes");
			}

			$dataLength = strlen($data);
			if ($dataLength === 0 && feof($this->fh))
				return false;


			// clear line break positions of previous buffers if not needed anymore
			if ($this->clearPreviousBufferLineBreaks) {

				// keep only the last line break
				array_splice($this->lineOffsets, -1);

				$this->clearPreviousBufferLineBreaks = false;
			}


			// remember the positions where new lines start
			$lastLine = array_key_last($this->lineOffsets) ?: 0;
			$i        = 0;
			while (($lineEndPos = strpos($data, "\n", $i + 1)) !== false) {
				$this->lineOffsets[++$lastLine] = $lineEndPos + 1;

				$i = $lineEndPos;
			}

			$this->bytesRead += $dataLength;


			return ($this->buffer = $data);
		}

		protected function throwUnexpectedEndOfFileException() {

			$line   = array_key_last($this->lineOffsets) ?: 0;
			$column = $this->bytesRead - (reset($this->lineOffsets) ?: 0);

			throw new JsonSyntaxException("Unexpected end of JSON document at line {$line}:{$column}", $line, $column);
		}

		protected function throwSyntaxException(string $message, string $fragment) {
			$fragment       .= $this->buffer;
			$fragmentOffset = $this->bytesRead - strlen($fragment);

			// calculate the position of the fragment
			$line   = -1;
			$column = -1;
			foreach ($this->lineOffsets as $currLineNumber => $currOffset) {

				if (($this->lineOffsets[$currLineNumber + 1] ?? PHP_INT_MAX) > $fragmentOffset) {
					$line   = $currLineNumber;
					$column = $fragmentOffset - $currOffset;
					break;
				}
			}

			throw new JsonSyntaxException(
				strlen($fragment) === 0 ?
					"JSON syntax error at line {$line}:{$column}: {$message}" :
					"JSON syntax error at line {$line}:{$column} near \"" . substr($fragment, 0, 15) . "\": {$message}",
				$line,
				$column
			);
		}

		/**
		 * Builds an absolute path
		 * @param string|string[] $path The relative path
		 * @param string $pathSeparator The path separator
		 * @return string[] The absolute path
		 */
		protected function makeAbsPath($path, string $pathSeparator): array {
			if (is_string($path)) {
				if ($path === '')
					$path = [];
				else
					$path = explode($pathSeparator, $path);
			}
			else if (!is_array($path)) {
				throw new InvalidArgumentException('Expected path to be string or array');
			}

			if (count($path) === 0 && $this->anyOutput)
				throw new InvalidArgumentException('Empty paths are not allowed when parsing has already been started.');

			return array_merge($this->path, $path);
		}
	}
<?php


	namespace MehrIt\EasyJson\Exception;


	use Throwable;

	class JsonSyntaxException extends JsonException
	{
		/**
		 * @var int
		 */
		protected $errorLine;

		/**
		 * @var int
		 */
		protected $errorColumn;


		public function __construct($message, int $errorLine = -1, int $errorColumn = -1, $code = 0, Throwable $previous = null) {
			parent::__construct($message, $code, $previous);

			$this->errorLine   = $errorLine;
			$this->errorColumn = $errorColumn;
		}

		/**
		 * Gets the line number where the error occurred. First line has number 0.
		 * @return int The line number where the error occurred
		 */
		public function getErrorLine(): int {
			return $this->errorLine;
		}

		/**
		 * Gets the column number where the error occurred. First column has number 0.
		 * @return int The column number where the error occurred
		 */
		public function getErrorColumn(): int {
			return $this->errorColumn;
		}

	}
<?php


	namespace MehrIt\EasyJson\Util;


	use InvalidArgumentException;

	class Numbers
	{

		/**
		 * Reduces the exponent (if any) from the given number
		 * @param string $number The number
		 * @return string The exponent
		 */
		public static function reduceExponent(string $number): string {

			if (!preg_match('/^(?P<sign>-)?(?P<digits>[0-9]+)(\.(?P<decimals>[0-9]+))?([eE](?P<exp>[\-+]?[0-9]+))?$/', $number, $matches))
				throw new InvalidArgumentException('The given argument is not a valid number.');


			if ($exp = (int)($matches['exp'] ?? 0)) {

				// convert number to integer with exponent
				$decimals = rtrim($matches['decimals'], '0');
				$int      = $matches['digits'] . $decimals;
				$intExp   = -strlen($decimals);
								

				if ($exp < 0) {
					// calculate new exponent
					$intExp = min($intExp + $exp, 0);
					
					// prepend required zeros
					$int = str_repeat('0', max(abs($intExp) - strlen($int) + 1, 0)) . $int;
				}
				else {
					// append required zeros
					$int = $int . str_repeat('0', max($intExp + $exp, 0));
					
					// calculate new exponent
					$intExp = min($intExp + $exp, 0);
				}
				
				
				$matches['digits']   = ltrim(substr($int, 0, strlen($int) + $intExp ), '0') ?: '0';
				$matches['decimals'] = rtrim(substr($int, strlen($int) + $intExp), '0') ?: '';
			}
			else {
				$matches['digits'] = ltrim($matches['digits'] ?? '', '0') ?: '0';
				$matches['decimals'] = rtrim($matches['decimals'] ?? '', '0') ?: '';
			}


			return rtrim(($matches['sign'] ?? '') . $matches['digits'] . '.' . ($matches['decimals'] ?? ''), '.');
		}

	}
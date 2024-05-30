<?php
namespace Web3;

use RuntimeException;
use InvalidArgumentException;
use stdClass;
use kornrunner\Keccak;
use phpseclib\Math\BigInteger as BigNumber;

class Utils
{
    const SHA3_NULL_HASH = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';

    const UNITS = [
        'noether' => '0',
        'wei' => '1',
        'kwei' => '1000',
        'Kwei' => '1000',
        'babbage' => '1000',
        'femtoether' => '1000',
        'mwei' => '1000000',
        'Mwei' => '1000000',
        'lovelace' => '1000000',
        'picoether' => '1000000',
        'gwei' => '1000000000',
        'Gwei' => '1000000000',
        'shannon' => '1000000000',
        'nanoether' => '1000000000',
        'nano' => '1000000000',
        'szabo' => '1000000000000',
        'microether' => '1000000000000',
        'micro' => '1000000000000',
        'finney' => '1000000000000000',
        'milliether' => '1000000000000000',
        'milli' => '1000000000000000',
        'ether' => '1000000000000000000',
        'kether' => '1000000000000000000000',
        'grand' => '1000000000000000000000',
        'mether' => '1000000000000000000000000',
        'gether' => '1000000000000000000000000000',
        'tether' => '1000000000000000000000000000000'
    ];

    /**
     * construct
     *
     * @return void
     */
    // public function __construct() {}

    /**
     * @param string|int|BigNumber $value
     * @param bool $isPrefix
     * @return string
     */
    public static function toHex($value, $isPrefix=false)
    {
        if (is_int($value) || is_float($value)) {
            // turn to hex number
            $bn = self::toBn($value);
            $hex = $bn->toHex(true);
            $hex = preg_replace('/^0+(?!$)/', '', $hex);
        } elseif (is_string($value)) {
            $hex = implode('', unpack('H*', $value));
        } elseif ($value instanceof BigNumber) {
            $hex = $value->toHex(true);
            $hex = preg_replace('/^0+(?!$)/', '', $hex);
        } else {
            throw new InvalidArgumentException('El valor proporcionado a la función toHex no es compatible.');
        }
        if ($isPrefix) {
            return '0x' . $hex;
        }
        return $hex;
    }

    /**
     * hexToBin
     *
     * @param string
     * @return string
     */
    public static function hexToBin($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('El valor proporcionado a la función hexToBin debe ser una cadena.');
        }
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            $value = str_replace('0x', '', $value, $count);
            // evitar el sufijo 0
            if (strlen($value) % 2 > 0) {
                $value = '0' . $value;
            }
        }
        return pack('H*', $value);
    }

    /**
     * isZeroPrefixed
     *
     * @param string
     * @return bool
     */
    public static function isZeroPrefixed($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('El valor proporcionado a la función isZeroPrefixed debe ser una cadena.');
        }
        return (strpos($value, '0x') === 0);
    }

    /**
     * stripZero
     *
     * @param string $value
     * @return string
     */
    public static function stripZero($value)
    {
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            return str_replace('0x', '', $value, $count);
        }
        return $value;
    }

    /**
     * isNegative
     *
     * @param string
     * @return bool
     */
    public static function isNegative($value)
{
    if (!is_string($value)) {
        throw new InvalidArgumentException('El valor para la función isNegative debe ser una cadena.');
    }
    return (strpos($value, '-') === 0);
}

/**
 * isAddress
 *
 * @param string $value
 * @return bool
 */
public static function isAddress($value)
{
    if (!is_string($value)) {
        throw new InvalidArgumentException('El valor para la función isAddress debe ser una cadena.');
    }
    if (preg_match('/^(0x|0X)?[a-f0-9A-F]{40}$/', $value) !== 1) {
        return false;
    } elseif (preg_match('/^(0x|0X)?[a-f0-9]{40}$/', $value) === 1 || preg_match('/^(0x|0X)?[A-F0-9]{40}$/', $value) === 1) {
        return true;
    }
    return self::isAddressChecksum($value);
}

/**
 * isAddressChecksum
 *
 * @param string $value
 * @return bool
 */
public static function isAddressChecksum($value)
{
    if (!is_string($value)) {
        throw new InvalidArgumentException('El valor para la función isAddressChecksum debe ser una cadena.');
    }
    $value = self::stripZero($value);
    $hash = self::stripZero(self::sha3(mb_strtolower($value)));

    for ($i = 0; $i < 40; $i++) {
        if (
            (intval($hash[$i], 16) > 7 && mb_strtoupper($value[$i]) !== $value[$i]) ||
            (intval($hash[$i], 16) <= 7 && mb_strtolower($value[$i]) !== $value[$i])
        ) {
            return false;
        }
    }
    return true;
}

/**
 * toChecksumAddress
 *
 * @param string $value
 * @return string
 */
public static function toChecksumAddress($value)
{
    if (!is_string($value)) {
        throw new InvalidArgumentException('El valor para la función toChecksumAddress debe ser una cadena.');
    }
    $value = self::stripZero(strtolower($value));
    $hash = self::stripZero(self::sha3($value));
    $ret = '0x';

    for ($i = 0; $i < 40; $i++) {
        if (intval($hash[$i], 16) >= 8) {
            $ret .= strtoupper($value[$i]);
        } else {
            $ret .= $value[$i];
        }
    }
    return $ret;
}

/**
 * isHex
 *
 * @param string $value
 * @return bool
 */
public static function isHex($value)
{
    return (is_string($value) && preg_match('/^(0x)?[a-f0-9]*$/', $value) === 1);
}

/**
 * sha3
 * keccak256
 *
 * @param string $value
 * @return string
 */
public static function sha3($value)
{
    if (!is_string($value)) {
        throw new InvalidArgumentException('El valor para la función sha3 debe ser una cadena.');
    }
    if (strpos($value, '0x') === 0) {
        $value = self::hexToBin($value);
    }
    $hash = Keccak::hash($value, 256);

    if ($hash === self::SHA3_NULL_HASH) {
        return null;
    }
    return '0x' . $hash;
}

/**
 * toString
 *
 * @param mixed $value
 * @return string
 */
public static function toString($value)
{
    $value = (string) $value;

    return $value;
}


 /**
 * toWei
 * Convierte un número de una unidad a wei.
 * Por ejemplo:
 * $wei = Utils::toWei('1', 'kwei');
 * $wei->toString(); // 1000
 *
 * @param BigNumber|string $number
 * @param string $unit
 * @return \phpseclib\Math\BigInteger
 */
public static function toWei($number, $unit)
{
    if (!is_string($number) && !($number instanceof BigNumber)) {
        throw new InvalidArgumentException('El número para toWei debe ser una cadena o un objeto BigNumber.');
    }
    $bn = self::toBn($number);

    if (!is_string($unit)) {
        throw new InvalidArgumentException('La unidad para toWei debe ser una cadena.');
    }
    if (!isset(self::UNITS[$unit])) {
        throw new InvalidArgumentException('toWei no admite la unidad ' . $unit . '.');
    }
    $bnt = new BigNumber(self::UNITS[$unit]);

    if (is_array($bn)) {
        // Número fraccionario
        list($entero, $fraccion, $longitudFraccion, $negativo) = $bn;

        if ($longitudFraccion > strlen(self::UNITS[$unit])) {
            throw new InvalidArgumentException('La parte fraccionaria de toWei está fuera de límite.');
        }
        $entero = $entero->multiply($bnt);

        // No hay función pow en phpseclib 2.0, solo se encuentra en dev-master
        // Tal vez implementemos nuestra propia clase BigInteger en el futuro
        // Ver BigInteger 2.0: https://github.com/phpseclib/phpseclib/blob/2.0/phpseclib/Math/BigInteger.php
        // Ver BigInteger dev-master: https://github.com/phpseclib/phpseclib/blob/master/phpseclib/Math/BigInteger.php#L700
        // $base = (new BigNumber(10))->pow(new BigNumber($longitudFraccion));

        // Cambiamos el parámetro especial global de phpseclib, cambiará en el futuro
        switch (MATH_BIGINTEGER_MODE) {
            case $entero::MODE_GMP:
                static $dos;
                $basePotencia = gmp_pow(gmp_init(10), (int) $longitudFraccion);
                break;
            case $entero::MODE_BCMATH:
                $basePotencia = bcpow('10', (string) $longitudFraccion, 0);
                break;
            default:
                $basePotencia = pow(10, (int) $longitudFraccion);
                break;
        }
        $base = new BigNumber($basePotencia);
        $fraccion = $fraccion->multiply($bnt)->divide($base)[0];

        if ($negativo !== false) {
            return $entero->add($fraccion)->multiply($negativo);
        }
        return $entero->add($fraccion);
    }

    return $bn->multiply($bnt);
}

/**
 * toEther
 * Convierte un número de una unidad a ether.
 * Por ejemplo:
 * list($bnq, $bnr) = Utils::toEther('1', 'kether');
 * $bnq->toString(); // 1000
 *
 * @param BigNumber|string|int $number
 * @param string $unit
 * @return array
 */
public static function toEther($number, $unit)
{
    // if ($unit === 'ether') {
    //     throw new InvalidArgumentException('Por favor, utiliza otra unidad.');
    // }
    $wei = self::toWei($number, $unit);
    $bnt = new BigNumber(self::UNITS['ether']);

    return $wei->divide($bnt);
}

/**
 * fromWei
 * Convierte un número de wei a una unidad.
 * Por ejemplo:
 * list($bnq, $bnr) = Utils::fromWei('1000', 'kwei');
 * $bnq->toString(); // 1
 *
 * @param BigNumber|string|int $number
 * @param string $unit
 * @return \phpseclib\Math\BigInteger
 */
public static function fromWei($number, $unit)
{
    $bn = self::toBn($number);

    if (!is_string($unit)) {
        throw new InvalidArgumentException('La unidad para fromWei debe ser una cadena.');
    }
    if (!isset(self::UNITS[$unit])) {
        throw new InvalidArgumentException('fromWei no admite la unidad ' . $unit . '.');
    }
    $bnt = new BigNumber(self::UNITS[$unit]);

    return $bn->divide($bnt);
}

/**
 * jsonMethodToString
 *
 * @param stdClass|array $json
 * @return string
 */
public static function jsonMethodToString($json)
{
    if ($json instanceof stdClass) {
        // Una forma de cambiar todo el objeto stdClass JSON a un tipo de array
        // $jsonString = json_encode($json);

        // if (JSON_ERROR_NONE !== json_last_error()) {
        //     throw new InvalidArgumentException('json_decode error: ' . json_last_error_msg());
        // }
        // $json = json_decode($jsonString, true);

        // Otra forma de cambiar todo el JSON a un tipo de array, pero se necesita la profundidad
        // $json = self::jsonToArray($json, $depth)

        // Otra forma de cambiar el JSON a un tipo de array, pero no el objeto stdClass completo
        $json = (array) $json;
        $typeName = [];

        foreach ($json['inputs'] as $param) {
            if (isset($param->type)) {
                $typeName[] = $param->type;
            }
        }
        return $json['name'] . '(' . implode(',', $typeName) . ')';
    } elseif (!is_array($json)) {
        throw new InvalidArgumentException('jsonMethodToString: el JSON debe ser un array o un stdClass.');
    }
    if (isset($json['name']) && strpos($json['name'], '(') > 0) {
        return $json['name'];
    }
    $typeName = [];

    foreach ($json['inputs'] as $param) {
        if (isset($param['type'])) {
            $typeName[] = $param['type'];
        }
    }
    return $json['name'] . '(' . implode(',', $typeName) . ')';
}

/**
 * jsonToArray
 *
 * @param stdClass|array $json
 * @return array
 */
public static function jsonToArray($json)
{
    if ($json instanceof stdClass) {
        $json = (array) $json;
        $typeName = [];

        foreach ($json as $key => $param) {
            if (is_array($param)) {
                foreach ($param as $subKey => $subParam) {
                    $json[$key][$subKey] = self::jsonToArray($subParam);
                }
            } elseif ($param instanceof stdClass) {
                $json[$key] = self::jsonToArray($param);
            }
        }
    } elseif (is_array($json)) {
        foreach ($json as $key => $param) {
            if (is_array($param)) {
                foreach ($param as $subKey => $subParam) {
                    $json[$key][$subKey] = self::jsonToArray($subParam);
                }
            } elseif ($param instanceof stdClass) {
                $json[$key] = self::jsonToArray($param);
            }
        }
    }
    return $json;
}
/**
 * toBn
 * Convierte un número o una cadena de números a un objeto bignumber.
 *
 * @param BigNumber|string|int $number
 * @return array|\phpseclib\Math\BigInteger
 */
public static function toBn($number)
{
    if ($number instanceof BigNumber) {
        $bn = $number;
    } elseif (is_int($number)) {
        $bn = new BigNumber($number);
    } elseif (is_numeric($number)) {
        $number = (string) $number;

        if (self::isNegative($number)) {
            $count = 1;
            $number = str_replace('-', '', $number, $count);
            $negative1 = new BigNumber(-1);
        }
        if (strpos($number, '.') > 0) {
            $comps = explode('.', $number);

            if (count($comps) > 2) {
                throw new InvalidArgumentException('El número para toBn debe ser válido.');
            }
            $whole = $comps[0];
            $fraction = $comps[1];

            return [
                new BigNumber($whole),
                new BigNumber($fraction),
                strlen($comps[1]),
                isset($negative1) ? $negative1 : false
            ];
        } else {
            $bn = new BigNumber($number);
        }
        if (isset($negative1)) {
            $bn = $bn->multiply($negative1);
        }
    } elseif (is_string($number)) {
        $number = mb_strtolower($number);

        if (self::isNegative($number)) {
            $count = 1;
            $number = str_replace('-', '', $number, $count);
            $negative1 = new BigNumber(-1);
        }
        if (self::isZeroPrefixed($number) || preg_match('/^[0-9a-f]+$/i', $number) === 1) {
            $number = self::stripZero($number);
            $bn = new BigNumber($number, 16);
        } elseif (empty($number)) {
            $bn = new BigNumber(0);
        } else {
            throw new InvalidArgumentException('El número para toBn debe ser una cadena hexadecimal válida.');
        }
        if (isset($negative1)) {
            $bn = $bn->multiply($negative1);
        }
    } else {
        throw new InvalidArgumentException('El número para toBn debe ser BigNumber, cadena o entero.');
    }
    return $bn;
}

/**
 * hexToNumber
 *
 * @param string $hexNumber
 * @return int
 */
public static function hexToNumber($hexNumber)
{
    if (!self::isZeroPrefixed($hexNumber)) {
        $hexNumber = '0x' . $hexNumber;
    }
    return intval(self::toBn($hexNumber)->toString());
}
}
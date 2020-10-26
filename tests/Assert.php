<?php

namespace Bugsnag\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PhpUnitAssert;

/**
 * This class holds assertions that were removed/renamed/changed in various
 * PHPUnit versions, so that our test suite can be compatible with as many
 * versions as possible.
 */
final class Assert
{
    /**
     * A PHPUnit 4, 7 & 9 compatible version of 'assertRegExp' and its replacement,
     * 'assertMatchesRegularExpression'.
     *
     * This is necessary to avoid warnings - PHPUnit 9 deprecated 'assertRegExp'
     * in favour of 'assertMatchesRegularExpression' and outputs a warning if
     * the former is used
     *
     * @param string $regex
     * @param string $value
     *
     * @return void
     */
    public static function matchesRegularExpression($regex, $value)
    {
        if (method_exists(PhpUnitAssert::class, 'assertMatchesRegularExpression')) {
            PhpUnitAssert::assertMatchesRegularExpression($regex, $value);

            return;
        }

        PhpUnitAssert::assertRegExp($regex, $value);
    }

    /**
     * A replacement for 'assertInternalType', which was removed in PHPUnit 9.
     *
     * @param string $type
     * @param mixed $value
     *
     * @return void
     */
    public static function isType($type, $value)
    {
        if (method_exists(PhpUnitAssert::class, 'assertInternalType')) {
            PhpUnitAssert::assertInternalType($type, $value);

            return;
        }

        $typeToAssertion = [
            'array' => [PhpUnitAssert::class, 'assertIsArray'],
            'bool' => [PhpUnitAssert::class, 'assertIsBool'],
            'callable' => [PhpUnitAssert::class, 'assertIsCallable'],
            'float' => [PhpUnitAssert::class, 'assertIsFloat'],
            'int' => [PhpUnitAssert::class, 'assertIsInt'],
            'iterable' => [PhpUnitAssert::class, 'assertIsIterable'],
            'numeric' => [PhpUnitAssert::class, 'assertIsNumeric'],
            'object' => [PhpUnitAssert::class, 'assertIsObject'],
            'resource' => [PhpUnitAssert::class, 'assertIsResource'],
            'scalar' => [PhpUnitAssert::class, 'assertIsScalar'],
            'string' => [PhpUnitAssert::class, 'assertIsString'],
        ];

        if (!isset($typeToAssertion[$type])) {
            throw new InvalidArgumentException("Unknown type '{$type}' given");
        }

        $typeToAssertion[$type]($value);
    }

    /**
     * @param string $format
     * @param string $dateString
     *
     * @return void
     */
    public static function matchesDateFormat($format, $dateString)
    {
        $date = new DateTimeImmutable($dateString);

        PhpUnitAssert::assertSame(
            $dateString,
            $date->format($format),
            "Date '{$dateString}' did not match format '{$format}'"
        );
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public static function matchesBugsnagDateFormat($date)
    {
        // The millisecond format specifier ("v") was added in PHP 7.0
        if (PHP_MAJOR_VERSION >= 7) {
            Assert::matchesDateFormat('Y-m-d\TH:i:s.vP', $date);

            return;
        }

        $regex = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})\.\d{3}([+-]\d{2}:\d{2})$/';

        Assert::matchesRegularExpression($regex, $date);

        preg_match($regex, $date, $matches);

        $dateTime = $matches[1];
        $offset = $matches[2];

        Assert::matchesDateFormat('Y-m-d\TH:i:sP', $dateTime.$offset);
    }
}

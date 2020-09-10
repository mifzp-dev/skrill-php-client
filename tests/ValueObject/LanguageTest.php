<?php

declare(strict_types=1);

namespace Skrill\Tests\ValueObject;

use Generator;
use PHPUnit\Framework\TestCase;
use Skrill\ValueObject\Language;
use Skrill\Exception\InvalidLangException;

/**
 * Class LanguageTest.
 */
class LanguageTest extends TestCase
{
    /**
     * @dataProvider successDataProvider
     *
     * @param string $value
     *
     * @throws InvalidLangException
     */
    public function testSuccess(string $value)
    {
        self::assertSame($value, (string)(new Language($value)));
    }

    /**
     * @throws InvalidLangException
     */
    public function testSuccess2()
    {
        self::assertSame('FR', (string)(new Language('FR ')));
    }

    /**
     * @return Generator
     */
    public function successDataProvider(): Generator
    {
        foreach (getSkillSupportsLanguages() as $lang => $title) {
            yield [$lang];
        }
    }

    /**
     * @throws InvalidLangException
     */
    public function testInvalidValue()
    {
        $this->expectException(InvalidLangException::class);
        $this->expectExceptionMessage('Not accepted language by Skrill.');

        new Language('test');
    }
}

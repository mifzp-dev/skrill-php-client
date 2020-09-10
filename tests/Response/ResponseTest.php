<?php

declare(strict_types=1);

namespace Skrill\Tests\Response;

use ReflectionClass;
use ReflectionException;
use Skrill\Response\Response;
use PHPUnit\Framework\TestCase;
use Skrill\Exception\ResponseDataException;

/**
 * Class ResponseTest.
 */
class ResponseTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testIsFinal()
    {
        $method = new ReflectionClass(Response::class);

        self::assertTrue($method->isFinal());
    }

    /**
     * @dataProvider dotNotationDataProvider
     *
     * @param string     $key
     * @param mixed      $expected
     * @param array      $data
     * @param mixed|null $default
     */
    public function testDotNotation($key, $expected, array $data, $default = null)
    {
        self::assertEquals($expected, (new Response($data))->get($key, $default));
    }

    /**
     * @return array
     */
    public function dotNotationDataProvider()
    {
        return [
            'level 1' => [
                'response_text',
                'Site successfully created',
                [
                    'response_text' => 'Site successfully created',
                    'site_id' => 13,
                ],
                null,
            ],
            'level 2' => [
                'periods.current',
                '2012-06-11 2012-06-25',
                [
                    'response_text' => "Information about client's periods.",
                    'periods' => [
                        'current' => '2012-06-11 2012-06-25',
                    ],
                ],
                null,
            ],
            'level 3' => [
                'invoices.cc.USD',
                [
                    'currency' => 'USD',
                    'credits' => 174.8,
                ],
                [
                    'invoices' => [
                        'cc' => [
                            'USD' => [
                                'currency' => 'USD',
                                'credits' => 174.8,
                            ],
                        ],
                    ],
                ],
                null,
            ],
            'level 4' => [
                'invoices.cc.USD.credits',
                174.8,
                [
                    'invoices' => [
                        'cc' => [
                            'USD' => [
                                'currency' => 'USD',
                                'credits' => 174.8,
                            ],
                        ],
                    ],
                ],
                null,
            ],
            'default' => [
                'invoices.cc.USD.credits',
                '---DEFAULT---',
                [
                    'invoices' => [
                        'cc' => [
                            'EUR' => [
                                'currency' => 'USD',
                                'credits' => 174.8,
                            ],
                        ],
                    ],
                ],
                '---DEFAULT---',
            ],
            'empty' => [
                'invoices.cc.USD.credits',
                null,
                [],
                null,
            ],
            'empty key' => [
                '',
                [
                    'response_text' => 'Site successfully created',
                    'site_id' => 13,
                ],
                [
                    'response_text' => 'Site successfully created',
                    'site_id' => 13,
                ],
                null,
            ],
            'key not exists' => [
                'test',
                '---DEFAULT---',
                [
                    'response_text' => 'Site successfully created',
                    'site_id' => 13,
                ],
                '---DEFAULT---',
            ],
        ];
    }

    public function testSetValueException()
    {
        $this->expectException(ResponseDataException::class);

        $response = new Response([]);
        $response->userId = 1;
    }

    public function testIssetProperty()
    {
        $response = new Response(['userId' => 111]);

        self::assertTrue(isset($response->userId));
        self::assertFalse(isset($response->address));
    }

    public function testGetPropertyValue()
    {
        $userId = 111;
        $response = new Response(['userId' => $userId]);

        self::assertSame($userId, $response->userId);
    }

    public function testGetNotExistsPropertyValue()
    {
        $userId = 111;
        $response = new Response(['userId' => $userId]);

        self::assertNull($response->address);
    }

    public function testGetValuesMixedApproach()
    {
        $userId = 111;
        $street = '4094 Kansas St San Diego, CA 92104';
        $response = new Response([
            'userId' => $userId,
            'address' => [
                'street' => $street,
            ],
        ]);

        self::assertNull($response->get('address.street_2'));
        self::assertSame($street, $response->get('address.street'));
        self::assertSame($userId, $response->userId);
        self::assertSame($userId, $response->get('userId'));
        self::assertEquals(['street' => $street], $response->address);
    }
}

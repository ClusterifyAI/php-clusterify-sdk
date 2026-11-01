<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Support;

use Clusterify\Support\Payload;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PayloadTest extends TestCase
{
    public function testDefensiveExtractionAgainstMalformedTypes(): void
    {
        $malformed = [
            'str_array' => ['nested'],
            'str_int' => 42,
            'int_obj' => new stdClass(),
            'int_str' => '120',
            'bool_str' => 'false',
            'map_scalar' => 'not-an-array',
            'list_mixed' => [['id' => 1], 'bad-element', ['id' => 2]],
            'strings_mixed' => ['step 1', ['ignored'], 2],
        ];

        $this->assertSame('fallback', Payload::string($malformed, 'str_array', 'fallback'));
        $this->assertSame('42', Payload::string($malformed, 'str_int'));
        $this->assertNull(Payload::nullableString($malformed, 'str_array'));
        $this->assertSame(99, Payload::int($malformed, 'int_obj', 99));
        $this->assertSame(120, Payload::int($malformed, 'int_str'));
        $this->assertNull(Payload::nullableInt($malformed, 'int_obj'));
        $this->assertFalse(Payload::bool($malformed, 'bool_str', true));
        $this->assertSame([], Payload::map($malformed, 'map_scalar'));
        $this->assertCount(2, Payload::listOfMaps($malformed, 'list_mixed'));
        $this->assertSame(['step 1', '2'], Payload::listOfStrings($malformed, 'strings_mixed'));
    }
}


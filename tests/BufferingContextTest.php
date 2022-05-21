<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\BufferingContext;
use ju1ius\TwigBuffersExtension\Exception\InvalidScope;
use PHPUnit\Framework\TestCase;

final class BufferingContextTest extends TestCase
{
    public function testItThrowsInvalidScope(): void
    {
        $this->expectException(InvalidScope::class);
        $ctx = new BufferingContext();
        $ctx->enter('foo');
        $ctx->leave('bar', '');
    }
}

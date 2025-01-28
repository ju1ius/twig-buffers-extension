<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Utils;

use ju1ius\TwigBuffersExtension\Utils\Output;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OutputTest extends TestCase
{
    #[DataProvider('joinProvider')]
    public function testJoin(iterable $input, string $expected): void
    {
        Assert::assertSame($expected, Output::join($input));
    }

    public static function joinProvider(): iterable
    {
        yield 'array' => [
            ['foo', 'bar', 'baz'],
            'foobarbaz',
        ];
        yield 'traversable' => [
            new \ArrayIterator(['foo', 'bar', 'baz']),
            'foobarbaz',
        ];
    }

    #[DataProvider('captureProvider')]
    public function testCapture(iterable $input, string $expected): void
    {
        Assert::assertSame($expected, Output::capture($input));
    }

    public static function captureProvider(): iterable
    {
        yield 'array' => [
            ['foo', 'bar', 'baz'],
            'foobarbaz',
        ];
        yield 'traversable' => [
            new \ArrayIterator(['foo', 'bar', 'baz']),
            'foobarbaz',
        ];
        yield 'generator' => [
            (fn() => yield from ['foo', 'bar', 'baz'])(),
            'foobarbaz',
        ];
    }

    public function testCaptureException(): void
    {
        $msg = 'output failed';
        $output = static function () use ($msg) {
            yield 'foo';
            throw new \RuntimeException($msg);
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($msg);
        Output::capture($output());
    }
}

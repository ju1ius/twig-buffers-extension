<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\Buffer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ju1ius\TwigBuffersExtension\Buffer
 */
final class BufferTest extends TestCase
{
    public function testIsEmpty()
    {
        $buf = new Buffer();
        Assert::assertTrue($buf->isEmpty());
        $buf->append('foo');
        Assert::assertFalse($buf->isEmpty());
    }

    public function testAppend()
    {
        $buf = new Buffer();
        $buf->append('foo');
        $buf->append('bar');
        Assert::assertSame('foobar', (string)$buf);
    }

    public function testUniqueAppend()
    {
        $buf = new Buffer();
        $buf->append('foo', 'uid');
        $buf->append('bar', 'uid');
        Assert::assertSame('foo', (string)$buf);
    }

    public function testPrepend()
    {
        $buf = new Buffer();
        $buf->prepend('foo');
        $buf->prepend('bar');
        Assert::assertSame('barfoo', (string)$buf);
    }

    public function testUniquePrepend()
    {
        $buf = new Buffer();
        $buf->prepend('foo', 'uid');
        $buf->prepend('bar', 'uid');
        Assert::assertSame('foo', (string)$buf);
    }

    public function testClear()
    {
        $buf = new Buffer();
        $buf->append('foo');
        $buf->append('bar');
        $buf->clear();
        Assert::assertTrue($buf->isEmpty());
        Assert::assertSame('', (string)$buf);
    }

    public function testDidInsert()
    {
        $buf = new Buffer();
        Assert::assertFalse($buf->didInsert('uid'));
        $buf->append('foo', 'uid');
        Assert::assertTrue($buf->didInsert('uid'));
    }
}

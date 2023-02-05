<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\Buffer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Buffer::class)]
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
        Assert::assertSame('foobar', (string)$buf);
    }

    public function testUniquePrepend()
    {
        $buf = new Buffer();
        $buf->prepend('foo', 'uid');
        $buf->prepend('bar', 'uid');
        Assert::assertSame('foo', (string)$buf);
    }

    public function testAppendPrepend()
    {
        $buf = new Buffer();
        $buf->append('foo');
        $buf->append('bar');
        $buf->prepend('baz');
        $buf->prepend('qux');
        Assert::assertSame('bazquxfoobar', (string)$buf);
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

    public function testJoin()
    {
        $buf = new Buffer();
        $buf->append('baz');
        $buf->append('qux');
        $buf->prepend('foo');
        $buf->prepend('bar');
        Assert::assertSame('foo,bar,baz,qux', $buf->join(','));
        Assert::assertSame('foo, bar, baz & qux', $buf->join(', ', ' & '));
    }
}

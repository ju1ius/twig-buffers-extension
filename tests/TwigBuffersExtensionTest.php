<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;
use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;

final class TwigBuffersExtensionTest extends ExtensionTestCase
{
    public function testItRendersNothingWhenBufferIsEmpty(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('empty.html.twig');
        Assert::assertSame('', $result);
    }

    public function testInsertion(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('insertion/insertion.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    public function testInsertionUsingCapture(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('insertion/capture.html.twig');
        Assert::assertSame('foo bar baz', self::normalizeWhitespace($result));
    }

    public function testInsertionThrowsOnUnknownBuffer(): void
    {
        $twig = self::createEnvironment();
        $this->expectException(UnknownBuffer::class);
        $twig->render('insertion/unknown-buffer.html.twig');
    }

    public function testInsertOrIgnore(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('insertion/ignore-missing.html.twig');
        Assert::assertSame('', self::normalizeWhitespace($result));
    }

    public function testInsertOrCreate(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('insertion/create-missing.html.twig');
        Assert::assertSame('<buffer>foobarbaz</buffer>', self::normalizeWhitespace($result));
    }

    public function testJoin(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('join/join.html.twig');
        Assert::assertSame('<buffer>foo, bar, baz</buffer>', self::normalizeWhitespace($result));
    }

    public function testJoinWithFinalGlue(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('join/final-glue.html.twig');
        Assert::assertSame('<buffer>foo, bar & baz</buffer>', self::normalizeWhitespace($result));
    }

    public function testEscapingWorks(): void
    {
        $twig = self::createEnvironment();
        $context = ['xss' => '<script>die();</script>'];

        $capturing = $twig->render('escaping/capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', self::normalizeWhitespace($capturing));

        $nonCapturing = $twig->render('escaping/non-capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', self::normalizeWhitespace($nonCapturing));
    }

    public function testInsertingFromIncludedTemplate(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('include/template.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    public function testBufferCanBeDefinedAfterInsertion(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('include/reversed-order.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    #[DataProvider('insertingWithInheritanceProvider')]
    public function testInsertingWithInheritance(string $template, string $expected)
    {
        $twig = self::createEnvironment();
        $result = $twig->render($template);
        Assert::assertSame($expected, self::normalizeWhitespace($result));
    }

    public static function insertingWithInheritanceProvider(): iterable
    {
        yield 'reference in parent, insert in child' => [
            'extends/simple/index.html.twig',
            'foobarbaz'
        ];
        yield 'reference in child, insert in parent' => [
            'extends/ref-in-child/index.html.twig',
            'foo'
        ];
    }

    public function testInsertingFromEmbeddedTemplate(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('embed/template.html.twig');
        Assert::assertSame('foo bar baz', self::normalizeWhitespace($result));
    }

    public function testInsertingFromEmbeddedTemplateWIthUniqueId(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('embed-uid/template.html.twig');
        Assert::assertSame('<head>HEAD</head> foo bar baz', self::normalizeWhitespace($result));
    }

    public function testBuffersDoNotPersistBetweenRenders(): void
    {
        $twig = self::createEnvironment();
        $first = $twig->render('multiple-renders/first.html.twig');
        Assert::assertSame('foobar', self::normalizeWhitespace($first));
        $second = $twig->render('multiple-renders/second.html.twig');
        Assert::assertSame('', self::normalizeWhitespace($second));
    }

    public function testOneBufferWithMultipleInsertionPoints(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('insertion/multiple-insertion-points.html.twig');
        Assert::assertSame('HEAD -- BODY -- FOOTER', self::normalizeWhitespace($result));
    }

    public function testExistingBufferCanBeCleared(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('clear/clear-existing.html.twig');
        Assert::assertSame('baz', self::normalizeWhitespace($result));
    }

    public function testNonExistingBufferCannotBeCleared(): void
    {
        $twig = self::createEnvironment();
        $this->expectException(UnknownBuffer::class);
        $twig->render('clear/clear-non-existing.html.twig');
    }

    public function testIsBufferInChildTemplate(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('is-buffer/child.html.twig');
        Assert::assertSame('<buffer>child</buffer>', self::normalizeWhitespace($result));
    }

    public function testIsEmpty(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('is-empty/existing.html.twig');
        Assert::assertSame('buffer is empty', self::normalizeWhitespace($result));
    }

    public function testIsEmptyReturnsTrueForNonExistingBuffer(): void
    {
        $twig = self::createEnvironment();
        $result = $twig->render('is-empty/non-existing.html.twig');
        Assert::assertSame('buffer is empty', self::normalizeWhitespace($result));
    }
}

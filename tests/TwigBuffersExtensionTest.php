<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;

final class TwigBuffersExtensionTest extends ExtensionTestCase
{
    #[DataProvider('useYieldProvider')]
    public function testItRendersNothingWhenBufferIsEmpty(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('empty.html.twig');
        Assert::assertSame('', $result);
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertion(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('insertion/insertion.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertionUsingCapture(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('insertion/capture.html.twig');
        Assert::assertSame('foo bar baz', self::normalizeWhitespace($result));
    }

    public function testInsertionThrowsOnUnknownBuffer(): void
    {
        $twig = self::createEnvironment();
        $this->expectException(UnknownBuffer::class);
        $twig->render('insertion/unknown-buffer.html.twig');
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertOrIgnore(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('insertion/ignore-missing.html.twig');
        Assert::assertSame('', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertOrCreate(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('insertion/create-missing.html.twig');
        Assert::assertSame('<buffer>foobarbaz</buffer>', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testJoin(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('join/join.html.twig');
        Assert::assertSame('<buffer>foo, bar, baz</buffer>', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testJoinWithFinalGlue(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('join/final-glue.html.twig');
        Assert::assertSame('<buffer>foo, bar & baz</buffer>', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testEscapingWorks(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $context = ['xss' => '<script>die();</script>'];

        $capturing = $twig->render('escaping/capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', self::normalizeWhitespace($capturing));

        $nonCapturing = $twig->render('escaping/non-capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', self::normalizeWhitespace($nonCapturing));
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertingFromIncludedTemplate(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('include/template.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    #[DataProvider('useYieldProvider')]
    public function testBufferCanBeDefinedAfterInsertion(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('include/reversed-order.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    #[DataProvider('insertingWithInheritanceProvider')]
    public function testInsertingWithInheritance(string $template, bool $useYield, string $expected)
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render($template);
        Assert::assertSame($expected, self::normalizeWhitespace($result));
    }

    public static function insertingWithInheritanceProvider(): iterable
    {
        foreach ([false, true] as $useYield) {
            $yield = \var_export($useYield, true);
            yield "reference in parent, insert in child (yield={$yield})" => [
                'extends/simple/index.html.twig',
                $useYield,
                'foobarbaz',
            ];
            yield "reference in child, insert in parent (yield={$yield})" => [
                'extends/ref-in-child/index.html.twig',
                $useYield,
                'foo',
            ];
        }
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertingFromEmbeddedTemplate(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('embed/template.html.twig');
        Assert::assertSame('foo bar baz', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testInsertingFromEmbeddedTemplateWIthUniqueId(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('embed-uid/template.html.twig');
        Assert::assertSame('<head>HEAD</head> foo bar baz', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testBuffersDoNotPersistBetweenRenders(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $first = $twig->render('multiple-renders/first.html.twig');
        Assert::assertSame('foobar', self::normalizeWhitespace($first));
        $second = $twig->render('multiple-renders/second.html.twig');
        Assert::assertSame('', self::normalizeWhitespace($second));
    }

    #[DataProvider('useYieldProvider')]
    public function testOneBufferWithMultipleInsertionPoints(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('insertion/multiple-insertion-points.html.twig');
        Assert::assertSame('HEAD -- BODY -- FOOTER', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testExistingBufferCanBeCleared(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('clear/clear-existing.html.twig');
        Assert::assertSame('baz', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testNonExistingBufferCannotBeCleared(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $this->expectException(UnknownBuffer::class);
        $twig->render('clear/clear-non-existing.html.twig');
    }

    #[DataProvider('useYieldProvider')]
    public function testIsBufferInChildTemplate(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('is-buffer/child.html.twig');
        Assert::assertSame('<buffer>child</buffer>', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testIsEmpty(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('is-empty/existing.html.twig');
        Assert::assertSame('buffer is empty', self::normalizeWhitespace($result));
    }

    #[DataProvider('useYieldProvider')]
    public function testIsEmptyReturnsTrueForNonExistingBuffer(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('is-empty/non-existing.html.twig');
        Assert::assertSame('buffer is empty', self::normalizeWhitespace($result));
    }
}

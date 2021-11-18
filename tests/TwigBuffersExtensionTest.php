<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;
use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use PHPUnit\Framework\Assert;

final class TwigBuffersExtensionTest extends ExtensionTestCase
{
    public function testItRendersNothingWhenBufferIsEmpty()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('empty.html.twig');
        Assert::assertSame('', $result);
    }

    public function testInsertion()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('insertion/insertion.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    public function testInsertionUsingCapture()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('insertion/capture.html.twig');
        Assert::assertSame('foo bar baz', $this->normalizeWhitespace($result));
    }

    public function testInsertionThrowsOnUnknownBuffer()
    {
        $twig = $this->createEnvironment();
        $this->expectException(UnknownBuffer::class);
        $twig->render('insertion/unknown-buffer.html.twig');
    }

    public function testInsertOrIgnore()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('insertion/ignore-missing.html.twig');
        Assert::assertSame('', $this->normalizeWhitespace($result));
    }

    public function testInsertOrCreate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('insertion/create-missing.html.twig');
        Assert::assertSame('<buffer>foobarbaz</buffer>', $this->normalizeWhitespace($result));
    }

    public function testJoin()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('join/join.html.twig');
        Assert::assertSame('<buffer>foo, bar, baz</buffer>', $this->normalizeWhitespace($result));
    }

    public function testJoinWithFinalGlue()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('join/final-glue.html.twig');
        Assert::assertSame('<buffer>foo, bar & baz</buffer>', $this->normalizeWhitespace($result));
    }

    public function testEscapingWorks()
    {
        $twig = $this->createEnvironment();
        $context = ['xss' => '<script>die();</script>'];

        $capturing = $twig->render('escaping/capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', $this->normalizeWhitespace($capturing));

        $nonCapturing = $twig->render('escaping/non-capturing.html.twig', $context);
        Assert::assertSame('&lt;script&gt;die();&lt;/script&gt;', $this->normalizeWhitespace($nonCapturing));
    }

    public function testInsertingFromIncludedTemplate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('include/template.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    public function testBufferCanBeDefinedAfterInsertion()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('include/reversed-order.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    /**
     * @dataProvider insertingWithInheritanceProvider
     */
    public function testInsertingWithInheritance(string $template, string $expected)
    {
        $twig = $this->createEnvironment();
        $result = $twig->render($template);
        Assert::assertSame($expected, $this->normalizeWhitespace($result));
    }

    public function insertingWithInheritanceProvider(): iterable
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

    public function testInsertingFromEmbeddedTemplate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('embed/template.html.twig');
        Assert::assertSame('foo bar baz', $this->normalizeWhitespace($result));
    }

    public function testInsertingFromEmbeddedTemplateWIthUniqueId()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('embed-uid/template.html.twig');
        Assert::assertSame('<head>HEAD</head> foo bar baz', $this->normalizeWhitespace($result));
    }

    public function testBuffersDoNotPersistBetweenRenders()
    {
        $twig = $this->createEnvironment();
        $first = $twig->render('multiple-renders/first.html.twig');
        Assert::assertSame('foobar', $this->normalizeWhitespace($first));
        $second = $twig->render('multiple-renders/second.html.twig');
        Assert::assertSame('', $this->normalizeWhitespace($second));
    }

    public function testOneBufferWithMultipleInsertionPoints()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('insertion/multiple-insertion-points.html.twig');
        Assert::assertSame('HEAD -- BODY -- FOOTER', $this->normalizeWhitespace($result));
    }

    public function testExistingBufferCanBeCleared()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('clear/clear-existing.html.twig');
        Assert::assertSame('baz', $this->normalizeWhitespace($result));
    }

    public function testNonExistingBufferCannotBeCleared()
    {
        $twig = $this->createEnvironment();
        $this->expectException(UnknownBuffer::class);
        $twig->render('clear/clear-non-existing.html.twig');
    }

    public function testIsBufferInChildTemplate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('is-buffer/child.html.twig');
        Assert::assertSame('<buffer>child</buffer>', $this->normalizeWhitespace($result));
    }

    public function testIsEmpty()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('is-empty/existing.html.twig');
        Assert::assertSame('buffer is empty', $this->normalizeWhitespace($result));
    }

    public function testIsEmptyReturnsTrueForNonExistingBuffer()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('is-empty/non-existing.html.twig');
        Assert::assertSame('buffer is empty', $this->normalizeWhitespace($result));
    }
}

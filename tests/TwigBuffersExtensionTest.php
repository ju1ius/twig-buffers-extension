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
        $result = $twig->render('insertion.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }

    public function testInsertionUsingCapture()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('capture.html.twig');
        Assert::assertSame('foo bar baz', $this->normalizeWhitespace($result));
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

    public function testInsertingFromChildTemplate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('extends/template.html.twig');
        Assert::assertSame('foobarbaz', $this->normalizeWhitespace($result));
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
        $result = $twig->render('multiple-insertion-points.html.twig');
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

    public function testHasBufferInChildTemplate()
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('has/child.html.twig');
        Assert::assertSame('<buffer>child</buffer>', $this->normalizeWhitespace($result));
    }
}

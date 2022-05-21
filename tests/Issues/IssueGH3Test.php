<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Issues;

use ju1ius\Tests\TwigBuffersExtension\ExtensionTestCase;
use PHPUnit\Framework\Assert;
use Twig\Loader\ArrayLoader;

/**
 * Test case for https://github.com/ju1ius/twig-buffers-extension/issues/3
 */
final class IssueGH3Test extends ExtensionTestCase
{
    private const TEMPLATE = <<<'TWIG'
    {% buffer test %} {% buffer test joined by ',' %}

    {% append to test 'foo' %}
    {% append to test 'bar' %}
    TWIG;

    public function testMultipleReferencesWithDifferentArguments(): void
    {
        $twig = $this->createEnvironment(new ArrayLoader([
            'gh-3.twig' => self::TEMPLATE,
        ]));
        $result = $twig->render('gh-3.twig');
        Assert::assertSame('foobar foo,bar', $this->normalizeWhitespace($result));
    }
}

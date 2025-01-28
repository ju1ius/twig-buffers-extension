<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Issues;

use ju1ius\Tests\TwigBuffersExtension\ExtensionTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('useYieldProvider')]
    public function testMultipleReferencesWithDifferentArguments(bool $useYield): void
    {
        $twig = self::createEnvironment(
            loader: new ArrayLoader(['gh-3.twig' => self::TEMPLATE]),
            useYield: $useYield,
        );
        $result = $twig->render('gh-3.twig');
        Assert::assertSame('foobar foo,bar', self::normalizeWhitespace($result));
    }
}

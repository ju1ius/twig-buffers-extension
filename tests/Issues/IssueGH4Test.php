<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Issues;

use ju1ius\Tests\TwigBuffersExtension\ExtensionTestCase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see https://github.com/ju1ius/twig-buffers-extension/issues/4
 */
final class IssueGH4Test extends ExtensionTestCase
{
    #[DataProvider('useYieldProvider')]
    public function testIssue(bool $useYield): void
    {
        $twig = self::createEnvironment(useYield: $useYield);
        $result = $twig->render('issues/gh-4/child.html.twig');
        Assert::assertSame('<head>test</head> <body></body>', $this->normalizeWhitespace($result));
    }
}

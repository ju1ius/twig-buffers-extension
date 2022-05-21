<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Issues;

use ju1ius\Tests\TwigBuffersExtension\ExtensionTestCase;
use PHPUnit\Framework\Assert;

final class IssueGH4Test extends ExtensionTestCase
{
    public function testIssue(): void
    {
        $twig = $this->createEnvironment();
        $result = $twig->render('issues/gh-4/child.html.twig');
        Assert::assertSame('<head>test</head> <body></body>', $this->normalizeWhitespace($result));
    }
}

<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension\Issues;

use ju1ius\Tests\TwigBuffersExtension\ExtensionTestCase;
use PHPUnit\Framework\Assert;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

final class IssueGH5Test extends ExtensionTestCase
{
    public function testExtensionCompatibility(): void
    {
        $twig = self::createEnvironment();
        $twig->addExtension(new ProfilerExtension($profile = new Profile()));
        $result = $twig->render('insertion/insertion.html.twig');
        Assert::assertSame('foobarbaz', $result);
    }
}

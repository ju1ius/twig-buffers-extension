<?php declare(strict_types=1);

namespace ju1ius\Tests\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\TwigBuffersExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ExtensionTestCase extends TestCase
{
    protected function createEnvironment($cache = false): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/templates');
        $twig = new Environment($loader, [
            'cache' => $cache ? __DIR__ . '/cache' : false,
            'debug' => false,
            'strict_variables' => true,
        ]);
        $twig->addExtension(new TwigBuffersExtension());
        return $twig;
    }

    protected function normalizeWhitespace(string $input): string
    {
        return preg_replace('/\s+/', ' ', trim($input));
    }
}

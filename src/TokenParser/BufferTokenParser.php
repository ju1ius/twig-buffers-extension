<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferReferenceNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class BufferTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();
        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        $stream->expect(Token::BLOCK_END_TYPE);

        return new BufferReferenceNode($name, $token->getLine());
    }

    public function getTag()
    {
        return 'buffer';
    }
}

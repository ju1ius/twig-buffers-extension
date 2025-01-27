<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferReferenceNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class BufferTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();
        $name = $stream->expect(Token::NAME_TYPE)->getValue();

        $glue = $finalGlue = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'joined')) {
            $stream->expect(Token::NAME_TYPE, 'by');
            $glue = $this->parser->getExpressionParser()->parseExpression();
            if ($stream->nextIf(Token::PUNCTUATION_TYPE, ',')) {
                $finalGlue = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new BufferReferenceNode($name, $glue, $finalGlue, $token->getLine());
    }

    public function getTag(): string
    {
        return 'buffer';
    }
}

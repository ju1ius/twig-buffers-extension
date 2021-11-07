<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

abstract class BufferInsertionTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $onMissing = BufferInsertionNode::ON_MISSING_ERROR;
        if ($stream->nextIf(Token::OPERATOR_TYPE, 'or')) {
            if ($stream->nextIf(Token::NAME_TYPE, 'ignore')) {
                $onMissing = BufferInsertionNode::ON_MISSING_IGNORE;
            } else if ($stream->nextIf(Token::NAME_TYPE, 'create')) {
                $onMissing = BufferInsertionNode::ON_MISSING_CREATE;
            }
        }

        $stream->expect(Token::NAME_TYPE, 'to');
        $name = $stream->expect(Token::NAME_TYPE)->getValue();

        $id = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'as')) {
            $id = $stream->expect(Token::NAME_TYPE)->getValue();
        }

        $this->parser->pushLocalScope();

        $capture = false;
        if ($stream->nextIf(Token::BLOCK_END_TYPE)) {
            $capture = true;
            $body = $this->parser->subparse(fn(Token $token) => $token->test("end{$this->getTag()}"), true);
        } else {
            $body = new PrintNode($this->parser->getExpressionParser()->parseExpression(), $lineno);
        }

        $this->parser->popLocalScope();
        $stream->expect(Token::BLOCK_END_TYPE);

        return $this->createNode($name, $body, $id, $onMissing, $lineno);
    }

    abstract protected function createNode(
        string $name,
        Node $body,
        ?string $id,
        int $onMissing,
        int $lineno,
    ): BufferInsertionNode;
}

<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\NodeVisitor;

use ju1ius\TwigBuffersExtension\Node\BufferReferenceNode;
use ju1ius\TwigBuffersExtension\Node\ModuleBodyNode;
use ju1ius\TwigBuffersExtension\Node\TemplateClassFooterNode;
use ju1ius\TwigBuffersExtension\Node\TemplateConstructorNode;
use SplStack;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

final class ModuleNodeVisitor implements NodeVisitorInterface
{
    private array $bufferReferences = [];

    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ModuleNode) {
            $this->bufferReferences = [];
        }
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof BufferReferenceNode) {
            $this->bufferReferences[] = $node->getAttribute('name');
        }
        if ($node instanceof ModuleNode) {
            $this->registerModuleBuffers($node);
        }
        return $node;
    }

    public function getPriority()
    {
        return -10;
    }

    private function registerModuleBuffers(ModuleNode $module)
    {
        $references = array_unique($this->bufferReferences);

        $footer = $module->getNode('class_end');
        $footer->setNode('buffers', new TemplateClassFooterNode($references));

        $constructor = $module->getNode('constructor_end');
        $constructor->setNode('buffers', new TemplateConstructorNode($references));

        if (!$references) return;
        $body = $module->getNode('body');
        $module->setNode('body', new ModuleBodyNode($body));
    }
}

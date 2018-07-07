<?php
/**
 * This file is part of PHPinnacle/Pinnacle.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\Pinnacle\Composer;

use Composer\Autoload\ClassLoader;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;

class ComposerClassFinder extends ClassFinder
{
    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @param ClassLoader $loader
     */
    public function __construct(ClassLoader $loader)
    {
        $this->loader = $loader;
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
    }

    /**
     * @param string $root
     *
     * @return self
     */
    public static function composer(string $root): self
    {
        $file = $root . '/vendor/autoload.php';

        if (!\is_readable($file)) {
            throw new \InvalidArgumentException('Invalid project root directory.');
        }

        return new self(require $file);
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    protected function findClasses(string $pattern): array
    {
        $found = \array_keys($this->loader->getClassMap());

        if ($this->loader->isClassMapAuthoritative()) {
            return $found;
        }

        $prefixes = \array_merge($this->loader->getPrefixes(), $this->loader->getPrefixesPsr4());

        foreach ($prefixes as $prefix => $paths) {
            $check = \strlen($pattern) <= strlen($prefix) ? [$pattern, $prefix] : [$prefix, $pattern];

            if (!$this->match(...$check)) {
                continue;
            }

            foreach ($paths as $path) {
                $found += $this->findInPath($path);
            }
        }

        return $found;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    private function findInPath(string $path): array
    {
        $found = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $files    = new \RegexIterator($iterator, '/\.php$/');

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isReadable()) {
                continue;
            }

            try {
                $ast = $this->parser->parse(\file_get_contents($file->getRealPath()));

                if (null === $ast) {
                    continue;
                }

                foreach ($this->traverse($ast) as $definition) {
                    $found[] = $definition->namespacedName->toString();
                }
            } catch (Error $error) {
            }
        }

        return $found;
    }

    /**
     * @param array $ast
     *
     * @return array
     */
    private function traverse(array $ast): array
    {
        $visitor = new NodeVisitor\FindingVisitor(function (Node $node) {
            return $node instanceof Node\Stmt\Class_ && $node->name !== null;
        });

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->addVisitor(new NodeVisitor\NameResolver());
        $traverser->traverse($ast);

        return $visitor->getFoundNodes();
    }
}

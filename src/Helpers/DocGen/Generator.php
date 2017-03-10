<?php

namespace Afosto\Bp\Helpers\DocGen;

use Afosto\Bp\Components\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generator extends Command {

    /**
     * @var string
     */
    public $root;

    public function configure() {
        $this->setName('docgen')->setDescription('Scraper for model data');
        $this->addArgument('namespace', InputArgument::OPTIONAL, 'Directory of models (relative to root / src directory)?', 'Models');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $root = $this->root . DIRECTORY_SEPARATOR . '/src/' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR;
        $directory = new \RecursiveDirectoryIterator($root);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $filePath = current($file);
            include($filePath);
        }

        foreach (get_declared_classes() as $classPath) {
            if (strpos($classPath, $input->getArgument('namespace')) !== false) {
                $class = new $classPath();
                if ($class instanceof Model) {

                    $reflection = new \ReflectionClass($class);
                    $fileLines = file($reflection->getFileName());

                    $newLines = [];
                    $docIncluded = false;
                    foreach ($fileLines as $fileLine) {

                        if (strpos($fileLine, 'class ' . basename($reflection->getFileName(), '.php')) !== false && $docIncluded === false) {
                            foreach ($class::getDocBlock() as $docLine) {
                                $newLines[] = trim($docLine);
                            }
                            $docIncluded = true;
                        }
                        $newLines[] = rtrim($fileLine);
                    }

                    file_put_contents($reflection->getFileName(), implode(PHP_EOL, $newLines));
                    $output->writeln('Added docs for ' . $reflection->getShortName());
                }
            }
        }
    }

}
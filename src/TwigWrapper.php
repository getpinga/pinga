<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigWrapper
{
    private Environment $twig;

    public function __construct(string $templateDir)
    {
        $loader = new FilesystemLoader($templateDir);
        $this->twig = new Environment($loader);
    }

    public function render(string $templateName, array $context = []): string
    {
        return $this->twig->render($templateName, $context);
    }
}

<?php

namespace App;

use League\Plates\Engine;

class PlatesWrapper
{
    private Engine $plates;

    public function __construct(string $templateDir)
    {
        $this->plates = new Engine($templateDir);
    }

    public function render(string $templateName, array $context = []): string
    {
        return $this->plates->render($templateName, $context);
    }
}

<?php

declare(strict_types=1);

namespace Charcoal\View;

/**
 * Interface LoaderInterface
 */
interface LoaderInterface
{
    /**
     * @param  string $ident The template to load.
     * @return string
     */
    public function load($ident);

    /**
     * @param  string      $varName       The name of the variable to set this template unto.
     * @param  string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void;

    /**
     * @param  string $varName The name of the variable to get template ident from.
     * @return string
     */
    public function dynamicTemplate(string $varName): string;
}

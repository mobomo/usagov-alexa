<?php

namespace MaxBeckers\AmazonAlexa\Response\Directives\Display;

use MaxBeckers\AmazonAlexa\Response\Directives\Directive;

/**
 * @author Maximilian Beckers <beckers.maximilian@gmail.com>
 */
class RenderTemplateDirective extends Directive
{
    const TYPE = 'Display.RenderTemplate';

    /**
     * @var Template|null
     */
    public $template;

    /**
     * @param Template $template
     *
     * @return RenderTemplateDirective
     */
    public static function create(Template $template): self
    {
        $renderTemplateDirective = new self();

        $renderTemplateDirective->type     = self::TYPE;
        $renderTemplateDirective->template = $template;

        return $renderTemplateDirective;
    }
}

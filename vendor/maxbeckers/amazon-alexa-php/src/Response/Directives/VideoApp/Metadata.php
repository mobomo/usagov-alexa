<?php

namespace MaxBeckers\AmazonAlexa\Response\Directives\VideoApp;

/**
 * @author Maximilian Beckers <beckers.maximilian@gmail.com>
 */
class Metadata
{
    /**
     * @var string|null
     */
    public $title;

    /**
     * @var string|null
     */
    public $subtitle;

    /**
     * @param string|null $title
     * @param string|null $subtitle
     *
     * @return Metadata
     */
    public static function create(string $title = null, string $subtitle = null): self
    {
        $metadata = new self();

        $metadata->title    = $title;
        $metadata->subtitle = $subtitle;

        return $metadata;
    }
}

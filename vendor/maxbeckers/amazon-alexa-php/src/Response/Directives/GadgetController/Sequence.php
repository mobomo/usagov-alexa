<?php

namespace MaxBeckers\AmazonAlexa\Response\Directives\GadgetController;

/**
 * @author Maximilian Beckers <beckers.maximilian@gmail.com>
 */
class Sequence
{
    /**
     * @var int|null
     */
    public $durationMs;

    /**
     * @var bool|null
     */
    public $blend;

    /**
     * @var string|null
     */
    public $color;

    /**
     * @param int    $durationMs
     * @param string $color
     * @param bool   $blend
     *
     * @return Sequence
     */
    public static function create(int $durationMs, string $color, bool $blend = false): self
    {
        $sequence = new self();

        $sequence->durationMs = $durationMs;
        $sequence->color      = $color;
        $sequence->blend      = $blend;

        return $sequence;
    }
}

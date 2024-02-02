<?php

namespace MaxBeckers\AmazonAlexa\Request\Request\AudioPlayer;

use MaxBeckers\AmazonAlexa\Helper\PropertyHelper;
use MaxBeckers\AmazonAlexa\Request\Request\AbstractRequest;

/**
 * @author Maximilian Beckers <beckers.maximilian@gmail.com>
 */
class PlaybackNearlyFinishedRequest extends AudioPlayerRequest
{
    const TYPE = 'AudioPlayer.PlaybackNearlyFinished';

    /**
     * @var int|null
     */
    public $offsetInMilliseconds;

    /**
     * @inheritdoc
     */
    public static function fromAmazonRequest(array $amazonRequest): AbstractRequest
    {
        $request = new self();

        $request->type                 = self::TYPE;
        $request->offsetInMilliseconds = PropertyHelper::checkNullValueInt($amazonRequest, 'offsetInMilliseconds');
        $request->setRequestData($amazonRequest);

        return $request;
    }
}

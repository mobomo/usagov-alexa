<?php

namespace MaxBeckers\AmazonAlexa\Request\Request\System;

use MaxBeckers\AmazonAlexa\Helper\PropertyHelper;

/**
 * @author Maximilian Beckers <beckers.maximilian@gmail.com>
 */
class Status
{
    /**
     * @var string|null
     */
    public $code;

    /**
     * @var string|null
     */
    public $message;

    /**
     * @param array $amazonRequest
     *
     * @return Status
     */
    public static function fromAmazonRequest(array $amazonRequest): self
    {
        $status = new self();

        $status->code    = PropertyHelper::checkNullValueString($amazonRequest, 'code');
        $status->message = PropertyHelper::checkNullValueString($amazonRequest, 'message');

        return $status;
    }
}

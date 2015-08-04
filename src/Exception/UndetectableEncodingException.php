<?php

namespace Ddeboer\Transcoder\Exception;

class UndetectableEncodingException extends StringException
{
    public function __construct($error)
    {
        parent::__construct(sprintf('Encoding is undetectable: %s', $error));
    }
}

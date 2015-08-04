<?php

namespace Ddeboer\Transcoder\Exception;

class UnsupportedEncodingException extends StringException
{
    public function __construct($encoding, $message = null)
    {
        $error = sprintf('Encoding %s is unsupported on this platform', $encoding);
        if ($message) {
            $error .= ': ' . $message;
        }

        return parent::__construct($error);
    }
}

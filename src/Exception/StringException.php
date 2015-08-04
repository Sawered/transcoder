<?php

namespace Ddeboer\Transcoder\Exception;

class StringException extends \RuntimeException
{
    protected $string;

    public function setString($str)
    {
        $this->string = $str;
    }

    public function getString()
    {
        return $this->string;
    }
}

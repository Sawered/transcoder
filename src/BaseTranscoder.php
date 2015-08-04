<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\ExtensionMissingException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;
use Exception;

abstract class BaseTranscoder
{
    private $lastException;

    public function removeInvalidUTF8Bytes($str)
    {
        $return = '';
        $length = strlen($str);
        $invalid = array_flip(array("\xEF\xBF\xBF" /* U-FFFF */, "\xEF\xBF\xBE" /* U-FFFE */));

        for ($i=0; $i < $length; $i++)
        {
            $c = ord($str[$o=$i]);

            if ($c < 0x80) $n=0; # 0bbbbbbb
            elseif (($c & 0xE0) === 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) === 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) === 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) === 0xF8) $n=4; # 111110bb
            else continue; # Does not match

            for ($j=++$n; --$j;) # n bytes matching 10bbbbbb follow ?
                if ((++$i === $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    continue 2
            ;

            $match = substr($str, $o, $n);

            if ($n === 3 && isset($invalid[$match])) # test invalid sequences
                continue;

            $return .= $match;
        }
        return $return;
    }

    protected function getLastException()
    {
        return $this->lastException;
    }

    protected function setLastException(Exception $e = null)
    {
        $this->lastException = $e;
    }
}

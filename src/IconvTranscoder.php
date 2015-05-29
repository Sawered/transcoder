<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\ExtensionMissingException;
use Ddeboer\Transcoder\Exception\IllegalCharacterException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;

class IconvTranscoder extends BaseTranscoder implements TranscoderInterface
{
    private $defaultEncoding;

    public function __construct($defaultEncoding = 'UTF-8//IGNORE')
    {
        if (!function_exists('iconv')) {
            throw new ExtensionMissingException('iconv');
        }

        $this->defaultEncoding = $defaultEncoding;
    }

    /**
     * {@inheritdoc}
     */
    public function transcode($string, $from = null, $to = null)
    {
        $to = $to ?: $this->defaultEncoding;

        try{
            return $this->realTranscode($string,$from,$to);

        }catch(IllegalCharacterException $e){
            //var_dump('cleanup',$from,$to);

            if(strtolower($from)=='utf-8'
                && strpos(strtolower($to),'utf-8') === 0
            ){
                //var_dump('cleanup');
                $string = $this->removeInvalidUTF8Bytes($string);
                return $this->realTranscode($string,$from,$to);
            }
            throw $e;
        }

    }

    protected function realTranscode($string,$from,$to)
    {
        set_error_handler(
            function ($no, $message) use ($string) {
                if (1 === preg_match('/Wrong charset, conversion (.+) is/', $message, $matches)) {
                    throw new UnsupportedEncodingException($matches[1], $message);
                } else {
                    throw new IllegalCharacterException($string, $message);
                }
            },
            E_NOTICE | E_USER_NOTICE
        );

        // UTF8 to UTF8//IGONRE will generate error
        $result = iconv($from, $to, $string);
        restore_error_handler();

        return $result;
    }
}

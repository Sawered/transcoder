<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\StringException;
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

            if(strtolower($from)=='utf-8'
                && strpos(strtolower($to),'utf-8') === 0
            ){
                $string = $this->removeInvalidUTF8Bytes($string);
                return $this->realTranscode($string,$from,$to);
            }
            throw $e;
        }

    }

    protected function realTranscode($string,$from,$to)
    {
        set_error_handler([$this,'errorHandler'], E_NOTICE | E_USER_NOTICE);
        $this->setLastException(null);

        // UTF8 to UTF8//IGNORE will generate error
        $result = iconv($from, $to, $string);
        restore_error_handler();

        if($e = $this->getLastException()){
            if($e instanceOf StringException){
                $e->setString($string);
                throw $e;
            }
        }

        return $result;
    }


    public function errorHandler($no, $message)
    {
        if (1 === preg_match('/Wrong charset, conversion (.+) is/', $message, $matches)) {
            $this->setLastException( new UnsupportedEncodingException($matches[1], $message));
        } else {
            $this->setLastException( new IllegalCharacterException($message));
        }
        return true; //otherwise you cannot restore previos handler
    }
}

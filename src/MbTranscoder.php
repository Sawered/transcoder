<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\StringException;
use Ddeboer\Transcoder\Exception\ExtensionMissingException;
use Ddeboer\Transcoder\Exception\UndetectableEncodingException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;

class MbTranscoder extends BaseTranscoder implements TranscoderInterface
{
    private static $encodings;
    private $defaultEncoding;

    public function __construct($defaultEncoding = 'UTF-8')
    {
        if (!function_exists('mb_convert_encoding')) {
            throw new ExtensionMissingException('mb');
        }

        if (null === self::$encodings) {
            self::$encodings = array_change_key_case(
                array_flip(mb_list_encodings()),
                CASE_LOWER
            );
        }

        $this->assertSupported($defaultEncoding);
        $this->defaultEncoding = $defaultEncoding;
    }

    /**
     * {@inheritdoc}
     */
    public function transcode($string, $from = null, $to = null)
    {
        if ($from) {
            if (is_array($from)) {
                array_map(array($this, 'assertSupported'), $from);
            } else {
                $this->assertSupported($from);
            }
        }

        if (!$from || 'auto' === $from) {
            set_error_handler([$this,'errorHandler'],E_WARNING);
        }
        $this->setLastException(null);


        if ($to) {
            $this->assertSupported($to);
        }

        $result = mb_convert_encoding(
            $string,
            $to ?: $this->defaultEncoding,
            $from ?: 'auto'
        );

        if($e = $this->getLastException()){
            if($e instanceOf StringException){
                $e->setString($string);
                throw $e;
            }
        }

        restore_error_handler();

        return $result;
    }

    private function assertSupported($encoding)
    {
        if (!$this->isSupported($encoding)) {
            throw new UnsupportedEncodingException($encoding);
        }
    }

    private function isSupported($encoding)
    {
        return isset(self::$encodings[strtolower($encoding)]);
    }

    public function errorHandler($no, $warning)
    {
        $this->setLastException(new UndetectableEncodingException($warning));
        return true; //otherwise you cannot restore previos handler
    }
}

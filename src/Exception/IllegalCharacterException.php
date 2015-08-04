<?php

namespace Ddeboer\Transcoder\Exception;

class IllegalCharacterException extends StringException
{
    public function __construct($warning)
    {
        parent::__construct(
            sprintf(
                'String (see exception) contains an illegal character: %s',
                $warning
            )
        );
    }
}

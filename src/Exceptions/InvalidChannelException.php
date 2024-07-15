<?php

namespace Gabrielbs\Rabbitmq\Exceptions;

class InvalidChannelException extends Exception
{
    protected $message = 'O canal informado não é válido.';
}

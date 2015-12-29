<?php

namespace App\Exceptions;

class eveAPIException extends \Exception
{
    public function handle() {
        $output['field'] = 'api';
        $output['error'] = $this->message;

        return response()->json($output);
    }
}

<?php

namespace App\Exceptions;

class eveAPIException extends \Exception
{
    public function reportOverride()
    {
        return;
    }
    public function renderOverride()
    {
        $output['field'] = 'api';
        $output['error'] = $this->message;

        return response()->json($output);
    }
}

<?php

namespace App\Support;

use App\Packages\SendGrid\SendGrid;

class CustomMail
{
    private $mailProvider;

    public function __construct()
    {
        $this->mailProvider = new SendGrid();
    }

    public function getMailProvider()
    {
        return $this->mailProvider;
    }
}

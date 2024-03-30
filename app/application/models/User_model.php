<?php

class User_model extends Custom_model
{
    public $table                   = 'employee';
    public $primary_key             = 'uid';
    // public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();
    }
}

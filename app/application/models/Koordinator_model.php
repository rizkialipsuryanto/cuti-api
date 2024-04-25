<?php

class Koordinator_model extends Custom_model
{
    public $table                   = 'l_koordinator_karu';
    public $primary_key             = 'id';
    // public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();
    }
}

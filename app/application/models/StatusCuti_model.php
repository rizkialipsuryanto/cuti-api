<?php

class StatusCuti_model extends Custom_model
{
    public $table                   = 'l_status_cuti';
    public $primary_key             = 'id_status';
    // public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();
    }
}

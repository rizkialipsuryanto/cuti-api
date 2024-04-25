<?php

class JenisCuti_model extends Custom_model
{
    public $table                   = 'l_jenis_cuti';
    public $primary_key             = 'id_jenis_cuti';
    // public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();
    }
}

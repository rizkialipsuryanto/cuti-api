<?php

class RiwayatView_model extends Custom_model
{
    public $table                   = 'v_list_riwayat_cuti';
    public $primary_key             = 'no_cuti';
    public $soft_deletes            = FALSE;
    public $timestamps              = FALSE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();
    }
}

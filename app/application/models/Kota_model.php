<?php

class Kota_model extends Custom_model
{
    public $table                   = 'm_kota';
    public $primary_key             = 'id';
    public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();

        $this->has_one['armada'] = array(
            'foreign_model'     => 'Kota_model',
            'foreign_table'     => 'm_kota',
            'foreign_key'       => 'id',
            'local_key'         => 'id_prov'
        );

        $this->has_one['kota_origin'] = array(
            'foreign_model'     => 'Kota_model',
            'foreign_table'     => 'm_kota',
            'foreign_key'       => 'id',
            'local_key'         => 'id_prov'
        );
    }
}

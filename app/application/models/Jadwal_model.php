<?php

class Jadwal_model extends Custom_model
{
    public $table                   = 'm_jadwal';
    public $primary_key             = 'id';
    public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();

        $this->has_one['armada'] = array(
            'foreign_model'     => 'Armada_model',
            'foreign_table'     => 'm_armada',
            'foreign_key'       => 'id',
            'local_key'         => 'id_armada'
        );

        $this->has_one['kota_origin'] = array(
            'foreign_model'     => 'Kota_model',
            'foreign_table'     => 'm_kota',
            'foreign_key'       => 'id',
            'local_key'         => 'id_kota_origin'
        );

        $this->has_one['kota_destination'] = array(
            'foreign_model'     => 'Kota_model',
            'foreign_table'     => 'm_kota',
            'foreign_key'       => 'id',
            'local_key'         => 'id_kota_destination'
        );
    }
}

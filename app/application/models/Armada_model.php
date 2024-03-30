<?php

class Armada_model extends Custom_model
{
    public $table                   = 'm_armada';
    public $primary_key             = 'id';
    public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();

        $this->has_one['driver'] = array(
            'foreign_model'     => 'User_model',
            'foreign_table'     => 'm_user',
            'foreign_key'       => 'id',
            'local_key'         => 'id_driver'
        );
    }
}

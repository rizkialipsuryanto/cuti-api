<?php

class Booking_model extends Custom_model
{
    public $table                   = 'tr_booking';
    public $primary_key             = 'id';
    public $soft_deletes            = TRUE;
    public $timestamps              = TRUE;
    public $return_as               = "array";

    public function __construct()
    {
        parent::__construct();

        $this->has_many['detail'] = array(
            'foreign_model'     => 'BookingDetail_model',
            'foreign_table'     => 'tr_booking_detail',
            'foreign_key'       => 'id_booking',
            'local_key'         => 'id'
        );

        $this->has_one['user'] = array(
            'foreign_model'     => 'User_model',
            'foreign_table'     => 'm_user',
            'foreign_key'       => 'id',
            'local_key'         => 'id_user'
        );

        $this->has_one['jadwal'] = array(
            'foreign_model'     => 'Jadwal_model',
            'foreign_table'     => 'm_jadwal',
            'foreign_key'       => 'id',
            'local_key'         => 'id_jadwal'
        );
    }
}

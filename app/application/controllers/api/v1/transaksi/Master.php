<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Master extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            "User_model"            => "user",
            "JenisCuti_model"         => "jeniscuti"
        ]);
    }

    public function jeniscuti_get()
    {
        $jeniscuti = $this->jeniscuti->get_all() ?: [];

        if (!$jeniscuti) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data Jenis Cuti tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data Jenis Cuti ditemukan",
            "data"          => $jeniscuti
        ], REST_Controller::HTTP_OK);
    }
}

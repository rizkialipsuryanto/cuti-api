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
            "JenisCuti_model"         => "jeniscuti",
            "StatusCuti_model"         => "statuscuti",
            "Koordinator_model"         => "koordinator",
            "KepalaInstalasi_model"         => "kepalainstalasi",
            "AtasanLangsung_model"         => "atasanlangsung"
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

    public function koordinator_get()
    {
        $koordinator = $this->koordinator->get_all() ?: [];

        if (!$koordinator) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data Koordinator tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data Koordinator ditemukan",
            "data"          => $koordinator
        ], REST_Controller::HTTP_OK);
    }

    public function kepalainstalasi_get()
    {
        $kepalainstalasi = $this->kepalainstalasi->get_all() ?: [];

        if (!$kepalainstalasi) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data kepalainstalasi tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data kepalainstalasi ditemukan",
            "data"          => $kepalainstalasi
        ], REST_Controller::HTTP_OK);
    }

    public function atasanlangsung_get()
    {
        $atasanlangsung = $this->atasanlangsung->get_all() ?: [];

        if (!$atasanlangsung) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data atasanlangsung tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data atasanlangsung ditemukan",
            "data"          => $atasanlangsung
        ], REST_Controller::HTTP_OK);
    }

    public function statuscuti_get()
    {
        $statuscuti = $this->statuscuti->get_all() ?: [];

        if (!$statuscuti) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data Status tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data Jenis Cuti ditemukan",
            "data"          => $statuscuti
        ], REST_Controller::HTTP_OK);
    }
}

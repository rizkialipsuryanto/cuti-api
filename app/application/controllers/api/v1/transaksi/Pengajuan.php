<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Pengajuan extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            "User_model"            => "user",
            "Booking_model"         => "booking",
            "RiwayatView_model"         => "riwayat"
        ]);
    }

    private function validateEmpty($input, $keterangan)
    {
        if (empty($input)) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "$keterangan Tidak boleh kosong",
            ), REST_Controller::HTTP_OK);
        }
    }

    public function riwayat_get()
    {
        $id        = $this->input->get("uid");
        $page           = $this->input->get("page")     ?: "1";
        $perPage        = $this->input->get("perpage")  ?: "10";

        $this->validateEmpty($id, "ID User");

        $riwayat = $this->riwayat->where(["uid" => $id])->get();

        if (!$riwayat) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data riwayat tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        $totalData  = $this->riwayat
            ->where(["uid" => $riwayat["uid"]])
            ->count_rows();

        $detailBooking      = $this->riwayat
            // ->with_user("fields:nama_emp,username")
            // ->with_detail()
            ->where(["uid" => $riwayat["uid"]])
            ->order_by("no_cuti", "DESC")
            ->limit($perPage, (($page - 1) * $perPage))
            ->get_all() ?: [];

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "OK",
            "data"          => [
                "items"     => $detailBooking,
                "paging"    => [
                    "page"          => (int) $page,
                    "per_page"      => (int) $perPage,
                    "total_data"    => $totalData,
                ]
            ]
        ], REST_Controller::HTTP_OK);

        // return $this->response([
        //     "status"        => true,
        //     "code"          => REST_Controller::HTTP_OK,
        //     "message"       => "Data riwayat ditemukan",
        //     "data"          => $riwayat
        // ], REST_Controller::HTTP_OK);
    }

    public function list_get()
    {
        $uid        = $this->input->get("uid");
        $page           = $this->input->get("page")     ?: "1";
        $perPage        = $this->input->get("perpage")  ?: "10";

        $this->validateEmpty($uid, "ID User");

        $_user          = $this->user->where(["uid" => $uid])->get();
        if (!$_user) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Terjadi kesalahan. User tidak diketahui. Silahkan coba kembali !",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $totalData  = $this->booking
            ->where(["uid" => $_user["uid"]])
            ->count_rows();

        $detailBooking      = $this->booking
            ->with_user("fields:nama_emp,username")
            // ->with_detail()
            // ->with_jadwal([
            //     "with"      => [
            //         [
            //             "relation"  => "armada",
            //             "fields"    => "nopol,merk,kapasitas,bbm,warna",
            //             "with"      => [
            //                 "relation"  => "driver",
            //                 "fields"    => "nama,jenis_kelamin"
            //             ]
            //         ],
            //         [
            //             "relation"  => "kota_origin",
            //             "fields"    => "nama"
            //         ],
            //         [
            //             "relation"  => "kota_destination",
            //             "fields"    => "nama"
            //         ],

            //     ],
            // ])
            ->where(["uid" => $_user["uid"]])
            ->order_by("no_cuti", "DESC")
            ->limit($perPage, (($page - 1) * $perPage))
            ->get_all() ?: [];

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "OK",
            "data"          => [
                "items"     => $detailBooking,
                "paging"    => [
                    "page"          => (int) $page,
                    "per_page"      => (int) $perPage,
                    "total_data"    => $totalData,
                ]
            ]
        ], REST_Controller::HTTP_OK);
    }
}

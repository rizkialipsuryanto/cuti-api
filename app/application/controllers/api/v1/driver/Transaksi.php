<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Transaksi extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            "VtrBooking_model"      => "vtrBooking",
            "Booking_model"         => "booking"
        ]);
    }

    public function list_get()
    {
        $id_driver              = $this->input->get("id_driver");
        $tanggal                = $this->input->get("tanggal");
        $page                   = $this->input->get("page")     ?: "1";
        $perPage                = $this->input->get("perpage")  ?: "10";

        $statusJemput           = $this->input->get("status_jemput");  //? MENUNGGU = 1 | DIJEMPUT = 2 | SELESAI = 3

        $this->validateEmpty($id_driver, "ID Driver");

        $kondisi["id_driver"]           = $id_driver;
        $kondisi["status_pembayaran"]   = SUDAH_VERIF;
        if ($statusJemput) {
            $kondisi["status_jemput"] = $statusJemput;
        }

        if ($tanggal) {
            $kondisi["tanggal"] = $tanggal;
        }

        $totalData              = $this->vtrBooking->where($kondisi)->count_rows();
        $data                   = $this->vtrBooking->where($kondisi)->order_by("id", "DESC")->limit($perPage, (($page - 1) * $perPage))->get_all() ?: [];

        if (!$data) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_OK,
                "message"       => "Data tidak ditemukan",
                "data"          => [
                    "items"     => $data,
                    "paging"    => [
                        "page"          => (int) $page,
                        "per_page"      => (int) $perPage,
                        "total_data"    => $totalData,
                    ]
                ]
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data ditemukan",
            "data"          => [
                "items"     => $data,
                "paging"    => [
                    "page"          => (int) $page,
                    "per_page"      => (int) $perPage,
                    "total_data"    => $totalData,
                ]
            ]
        ], REST_Controller::HTTP_OK);
    }

    public function jemput_post()
    {
        $idDriver       = $this->input->post("id_driver");
        $kodeBooking    = $this->input->post("kode_booking");

        $this->validateEmpty($idDriver, "ID Driver");
        $this->validateEmpty($kodeBooking, "Kode Booking");

        $cek            = $this->vtrBooking->where(["kode_booking" => $kodeBooking, "id_driver" => $idDriver])->get();
        if (!$cek) {
            return $this->response(array(
                "status"                => true,
                "code"                  => REST_Controller::HTTP_NOT_FOUND,
                "message"               => "Data tidak ditemukan",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $update     = $this->booking->where(["id" => $cek["id"]])->update(["status_jemput" => DIJEMPUT]);
        $data       = $this->vtrBooking->where(["id" => $cek["id"]])->get();
        if (!$update) {
            return $this->response(array(
                "status"                => true,
                "code"                  => REST_Controller::HTTP_BAD_GATEWAY,
                "message"               => "Terjadi kesalahan saat melakukan penjemputan",
                "data"                  => $data
            ), REST_Controller::HTTP_OK);
        }

        return $this->response(array(
            "status"                => true,
            "code"                  => REST_Controller::HTTP_OK,
            "message"               => "Penumpang berhasil dijemput",
            "data"                  => $data
        ), REST_Controller::HTTP_OK);
    }

    public function selesai_post()
    {
        $idDriver       = $this->input->post("id_driver");
        $kodeBooking    = $this->input->post("kode_booking");

        $this->validateEmpty($idDriver, "ID Driver");
        $this->validateEmpty($kodeBooking, "Kode Booking");

        $cek            = $this->vtrBooking->where(["kode_booking" => $kodeBooking, "id_driver" => $idDriver])->get();
        if (!$cek) {
            return $this->response(array(
                "status"                => true,
                "code"                  => REST_Controller::HTTP_NOT_FOUND,
                "message"               => "Data tidak ditemukan",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $update     = $this->booking->where(["id" => $cek["id"]])->update(["status_jemput" => SELESAI]);
        $data       = $this->vtrBooking->where(["id" => $cek["id"]])->get();
        if (!$update) {
            return $this->response(array(
                "status"                => true,
                "code"                  => REST_Controller::HTTP_BAD_GATEWAY,
                "message"               => "Terjadi kesalahan saat melakukan penjemputan",
                "data"                  => $data
            ), REST_Controller::HTTP_OK);
        }

        return $this->response(array(
            "status"                => true,
            "code"                  => REST_Controller::HTTP_OK,
            "message"               => "Penumpang berhasil diselesaikan",
            "data"                  => $data
        ), REST_Controller::HTTP_OK);
    }

    private function validateEmpty($input, $keterangan)
    {
        if (empty($input)) {
            return $this->response(array(
                "status"                => true,
                "code"                  => REST_Controller::HTTP_PRECONDITION_FAILED,
                "message"               => "$keterangan Tidak boleh kosong",
            ), REST_Controller::HTTP_OK);
        }
    }
}

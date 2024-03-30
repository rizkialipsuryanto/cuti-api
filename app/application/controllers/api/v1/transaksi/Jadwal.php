<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Jadwal extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            "User_model"            => "user",
            "Kota_model"            => "kota",
            "Jadwal_model"          => "jadwal",
            "Booking_model"         => "booking",
            "BookingDetail_model"   => "bookingDetail",
            "KursiView_model"       => "vKursi"
        ]);
    }

    public function kota_get()
    {
        $kota = $this->kota->get_all() ?: [];

        if (!$kota) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Data kota tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data kota ditemukan",
            "data"          => $kota
        ], REST_Controller::HTTP_OK);
    }

    public function cari_get()
    {
        $arrHari                    = ["senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "minggu"];
        $id_kota_origin             = $this->input->get("id_kota_origin");
        $id_kota_destination        = $this->input->get("id_kota_destination");
        $tanggal                    = $this->input->get("tanggal");


        if (!validateDate($tanggal)) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_PRECONDITION_FAILED,
                "message"       => "Tanggal booking tidak valid",
            ], REST_Controller::HTTP_OK);
        }

        if ($tanggal < date("Y-m-d")) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_PRECONDITION_FAILED,
                "message"       => "Tanggal booking tidak boleh kurang dari hari ini",
            ], REST_Controller::HTTP_OK);
        }

        $noHari             = date("N", strtotime($tanggal));
        $jadwal             = $this->jadwal
            ->where([
                "id_kota_origin"        => $id_kota_origin,
                "id_kota_destination"   => $id_kota_destination,
                "no_hari"               => $noHari,
                "status"                => "AKTIF",
            ])
            ->with_armada([
                "with"      => [
                    "relation"  => "driver",
                    "fields"    => "nama"
                ]
            ])
            ->with_kota_origin("fields:nama")
            ->with_kota_destination("fields:nama")
            ->get_all();

        if (!$jadwal) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Jadwal tidak ditemukan",
                "data"          => []
            ], REST_Controller::HTTP_OK);
        }



        for ($i = 0; $i < sizeof($jadwal); $i++) {
            $jadwal[$i]["waktu_origin"]         = date("H:i", strtotime($tanggal . " " . $jadwal[$i]["waktu_origin"]));
            $jadwal[$i]["waktu_destination"]    = date("H:i", strtotime($tanggal . " " . $jadwal[$i]["waktu_destination"]));
            $jadwal[$i]["hari"]                 = ucfirst($arrHari[$noHari - 1]);
            $jadwal[$i]["tanggal"]              = longdate_indo($tanggal);
            $jadwal[$i]["harga"]                = Rupiah2($jadwal[$i]["harga"]);

            $booked = $this->vKursi
                ->where([
                    "id_jadwal"     => $jadwal[$i]["id"],
                    "tanggal"       => $tanggal
                ])
                ->get_all() ?: [];

            $seatBooked = [];
            foreach ($booked as $b) {
                $seatBooked[]  = (int) $b["no_kursi"];
            }

            $totalSeat  = $jadwal[$i]["armada"]["kapasitas"] ?: 0;
            $seatData   = [];
            for ($s = 1; $s <= $totalSeat; $s++) {
                $isAvailable = !in_array($s, $seatBooked);
                $seatData[] = [
                    "id"            => $s,
                    "label"         => "Kursi $s",
                    "available"     => $isAvailable
                ];
            }

            $jadwal[$i]["armada"]["seats"]      = $seatData;
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Data jadwal ditemukan",
            "data"          => $jadwal
        ], REST_Controller::HTTP_OK);
    }
}

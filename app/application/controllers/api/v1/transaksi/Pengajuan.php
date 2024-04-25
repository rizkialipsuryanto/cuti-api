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
            "RiwayatView_model"         => "riwayat",
            "MasterCuti_model"       => "mastercuti"
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

    public function insertPengajuan_post()
    {
        // $no_cuti        = $this->generateKodeBooking();
        $no_cuti        = $this->input->post("no_cuti");
        $uid            = $this->input->post("uid");
        $tglambilcuti          = $this->input->post("tglambilcuti");
        $tgl_pengajuan            = date("Y-m-d H:i:s");
        // $durasi         = $this->input->post("durasi");
        $keterangan         = $this->input->post("keterangan");
        $alamatcuti            = $this->input->post("alamatcuti");
        $atasan_langsung          = $this->input->post("atasan_langsung");
        $kepala_instalasi            = $this->input->post("kepala_instalasi");
        $kordinator_karu         = $this->input->post("kordinator_karu");
        $jeniscuti         = $this->input->post("jeniscuti");
        $created_at   = date("Y-m-d H:i:s");

        //menghitung durasi
        $array_data = explode(',', $tglambilcuti); // Pisahkan data berdasarkan koma
        $durasi = count($array_data); // Hitung jumlah data dalam array

        //! DETAIL
        // $no_identitas       = $this->input->post("no_identitas");
        // $jenis_identitas    = $this->input->post("jenis_identitas");
        // $no_kursi           = $this->input->post("no_kursi");
        // $nama_penumpang     = $this->input->post("nama_penumpang");

        $cekCuti           = $this->mastercuti
            ->where([
                "uid"     => $uid,
                "tahun"       => date("Y")
                // "sisa_cuti"      => $sisa_cuti
            ])
            ->get();
        
        // var_dump($cekCuti);
        // die();
        if ($cekCuti['sisa_cuti'] == '0' || $cekCuti['sisa_cuti'] < $durasi) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Cuti Sudah Habis, Anda tidak bisa mengajukan !!!",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        //TODO : INSERT
        $dataInsert     = [
            // "no_cuti"      => $no_cuti,
            "uid"           => $uid,
            "tglambilcuti"         => $tglambilcuti,
            "tgl_pengajuan"           => $tgl_pengajuan,
            "durasi"        => $durasi,
            "keterangan"        => $keterangan,
            "alamatcuti"     => $alamatcuti,
            "atasan_langsung" => $atasan_langsung,
            "kepala_instalasi"  => $kepala_instalasi,
            "kordinator_karu"      => $kordinator_karu,
            "stt_cuti"           => 1,
            "jeniscuti"         => $jeniscuti,
            "created_at"           => $created_at
        ];

        $insertPengajuan          = $this->booking->insert($dataInsert);
        if (!$dataInsert) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Terjadi kesalahan saat melakukan proses Cuti. Kode (R001)",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        //! TODO : INSERT DETAIL
        // $dataDetail             = [
        //     "id_booking"        => $insertBooking,
        //     "no_identitas"      => $no_identitas,
        //     "jenis_identitas"   => $jenis_identitas,
        //     "no_kursi"          => $no_kursi,
        //     "harga"             => $_jadwal["harga"],
        //     "nama_penumpang"    => $nama_penumpang,
        // ];

        // $insertDetail           = $this->bookingDetail->insert($dataDetail);
        // if (!$insertDetail) {
        //     return $this->response([
        //         "status"        => true,
        //         "code"          => REST_Controller::HTTP_BAD_REQUEST,
        //         "message"       => "Terjadi kesalahan saat melakukan proses booking. Kode (R002)",
        //         "data"          => NULL
        //     ], REST_Controller::HTTP_OK);
        // }

        
        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Pengajuan telah berhasil",
            "data"          => $dataInsert
        ], REST_Controller::HTTP_OK);
    }
}

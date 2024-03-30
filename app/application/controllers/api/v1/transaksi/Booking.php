<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Booking extends REST_Controller
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

    private function generateKodeBooking()
    {
        $kode   = generator(5);
        $cek    = $this->booking->where(["kode_booking" => $kode])->get();
        if ($cek) {
            return $this->generateKodeBooking();
        }
        return $kode;
    }

    public function index_post()
    {
        $kodeBooking        = $this->generateKodeBooking();
        $id_user            = $this->input->post("id_user");
        $id_jadwal          = $this->input->post("id_jadwal");
        $tanggal            = $this->input->post("tanggal");
        $lat_jemput         = $this->input->post("lat_jemput");
        $lng_jemput         = $this->input->post("lng_jemput");
        $status_jemput      = MENUNGGU;
        $status_pembayaran  = MENUNGGU;
        $batas_pembayaran   = date("Y-m-d H:i:s", strtotime('+5 hours'));

        //! DETAIL
        $no_identitas       = $this->input->post("no_identitas");
        $jenis_identitas    = $this->input->post("jenis_identitas");
        $no_kursi           = $this->input->post("no_kursi");
        $nama_penumpang     = $this->input->post("nama_penumpang");

        if (!validateDate($tanggal)) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Format tanggal booking tidak dikenali",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        $config = [
            [
                'field' => 'id_user',
                'label' => 'User',
                'rules' => 'required|trim'
            ],
            [
                'field' => 'id_jadwal',
                'label' => 'Jadwal',
                'rules' => 'required|trim'
            ],
            [
                'field' => 'tanggal',
                'label' => 'Tanggal',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'lat_jemput',
                'label' => 'Latitude Penjemputan',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'lng_jemput',
                'label' => 'Longitude Penjemputan',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'no_identitas',
                'label' => 'Nomor Identitas',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'jenis_identitas',
                'label' => 'Jenis Identitas',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'no_kursi',
                'label' => 'No Kursi',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
            [
                'field' => 'nama_penumpang',
                'label' => 'Nama Penumpang',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi'
                ]
            ],
        ];

        $this->form_validation->set_rules($config);

        if (!$this->form_validation->run()) {
            $errorArray     = $this->form_validation->error_array();
            $dataError      = "";
            $i              = 0;
            $numItems       = count($errorArray);
            foreach ($errorArray as $e) {
                $dataError .= $e;
                if (++$i !== $numItems) {
                    $dataError .= "\n";
                }
            }
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => $dataError,
            ], REST_Controller::HTTP_OK);
        }

        $_user              = $this->user->where(["id" => $id_user])->get();
        if (!$_user) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "User tidak dikenali",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        $_jadwal            = $this->jadwal->with_armada()->where(["id" => $id_jadwal])->get();
        if (!$_jadwal) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Jadwal tidak ditemukan",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        $totalSeat          = isset($_jadwal["armada"]) ? $_jadwal["armada"]["kapasitas"] : "0";
        if ($no_kursi < 0 || $no_kursi > $totalSeat) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "No Kursi tidak dikenali. Silahkan isi sesuai no kursi yang ada",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        $cekKursi           = $this->vKursi
            ->where([
                "id_jadwal"     => $_jadwal["id"],
                "tanggal"       => $tanggal,
                "no_kursi"      => $no_kursi
            ])
            ->get();

        if ($cekKursi) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Kursi nomor $no_kursi sudah dibooking. Silahkan pilih no kursi yang lain",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        //TODO : INSERT
        $dataInsert     = [
            "kode_booking"      => $kodeBooking,
            "id_user"           => $_user["id"],
            "id_jadwal"         => $_jadwal["id"],
            "tanggal"           => $tanggal,
            "lat_jemput"        => $lat_jemput,
            "lng_jemput"        => $lng_jemput,
            "status_jemput"     => $status_jemput,
            "status_pembayaran" => $status_pembayaran,
            "batas_pembayaran"  => $batas_pembayaran
        ];

        $insertBooking          = $this->booking->insert($dataInsert);
        if (!$dataInsert) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Terjadi kesalahan saat melakukan proses booking. Kode (R001)",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        //! TODO : INSERT DETAIL
        $dataDetail             = [
            "id_booking"        => $insertBooking,
            "no_identitas"      => $no_identitas,
            "jenis_identitas"   => $jenis_identitas,
            "no_kursi"          => $no_kursi,
            "harga"             => $_jadwal["harga"],
            "nama_penumpang"    => $nama_penumpang,
        ];

        $insertDetail           = $this->bookingDetail->insert($dataDetail);
        if (!$insertDetail) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Terjadi kesalahan saat melakukan proses booking. Kode (R002)",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        // $detailBooking      = $this->booking->where(["id" => $dataDetail["id_booking"]])->get();
        $detailBooking      = $this->booking
            ->with_user("fields:nama,nohp")
            ->with_detail()
            ->with_jadwal([
                "with"      => [
                    [
                        "relation"  => "armada",
                        "fields"    => "nopol,merk,kapasitas,bbm,warna",
                        "with"      => [
                            "relation"  => "driver",
                            "fields"    => "nama,jenis_kelamin"
                        ]
                    ],
                    [
                        "relation"  => "kota_origin",
                        "fields"    => "nama"
                    ],
                    [
                        "relation"  => "kota_destination",
                        "fields"    => "nama"
                    ],
                ],
            ])
            ->where(["id" => $dataDetail["id_booking"]])
            ->get();


        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Booking telah berhasil",
            "data"          => $detailBooking
        ], REST_Controller::HTTP_OK);
    }

    public function list_get()
    {
        $id_user        = $this->input->get("id_user");
        $page           = $this->input->get("page")     ?: "1";
        $perPage        = $this->input->get("perpage")  ?: "10";

        $this->validateEmpty($id_user, "ID User");

        $_user          = $this->user->where(["id" => $id_user])->get();
        if (!$_user) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Terjadi kesalahan. User tidak diketahui. Silahkan coba kembali !",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $totalData  = $this->booking
            ->where(["id_user" => $_user["id"]])
            ->count_rows();

        $detailBooking      = $this->booking
            ->with_user("fields:nama,nohp")
            ->with_detail()
            ->with_jadwal([
                "with"      => [
                    [
                        "relation"  => "armada",
                        "fields"    => "nopol,merk,kapasitas,bbm,warna",
                        "with"      => [
                            "relation"  => "driver",
                            "fields"    => "nama,jenis_kelamin"
                        ]
                    ],
                    [
                        "relation"  => "kota_origin",
                        "fields"    => "nama"
                    ],
                    [
                        "relation"  => "kota_destination",
                        "fields"    => "nama"
                    ],

                ],
            ])
            ->where(["id_user" => $_user["id"]])
            ->order_by("id", "DESC")
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

    public function cancel_post()
    {
        $id_user        = $this->input->post("id_user");
        $kode_booking   = $this->input->post("kode_booking");
        $alasan         = $this->input->post("alasan");

        $this->validateEmpty($id_user,      "ID User");
        $this->validateEmpty($kode_booking, "Kode Booking");
        $this->validateEmpty($alasan,       "Alasan pembatalan");

        $_user          = $this->user->where(["id" => $id_user])->get();
        if (!$_user) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Terjadi kesalahan. User tidak diketahui. Silahkan coba kembali !",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $_booking       = $this->booking->where(["kode_booking" => $kode_booking])->get();
        if (!$_booking) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Terjadi kesalahan. Data booking tidak diketahui. Silahkan coba kembali !",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        if ($_booking["id_user"] != $_user["id"]) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Anda tidak memiliki akses untuk membatalkan booking ini !",
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        if ($_booking["status_pembayaran"] != MENUNGGU) {
            return $this->response(array(
                "status"                => true,
                "response_code"         => REST_Controller::HTTP_PRECONDITION_FAILED,
                "response_message"      => "Tidak dapat melakukan pembatalan pemesanan ini, karena status booking ini : " . $_booking["status_pembayaran"],
                "data"                  => NULL
            ), REST_Controller::HTTP_OK);
        }

        $update     = $this->booking
            ->where(["id" => $_booking["id"]])
            ->update([
                "status_pembayaran" => "BATAL",
                "status_jemput"     => "BATAL",
                "status_keterangan" => $alasan
            ]);

        if (!$update) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Terjadi kesalahan saat melakukan pembatalan booking. Kode (R002)",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        //TODO : RESET KURSI
        $this->bookingDetail->where(["id_booking" => $_booking["id"]])->update(["no_kursi" => "-1"]);
        $detailBooking      = $this->booking
            ->with_user("fields:nama,nohp")
            ->with_detail()
            ->with_jadwal([
                "with"      => [
                    [
                        "relation"  => "armada",
                        "fields"    => "nopol,merk,kapasitas,bbm,warna",
                        "with"      => [
                            "relation"  => "driver",
                            "fields"    => "nama,jenis_kelamin"
                        ]
                    ],
                    [
                        "relation"  => "kota_origin",
                        "fields"    => "nama"
                    ],
                    [
                        "relation"  => "kota_destination",
                        "fields"    => "nama"
                    ],
                ],
            ])
            ->where(["id" => $_booking["id"]])
            ->get();


        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Berhasil melakukan pembatalan booking",
            "data"          => $detailBooking
        ], REST_Controller::HTTP_OK);
    }
}

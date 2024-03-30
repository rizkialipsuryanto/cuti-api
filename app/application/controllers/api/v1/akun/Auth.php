<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Auth extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            "User_model"            => "user"
        ]);
    }

    public function index_get()
    {
        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "OK",
            "data"          => $this->user->get_all()
        ], REST_Controller::HTTP_OK);
    }

    public function register_post()
    {
        $username       = $this->input->post("username");
        $password       = md5($this->input->post("password") ?: "");
        $nama           = $this->input->post("nama");
        $jenisKelamin   = strtoupper($this->input->post("jenis_kelamin"));
        $noHp           = $this->input->post("no_hp");
        $level          = "USER";

        $config = [
            [
                'field' => 'nama',
                'label' => 'Nama',
                'rules' => 'required|trim'
            ],
            [
                'field' => 'jenis_kelamin',
                'label' => 'Jenis Kelamin',
                'rules' => 'required|trim|in_list[LAKI-LAKI,PEREMPUAN]',
                'errors' => [
                    'in_list' => 'Kolom {field} harus berisi salah satu dari {param}'
                ]
            ],
            [
                'field' => 'username',
                'label' => 'Username',
                'rules' => 'required|trim|is_unique[m_user.username]'
            ],
            [
                'field' => 'no_hp',
                'label' => 'No HP',
                'rules' => 'required|trim|numeric|min_length[10]|max_length[13]'
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|trim|min_length[6]'
            ],
            [
                'field' => 'konfirmasi_password',
                'label' => 'Konfirmasi Password',
                'rules' => 'required|trim|min_length[6]|matches[password]'
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

        $simpan = $this->user->insert([
            "username"      => $username,
            "password"      => $password,
            "nama"          => $nama,
            "jenis_kelamin" => $jenisKelamin,
            "nohp"          => $noHp,
            "level"         => $level
        ]);

        if (!$simpan) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_BAD_REQUEST,
                "message"       => "Pendaftaran gagal, Silahkan coba beberapa saat lagi. (Kode Error : R001)",
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Pendaftaran berhasil, silahkan masuk menggunakan username yang di daftarkan",
        ], REST_Controller::HTTP_OK);
    }

    public function login_post()
    {
        $username       = $this->input->post("username", true);
        // $password       = md5($this->input->post("password", true));
        $password       = $this->input->post("password", true);

        $config = [
            [
                'field' => 'username',
                'label' => 'Username',
                'rules' => 'required|trim'
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|trim'
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

        $user           = $this->user
            ->where([
                "username"      => $username,
                "password"      => $password
            ])
            ->get();

        if (!$user) {
            return $this->response([
                "status"        => true,
                "code"          => REST_Controller::HTTP_NOT_FOUND,
                "message"       => "Username atau password yang kamu masukan salah!",
                "data"          => NULL
            ], REST_Controller::HTTP_OK);
        }

        return $this->response([
            "status"        => true,
            "code"          => REST_Controller::HTTP_OK,
            "message"       => "Selamat datang kembali, " . $user["nama_emp"],
            "data"          => $user
        ], REST_Controller::HTTP_OK);
    }
}

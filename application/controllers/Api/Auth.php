<?php

defined('BASEPATH') or exit('No direct script access allowed');
// require_once APPPATH . '/third_party/REST/RestController.php';
class Auth extends CI_Controller
{



    public function __construct()
    {
        parent::__construct();
        // $this->load->model('m_product');
        $this->load->model('M_login');
        $this->load->library('JWTToken');
        $this->load->model('Global_data');

        // $this->data['set'] = $this->setting->getSetting("semua");
    }

    public function login()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $username = "";
        $password = "";
        // Check if username and password exist in the $data array
        if (isset($data['email'], $data['password'])) {
            $username = $data['email'];
            $password = $data['password'];
        }

        $user = $this->M_login->cek_user_by_email($username);
        $jwt_token = new JWTToken();
        if ($user->num_rows() == 0) {
            $response = array(
                "success" => false,
                "token" => $this->security->get_csrf_hash()
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
        $pass = null;
        $aktif = false;
        $res = null; // Inisialisasi variabel $res
        foreach ($user->result() as $row) {
            $res = $row; // Assign $res to $row
            $pass = $this->M_login->decode($row->password);
            $aktif = ($row->status == 0) ? false : true;
        }
        if ($password == $pass) {
            // Generate JWT token
            $token = $jwt_token->generate_token(array(
                'uid' => $res->id,
                'email' => $res->username,
                'gid' => $res->level,
                'nohp' => $res->nohp,
                'status' => $res->status
            ));

            // Set Session data
            $sessionLogin = array(
                'uid'    => $res->id,
                'gid'    => $res->level,
                'status' => $res->status,
            );
            $this->session->set_userdata($sessionLogin);

            $response = array(
                "success" => true,
                "redirect" => base_url(),
                "token" => $token,
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            $response = array(
                "success" => false,
                "redirect" => "",
                "msg" => "Terjadi Kesalahan Pada Akun Username " . $username . " dan Password " . $password,
                "token" => "",
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }


    public function register()
    {
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

        $user = $this->M_login->cek_user_by_email($input["email"]);
        if (!empty($user->row())) {
            $response = array(
                "success" => false,
                "result" => "Email sudah terdaftar !",
            );
        } else {
            $data = array(
                "username" => $input["email"],
                "nohp"       => $input["nohp"],
                "password" => $this->M_login->encode($input["password"]),
                "level"    => 1,
                "status"   => 1
            );
            $usrid = $this->M_login->register($data);
            $data2 = array(
                "usrid" => $usrid,
                "nama"       => $input["nama"],
                "nohp"       => $input["nohp"],
                "lahir"       => $input["lahir"],
                "kelamin"       => $input["kelamin"],
                "foto"       => "user.png",
            );
            $query = $this->M_login->register_profile($data2);
            $response = array(
                "success" => true,
                "result" => "Akun Anda Berhasil Terdaftar",
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

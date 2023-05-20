<?php
class Profile extends CI_Controller
{

    private $jwt_token;
    protected $accountType = 'https://pro.rajaongkir.com/api';
    protected $apiKey;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Global_data');
        $this->load->model("M_order");
        $this->load->model('M_profile');
        $this->load->library('JWTToken');
        $this->jwt_token = new JWTToken();
        // Cek apakah ada header Authorization pada request
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header) {
            // Jika header Authorization tidak ada, tampilkan response error
            $response = array(
                "success" => false,
                "message" => "Authorization header not found",
                "status" => 401
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            exit;
        }
        // Cek validitas token JWT
        $token = str_replace('Bearer ', '', $header);
        $payload = $this->jwt_token->validate_token($token);
        if (!$payload) {
            // Jika token tidak valid, tampilkan response error
            $response = array(
                "success" => false,
                "message" => "Invalid token",
                "status" => 401
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            exit;
        }
        // Simpan data user dari payload ke dalam session atau variabel
        $this->session->set_userdata('user', $payload);
        $this->session->set_userdata('user_id', $payload["uid"]);
    }



    public function index()
    {
        $userid = $this->session->userdata('user_id');
        $value = $this->M_profile->getprofile($userid);
        if (!empty($value)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($value));
        } else {
            $respon = array(
                "success" => false,
                "message" => "Invalid token",
                "status" => 401
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($respon));
        }
    }

    public function getprovinces()
    {
        $idProv = $this->input->get('id');
        $this->apiKey = $this->Global_data->globalset("semua")->rajaongkir;
        $curl = curl_init();
        if ($idProv == null) {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->accountType . "/province",
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: $this->apiKey"
                ),
            ));
        } else {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->accountType . "/province?id=" . $idProv,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: $this->apiKey"
                ),
            ));
        }
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // $respon = array(
            //     'provinsi' => $response['rajaongkir']['results'],
            // );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            // echo $response;
            // return json_encode($response, true);
        }
    }


    public function getkabupaten()
    {
        $curl = curl_init();
        $idProvince = $this->input->get('provinsi');
        $this->apiKey = $this->Global_data->globalset("semua")->rajaongkir;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->accountType . "/city?province=" . $idProvince,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: $this->apiKey"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // return json_decode($response, true);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    function getkecamatan()
    {
        $idCity = $this->input->get('city');
        $this->apiKey = $this->Global_data->globalset("semua")->rajaongkir;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->accountType . "/subdistrict?city=" . $idCity,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: $this->apiKey"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // return json_decode($response, true);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    public function getcity()
    {
        $idCity = $this->input->get('id');
        $this->apiKey = $this->Global_data->globalset("semua")->rajaongkir;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->accountType . "/city?id=" . $idCity,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: $this->apiKey"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // return json_decode($response, true);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    public function register_alamat()
    {
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);
        $data = array(
            "usrid"   => $input["uid"],
            "idkab"   => $input["idkab"],
            "idkec"   => $input["idkec"],
            "nama"    => $input["nama"],
            "judul"   => $input["judul"],
            "alamat"  => $input["alamat"],
            "kodepos" => $input["kodepos"],
            "nohp"    => $input["nohp"],
            "status"  => $input["status"]
        );
        $cekdata = $this->M_profile->baca_alamat($input["uid"]);
        if (!empty($cekdata)) {
            $data = array(
                "idkab"   => $input["idkab"],
                "idkec"   => $input["idkec"],
                "nama"    => $input["nama"],
                "judul"   => $input["judul"],
                "alamat"  => $input["alamat"],
                "kodepos" => $input["kodepos"],
                "nohp"    => $input["nohp"],
                "status"  => $input["status"]
            );
            $id = $cekdata->id;
            $query = $this->M_profile->update_alamat($data, $id);
            $response = array(
                "success" => true,
                "message" => "Update successfully",
            );
        } else {
            $query = $this->M_profile->register_alamat($data);
            $response = array(
                "success" => true,
                "message" => "Update successfully",
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

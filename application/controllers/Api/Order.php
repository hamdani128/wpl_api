<?php
class Order extends CI_Controller
{

    private $jwt_token;
    protected $accountType = 'https://pro.rajaongkir.com/api';
    protected $apiKey;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("M_order");
        $this->load->model('Global_data');
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
        $this->apiKey = $this->Global_data->globalset("semua")->rajaongkir;
    }

    public function cart()
    {
        $userid = $this->input->get("userid");
        $order = $this->M_order->show_cart($userid);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($order));
    }


    public function add_cart()
    {

        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

        $data = array(
            "usrid"       => $input["uid"],
            "idproduk"    => $input["idproduk"],
            "tgl"         => date("Y-m-d H:i:s"),
            "jumlah"      => $input["jumlah"],
            "harga"       => $input["harga"],
            "keterangan"  => $input["keterangan"],
            "variasi"     => $input["variasi"],
            "idtransaksi" => 0,
        );

        $query = $this->M_order->add_cart($data);
        if ($query) {
            $response = array(
                "success" => true,
                "message" => "Inserted successfully",
            );
        } else {
            $response = array(
                "success" => false,
                "message" => " Error inserting ",
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function delete_cart($id)
    {
        $query = $this->M_order->delete_cart($id);
        if ($query) {
            $response = array(
                "success" => true,
                "message" => "Deleted successfully",
            );
        } else {
            $response = array(
                "success" => false,
                "message" => " Error Deleted ",
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function kurir()
    {
        $kur = array(
            'jne' => 'JNE',
            'sicepat' => 'SiCepat Express',
            'gosend' => 'Gosen/Grab Express'
        );
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($kur));
    }





    // getCost Raja Ongkir
    // public function getcost($from, $destination, $weight, $courier)
    public function getcost()
    {
        $json = file_get_contents('php://input');
        $inputan = json_decode($json, true);
        $from = $inputan['dari_idkb'];
        $destination = $inputan['tujuan_idkab'];
        $weight = $inputan['berat_total'];
        $courier = $inputan['kurir'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->accountType . "/cost",
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $from . "&originType=city&destination=" . $destination . "&destinationType=city&weight=" . $weight . "&courier=" . $courier,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: $this->apiKey"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo json_encode($response, true);
        }
    }

    // Definisi Detail Raja Ongkir
    private function check_request_ongkir($dari, $berat, $tujuan, $kurir, $service, $hargapaket)
    {
        $usrid = $this->session->userdata('user_id');
        $beratkg = $this->Global_data->beratkg($berat, $kurir);
        $beratkg = $beratkg < 1 ? 1 : $beratkg;
        $ongkir = $this->getcost($dari, $tujuan, $berat, $kurir);
        $hasil = array(
            "success" => false,
            "response" => "daerah tidak terjangkau!",
            "message" => "service code tidak ada data",
            "harga" => 0,
            "token" => $this->security->get_csrf_hash()
        );

        if ($ongkir['rajaongkir']['status']['code'] == 200) {
            for ($i = 0; $i < count($ongkir['rajaongkir']['results'][0]['costs']); $i++) {
                $harga = $hargapaket;
                $hargaperkg = $harga / $beratkg;
                // $service = $ongkir['rajaongkir']['results'][0]['costs'][$i]['service'];
                $array = array(
                    "dari"        => $dari,
                    "tujuan"    => $tujuan,
                    "kurir"        => $kurir,
                    "service"    => $service,
                    "harga"        => $harga,
                    "update"    => date("Y-m-d H:i:s"),
                    "usrid"        => $usrid
                );

                $idhistory = $this->Global_data->getHistoryOngkir(array(
                    "dari" => $dari,
                    "tujuan" => $tujuan,
                    "kurir" => $kurir,
                    "service" => $service
                ), "id");
                if ($idhistory > 0) {
                    $this->db->where("id", $idhistory);
                    $this->db->update("history_ongkir", $array);
                } else {
                    if ($hargaperkg > 0) {
                        $this->db->insert("history_ongkir", $array);
                    }
                }
                $hasil = array(
                    "success"    => true,
                    "dari"        => $dari,
                    "tujuan"    => $tujuan,
                    "kurir"        => $kurir,
                    "service"    => $service,
                    "harga"        => $harga,
                    "update"    => date("Y-m-d H:i:s"),
                    "hargaperkg" => $hargaperkg,
                    "token" => $this->security->get_csrf_hash()
                );
            }
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($hasil));
        // echo json_encode($hasil);
    }

    public function ongkir()
    {
        $json = file_get_contents('php://input');
        $inputan = json_decode($json, true);
        $daris = $inputan["dari"];
        $tujuan = $inputan["tujuan"];
        $berat =  $inputan["berat"];
        $hargapaket =  $inputan["hargapaket"];
        $berat = ($berat == 0) ? 1000 : $berat;
        $kurir = $inputan["kurir"] ? $inputan["kurir"] : "jne";

        $srvdefault = "";
        $service = $inputan["service"] ? $inputan["service"] : $srvdefault;

        $this->db->where("dari", $daris);
        $this->db->where("tujuan", $tujuan);
        $this->db->where("kurir", $kurir);
        $this->db->limit(1);
        $this->db->order_by("id", "DESC");
        $results = $this->db->get("history_ongkir");
        if ($results->num_rows() > 0) {
            foreach ($results->result() as $res) {
                $this->check_request_ongkir($daris, $berat, $tujuan, $kurir, $service, $hargapaket);
                $just = true;
            }
            if (!isset($just)) {
                $this->check_request_ongkir($daris, $berat, $tujuan, $kurir, $service, $hargapaket);
            }
        } else {
            $this->check_request_ongkir($daris, $berat, $tujuan, $kurir, $service, $hargapaket);
        }
    }
}

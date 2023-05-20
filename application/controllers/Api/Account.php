<?php

class Account extends CI_Controller
{
    private $jwt_token;
    protected $iduser;
    public function __construct()
    {
        parent::__construct();
        $this->load->model("M_order");
        $this->load->model("Global_data");
        $this->load->model("M_profile");
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

    public function order()
    {
        $userid = $this->session->userdata('user_id');
        $data = $this->M_order->getInfoInvoice($userid);
        $profile = $this->M_profile->getprofile($userid);
        if (!empty($data)) {
            $rowData = array(
                'profile' => $profile,
                'transaksi' => $data,
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($rowData));
        } else {
            $response = array(
                "success" => false,
                "message" => "Data Not Found",
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    public function transaction_delete($id_transaction)
    {
        $data_id = $this->M_order->get_row_transaction($id_transaction)->id;
        $query1 = $this->M_order->delete_blw_invoice($id_transaction);
        $query2 = $this->M_order->delete_blw_sales($data_id);
        $query3 = $this->M_order->delete_blw_product($data_id);
        if ($query1 && $query2 && $query3) {
            $response = array(
                'success' => true,
                'message' => 'Delete Transaction Successfully'
            );
        } else {
            $response = array(
                'sucess' => false,
                'message' => 'Delete Transaction Failure',
            );
        }
        $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function complete_payment()
    {
        $userid = $this->session->userdata('user_id');
        $data = $this->M_order->invoice_complete($userid);
        if (!empty($data)) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
        } else {
            $response = array(
                "success" => false,
                "message" => "Data Not Found",
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    public function expired_payment()
    {
        $userid = $this->session->userdata('user_id');
        $data = $this->M_order->invoice_expired($userid);
        if (!empty($data)) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
        } else {
            $response = array(
                "success" => false,
                "message" => "Data Not Found",
            );
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }


    public function delete($id_account)
    {
        $table_invoice = $this->db->where('usrid', $id_account)->delete("invoice");
        $table_sales = $this->db->where('usrid', $id_account)->delete("sales");
        $table_alamat = $this->db->where('usrid', $id_account)->delete("user_alamat");
        $table_profil = $this->db->where('usrid', $id_account)->delete("user_profil");
        $table_user = $this->db->where('id', $id_account)->delete("user");

        if ($table_invoice && $table_sales && $table_alamat && $table_profil && $table_user) {
            $response = [
                "success" => true,
                "message" => "good luck success!",
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "Data Not Found !",
            ];
        }
        $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

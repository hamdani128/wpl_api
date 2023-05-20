<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->automaticSystemUpdateForCronJobs();
    }

    public function automaticSystemUpdateForCronJobs()
    {
        $this->load->model('Global_data');
        // BATALKAN PEMBAYARAN
        $this->db->where("status", 0);
        $this->db->where("kadaluarsa <", date("Y-m-d H:i:s"));
        $db = $this->db->get("invoice");
        foreach ($db->result() as $r) {
            // UPDATE TRANSAKSI
            $this->Global_data->updateTransaksi(
                ["idbayar" => $r->id],
                array(
                    "status" => 4,
                    "selesai" => date("Y-m-d H:i:s"),
                    "keterangan" => "dibatalkan oleh sistem, karena melewati batas waktu pembayaran"
                )
            );
        }

        // UPDATE PEMBAYARAN
        $this->db->where("status", 0);
        $this->db->where("kadaluarsa <", date("Y-m-d H:i:s"));
        $this->db->update("invoice", array("status" => 3, "tglupdate" => date("Y-m-d H:i:s")));
    }
}

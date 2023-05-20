<?php

use Xendit\Invoice;
use Xendit\Xendit;

class Payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Global_data");
    }

    public function callback($invoice)
    {
        require_once FCPATH . 'vendor/autoload.php';
        Xendit::setApiKey("xnd_production_35MgOPYqsrz0VIwkBZNf28GxiUXVk6tXkLEpPTH8CFYYX1JxXGQyFtjFBSqIX");
        // Get Pembayaran by Order ID
        $bayar = $this->Global_data->getBayar($invoice, "semua", 'invoice');
        // echo $bayar->xendit_id;
        // Get Invoice from xendit official
        // $getInvoice = \Xendit\Invoice::retrieve($bayar->xendit_id);
        $getInvoice = Invoice::retrieve($invoice);
        // echo $getInvoice['status'];
        if ($getInvoice['status'] == 'SETTLED') {
            $this->db->where("id", $bayar->id);
            $this->db->update("invoice", ["status" => 1]);
            // SETTLED TRANSAKSI
            $this->Global_data->updateTransaksi($bayar->id, ["status" => 1]);
        } else if ($getInvoice['status'] == 'PAID') {
            $this->db->where("id", $bayar->id);
            $this->db->update("invoice", ["status" => 1]);
            // PAID TRANSAKSI
            $this->Global_data->updateTransaksi($bayar->id, ["status" => 1]);
        } else if ($getInvoice['status'] == 'PENDING') {
            $this->db->where("id", $bayar->id);
            $this->db->update("invoice", ["status" => 0]);
            // PENDING TRANSAKSI
            $this->Global_data->updateTransaksi($bayar->id, ["status" => 0]);
        } else if ($getInvoice['status'] == 'EXPIRED') {
            $this->db->where("id", $bayar->id);
            $this->db->update("invoice", ["status" => 3]);
            // EXPIRED TRANSAKSI
            $this->Global_data->updateTransaksi($bayar->id, ["status" => 4]);
        }
    }
}

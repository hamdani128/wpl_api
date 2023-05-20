<?php

use Xendit\Invoice;
use Xendit\Xendit;

class M_order extends CI_Model
{
    private $tabel_sale_produk      = "blw_sales_produk";
    private $tabel_voucher          = "sales_voucher";
    private $tabel_saldo            = "user_saldo";
    private $tabel_saldo_history    = "user_saldo_history";
    private $tabel_sale             = "sales";
    private $tabel_variasi          = "produk_variasi";
    private $tabel_produk           = "produk";
    private $tabel_user             = "user";
    private $tabel_profile          = "blw_user_profil";


    public function cek_user_by_email($email)
    {
        $this->db->where("username", $email);
        $this->db->limit(1);
        $user = $this->db->get($this->tabel_user);
        return $user;
    }

    public function show_cart($usrid)
    {
        $SQL = "SELECT
                a.id as id,
                a.usrid as usrid,
                a.variasi as variasi,
                a.idproduk as idproduk,
                b.idcat as idcat,
                b.nama as nama,
                b.berat as berat,
                a.tgl as tgl,
                a.jumlah as jumlah,
                a.harga as harga,
                a.diskon as diskon,
                a.keterangan as keterangan,
                a.idtransaksi as idtransaksi,
                a.idpo as idpo
                FROM blw_sales_produk a
                LEFT JOIN blw_produk b ON a.idproduk = b.id 
                WHERE a.usrid='" . $usrid . "' AND idtransaksi='0'";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            $totalberat = 0;
            $total_harga = 0;
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = $row->id;
                $tempArray['usrid'] = $row->usrid;
                $tempArray['variasi'] = $row->variasi;
                $tempArray['idproduk'] = $row->idproduk;
                $tempArray['idcat'] = $row->idcat;
                $tempArray['nama'] = $row->nama;
                $tempArray['berat'] = $row->berat;
                $tempArray['tgl'] = $row->tgl;
                $tempArray['jumlah'] = $row->jumlah;
                $tempArray['harga'] = $row->harga;
                $tempArray['diskon'] = $row->diskon;
                $tempArray['keterangan'] = $row->keterangan;
                $tempArray['idtransaksi'] = $row->idtransaksi;
                $tempArray['idpo'] = $row->idpo;
                $totalberat = $totalberat + $row->berat;
                $total_harga = $total_harga + $row->harga;
                $response['data'][] = $tempArray;
                $response['totalberat'] = $totalberat;
                $response['totalharga'] = $total_harga;
            }
            return $response;
        }
    }

    public function add_cart($data)
    {
        return $this->db->insert($this->tabel_sale_produk, $data);
    }

    public function delete_cart($id)
    {
        return $this->db->delete($this->tabel_sale_produk, array('id' => $id));
    }

    public function getvoucher($id, $what, $opo = "id")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get($this->tabel_voucher);

        if ($what == "semua") {
            if ($res->num_rows() == 0) {
                $result = array(0);
            }
            foreach ($res->result() as $key => $value) {
                $result[$key] = $value;
            }
            $result = $result[0];
        } else {
            $result = 0;
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        }
        return $result;
    }
    function getVoucherActive()
    {
        $this->db->where("mulai <=", date("Y-m-d"));
        $this->db->where("selesai >=", date("Y-m-d"));
        return $this->db->get($this->tabel_voucher);
    }

    function getSaldo($id, $what, $opo = "id")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get($this->tabel_saldo);

        if ($what == "semua") {
            $result = array();
            foreach ($res->result() as $key => $value) {
                $result[$key] = $value;
            }
            $result = $result[0];
        } else {
            $result = 0;
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        }
        return $result;
    }
    public function updateSaldo($id, $data)
    {
        $this->db->where("usrid", $id);
        $this->db->update($this->tabel_saldo, $data);
    }
    public function saveSaldoHistory($data)
    {
        $this->db->insert($this->tabel_saldo_history, $data);
    }
    public function saveTransaksi($data)
    {
        $this->db->insert($this->tabel_sale, $data);
        return $this->db->insert_id();
    }

    public function getDetailSale($uid)
    {
        $SQL = "SELECT * FROM " . $this->tabel_sale_produk . " WHERE usrid='" . $uid . "' AND idtransaksi='0'";
        return $this->db->query($SQL)->result();
    }

    public function updateSale($id, $data)
    {
        $this->db->where("id", $id);
        $this->db->update($this->tabel_sale_produk, $data);
    }
    function getSaleByTransaksi($idtransaksi)
    {
        $this->db->where("idtransaksi", $idtransaksi);
        return $this->db->get($this->tabel_sale_produk);
    }

    // UPDATE
    public function updateVariasi($id, $data)
    {
        $this->db->where("id", $id);
        $this->db->update($this->tabel_variasi, $data);
    }

    function getProduk($id, $what, $opo = "id")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get($this->tabel_produk);

        if ($what == "semua") {
            $result = null;
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $key => $value) {
                    $result[$key] = $value;
                }
                $result = $result[0];
            }
        } else {
            $result = null;
            foreach ($res->result() as $re) {
                if ($what == "harga") {
                    $level = isset($_SESSION["gid"]) ? $_SESSION["gid"] : "";
                    if ($level == 3) {
                        $result = $re->hargaagen;
                    } elseif ($level == 2) {
                        $result = $re->hargareseller;
                    } else {
                        $result = $re->harga;
                    }
                } else {
                    $result = $re->$what;
                }
            }
        }
        return $result;
    }

    // UPDATE
    public function updateProduk($id, $data)
    {
        $this->db->where("id", $id);
        $this->db->update($this->tabel_produk, $data);
    }

    // GET
    public function getUser($id, $what, $where = "id")
    {
        $this->db->where($where, $id);
        $this->db->limit(1);
        $res = $this->db->get($this->tabel_user);

        if ($what == "semua") {
            $result = array(0);
            foreach ($res->result() as $key => $value) {
                $result[$key] = $value;
            }
            $result = $result[0];
        } else {
            $result = null;
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        }
        return $result;
    }
    // GET
    public function getProfil($id, $what, $opo = "usrid")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get($this->tabel_profile);

        if ($what == "semua") {
            $result = array();
            foreach ($res->result() as $key => $value) {
                $result[$key] = $value;
            }
            $result = $result[0];
        } else {
            $result = null;
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        }
        return $result;
    }

    public function getInfoInvoice($userid)
    {
        require_once FCPATH . 'vendor/autoload.php';
        Xendit::setApiKey("xnd_production_35MgOPYqsrz0VIwkBZNf28GxiUXVk6tXkLEpPTH8CFYYX1JxXGQyFtjFBSqIX");
        $SQL = "SELECT * FROM blw_invoice WHERE usrid = '" . $userid . "'";
        $query = $this->db->query($SQL)->result();
        $response = array(); // menambahkan definisi variabel $response
        if (count($query) > 0) {
            foreach ($query as $row) {
                $tempArray = array();
                $getInvoice = Invoice::retrieve($row->xendit_id);
                if ($getInvoice['status'] == 'SETTLED') {
                    $this->db->where("id", $row->id);
                    $this->db->update("blw_invoice", ["status" => 1]);
                    // SETTLED TRANSAKSI
                    $this->updateTransaksi($row->id, ["status" => 1]);
                } else if ($getInvoice['status'] == 'PAID') {
                    $this->db->where("id", $row->id);
                    $this->db->update("blw_invoice", ["status" => 1]);
                    // PAID TRANSAKSI
                    $this->updateTransaksi($row->id, ["status" => 1]);
                } else if ($getInvoice['status'] == 'PENDING') {
                    $this->db->where("id", $row->id);
                    $this->db->update("blw_invoice", ["status" => 0]);
                    // PENDING TRANSAKSI
                    $this->updateTransaksi($row->id, ["status" => 0]);
                } else if ($getInvoice['status'] == 'EXPIRED') {
                    $this->db->where("id", $row->id);
                    $this->db->update("blw_invoice", ["status" => 3]);
                    // EXPIRED TRANSAKSI
                    $this->updateTransaksi($row->id, ["status" => 4]);
                }

                $tempArray['id'] = (int)$row->id;
                $tempArray['invoice'] = $row->invoice;
                $tempArray['detail'] = $this->getDetailBelanja($row->invoice);
                $tempArray['xendit_url'] = $row->xendit_url;
                $tempArray['status'] = $getInvoice['status'];
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = 'Empty';
            return $response;
        }
    }


    public function getDetailBelanja($invoice)
    {
        $SQL = "SELECT
                b.invoice as invoice,
                c.nama as nama_produk,
                a.harga as harga,
                a.jumlah as jumlah,
                a.diskon as diskon,
                a.keterangan as keterangan
                FROM blw_sales_produk a 
                LEFT JOIN blw_invoice b ON a.idtransaksi = b.id
                LEFT JOIN blw_produk c ON a.idproduk = c.id
                WHERE b.invoice='" . $invoice . "'";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['nama_produk'] = $row->nama_produk;
                $tempArray['harga'] = $row->harga;
                $tempArray['jumlah'] = $row->jumlah;
                $tempArray['diskon'] = $row->diskon;
                $tempArray['keterangan'] = $row->keterangan;
                $response['data'][] = $tempArray;
            }
            return $response;
        }
    }

    public function updateTransaksi($where, $data)
    {
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } else {
            $this->db->where("id", $where);
        }
        $this->db->update("blw_sales", $data);
    }


    public function get_row_transaction($id_transaction)
    {
        $SQL = "SELECT * FROM blw_invoice WHERE invoice='" . $id_transaction . "'";
        return $this->db->query($SQL)->row();
    }

    public function delete_blw_invoice($id_transaction)
    {
        $SQL = "DELETE FROM blw_invoice WHERE invoice='" . $id_transaction . "'";
        return $this->db->query($SQL);
    }

    public function delete_blw_sales($id_bayar)
    {
        $SQL = "DELETE FROM blw_sales WHERE idbayar='" . $id_bayar . "'";
        return $this->db->query($SQL);
    }

    public function delete_blw_product($id_transaksi)
    {
        $SQL = "DELETE FROM blw_sales_produk WHERE idtransaksi='" . $id_transaksi . "'";
        return $this->db->query($SQL);
    }

    public function invoice_complete($userid)
    {
        $SQL = "SELECT * FROM blw_invoice WHERE usrid = '" . $userid . "' AND status ='1'";
        $query = $this->db->query($SQL)->result();
        $response = array();
        if (count($query) > 0) {
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['invoice'] = $row->invoice;
                $tempArray['tgl'] = $row->tgl;
                $tempArray['total'] = $row->tgl;
                $tempArray['status'] = "Complete";
                $tempArray['list_detail_item'] = $this->getDetailBelanja($row->invoice);
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = 'Empty';
            return $response;
        }
    }

    public function invoice_expired($userid)
    {
        $SQL = "SELECT * FROM blw_invoice WHERE usrid = '" . $userid . "' AND status ='3'";
        $query = $this->db->query($SQL)->result();
        $response = array();
        if (count($query) > 0) {
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['invoice'] = $row->invoice;
                $tempArray['tgl'] = $row->tgl;
                $tempArray['total'] = $row->tgl;
                $tempArray['status'] = "Expired";
                $tempArray['list_detail_item'] = $this->getDetailBelanja($row->invoice);
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = 'Empty';
            return $response;
        }
    }
}

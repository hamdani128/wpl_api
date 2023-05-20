<?php

use Xendit\Invoice;
use Xendit\Xendit;



class Checkout extends CI_Controller
{

    private $jwt_token;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("M_order");
        $this->load->model("Global_data");
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
    }

    public function payment()
    {
        require_once FCPATH . 'vendor/autoload.php';
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

        $idbayar = 0;
        $kodebayaran = rand(100, 999);
        $kodebayar = $kodebayaran;

        $uid = $input["uid"];
        $diskon = $input["diskon"];
        $total = $input["total"];
        $saldo = $input["saldo"];
        $kurir = $input["kurir"];
        $alamat_id = $input["alamat_id"];
        $idkec = $input["idkec"];
        $idkab = $input["idkab"];
        $judul = $input["judul"];
        $alamat = $input["alamat_baru"];
        $kodepos = $input["kodepos"];
        $nama = $input["nama"];
        $nohp = $input["nohp"];
        $kodevoucher = $input["kodevoucher"];
        $metode = $input["metode"];
        $berat = $input["berat"];
        $ongkir = $input["ongkir"];
        $paket = $input["paket"];
        $dari = $input["dari"];
        $tujuan = $input["tujuan"];
        // $idproduct =  $input["idproduct"];

        // echo $uid, $diskon, $total, $saldo, $kurir, $alamat_id, $kodevoucher, $metode, $berat, $ongkir;

        $transfer = intval($total) - intval($saldo);
        if ($transfer > 0) {
            $total = $kodebayaran + intval($total);
        } else {
            $total = $total;
            $kodebayar = 0;
        }

        $seli = intval($saldo) - intval($total);
        $status = $seli >= 0 ? 1 : 0;
        $status = ($kurir  == "cod") ? 1 : $status;

        // Set Alamat
        if ($alamat_id == "0" || $alamat_id == "") {
            $this->db->where("usrid", $uid);
            $statusal = ($this->db->get("user_alamat")->num_rows() > 0) ? 0 : 1;
            $alamat = array(
                "usrid"        => $uid,
                "status"    => $statusal,
                "idkec"        => $idkec,
                "idkab"        => $idkab,
                "judul"        => $judul,
                "alamat"    => $alamat,
                "kodepos"    => $kodepos,
                "nama"        => $nama,
                "nohp"        => $nohp,
            );
            $this->db->insert("user_alamat", $alamat);
            $idalamat = $this->db->insert_id();
        } else {
            $idalamat = $alamat_id;
        }
        // Set voucher
        $voucher = $this->M_order->getvoucher($kodevoucher, "id", "kode");
        /**------------------------------------------------------------------------
         *                           Insert To table Pembayaran
         *------------------------------------------------------------------------**/
        $bayar = array(
            "usrid"      => $uid,
            "tgl"        => date("Y-m-d H:i:s"),
            "total"      => $total,
            "saldo"      => $saldo,
            "kodebayar"  => $kodebayar,
            "transfer"   => $transfer,
            "voucher"    => $voucher,
            "metode"     => $metode,
            "diskon"     => $diskon,
            "status"     => $status,
            "kadaluarsa" => date('Y-m-d H:i:s', strtotime("+1 days"))
        );
        $this->db->insert("invoice", $bayar);
        $idbayar = $this->db->insert_id();
        /**------------------------------------------------------------------------
         *                           Update Saldo
         *------------------------------------------------------------------------**/
        if ($metode == 2) {
            $saldoawal = $this->M_order->getSaldo($uid, "saldo", "usrid");
            $saldoakhir = $saldoawal - intval($saldo);

            $update = array(
                "saldo" => $saldoakhir,
                "apdet" => date("Y-m-d H:i:s")
            );
            $this->M_order->updateSaldo($uid, $update);

            $sh = array(
                "tgl"        => date("Y-m-d H:i:s"),
                "usrid"      => $uid,
                "jenis"      => 2,
                "jumlah"     => $saldo,
                "darike"     => 3,
                "sambung"    => $idbayar,
                "saldoawal"  => $saldoawal,
                "saldoakhir" => $saldoakhir
            );
            $this->M_order->saveSaldoHistory($sh);
        }


        /**------------------------------------------------------------------------
         *                           Update no invoice di pembayaran
         *------------------------------------------------------------------------**/
        $invoice = date("Ymd") . $idbayar . $kodebayaran;
        $this->db->where("id", $idbayar);
        $this->db->update("invoice", array("invoice" => $invoice));
        $invoice = "#" . $invoice;

        /**------------------------------------------------------------------------
         *                           Insert Transaksi
         *------------------------------------------------------------------------**/
        $transaksi = array(
            "orderid"    => "TRX" . date("YmdHis"),
            "tgl"        => date("Y-m-d H:i:s"),
            "kadaluarsa" => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . ' + 1 days')),
            "usrid"      => $uid,
            "alamat"     => $idalamat,
            "berat"      => $berat,
            "ongkir"     => $ongkir,
            "kurir"      => $kurir,
            "paket"      => $paket,
            "dari"       => $dari,
            "tujuan"     => $tujuan,
            "status"     => $status,
            "idbayar"    => $idbayar
        );
        $this->M_order->saveTransaksi($transaksi);
        $idtransaksi = $this->db->insert_id();

        /**------------------------------------------------------------------------
         *                           Update Transaksi ITEM Produk
         *------------------------------------------------------------------------**/
        // for ($i = 0; $i < count($_POST["idproduk"]); $i++) {
        //     $this->sales_model->updateSale($_POST["idproduk"][$i], array("idtransaksi" => $idtransaksi));
        // }
        $val = $this->M_order->getDetailSale($uid);
        foreach ($val as $r) {
            $id = $r->id;
            $this->M_order->updateSale($id, array("idtransaksi" => $idtransaksi));
        }
        /**------------------------------------------------------------------------
         *                           UPDATE History STOK
         *------------------------------------------------------------------------**/
        $items = [];
        $db = $this->M_order->getSaleByTransaksi($idtransaksi);
        foreach ($db->result() as $r) {
            $produk = $this->M_order->getProduk($r->idproduk, "semua");
            $item = array(
                "name"       => $produk->nama,
                "price"    => $r->harga,
                "quantity"   => $r->jumlah
            );
            $items[] = $item;

            if ($r->variasi != 0) {
                $var = $this->M_order->getVariasi($r->variasi, "semua", "id");
                if ($r->jumlah > $var->stok) {
                    echo json_encode(array("success" => false, "message" => "stok produk tidak mencukupi"));
                    $stok = 0;
                    exit;
                } else {
                    $stok = $var->stok - $r->jumlah;
                }
                $variasi[] = $r->variasi;
                $stock[] = $stok;
                $stokawal[] = $var->stok;
                $jml[] = $r->jumlah;

                for ($i = 0; $i < count($variasi); $i++) {
                    $update = ["stok" => $stock[$i], "tgl" => date("Y-m-d H:i:s")];
                    $this->M_order->updateVariasi($variasi[$i], $update);

                    $data = array(
                        "usrid"       => $$uid,
                        "stokawal"    => $stokawal[$i],
                        "stokakhir"   => $stock[$i],
                        "variasi"     => $variasi[$i],
                        "jumlah"      => $jml[$i],
                        "tgl"         => date("Y-m-d H:i:s"),
                        "idtransaksi" => $idtransaksi
                    );
                    $this->db->insert("history_stok", $data);
                }
            } else {
                $pro = $this->M_order->getProduk($r->idproduk, "semua");
                if ($r->jumlah > $pro->stok) {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "stok produk tidak mencukupi"
                    ));
                    $stok = 0;
                    exit;
                }
                $stok = $pro->stok - $r->jumlah;
                $this->M_order->updateProduk(
                    $r->idproduk,
                    ["stok" => $stok, "tglupdate" => date("Y-m-d H:i:s")]
                );

                $data = array(
                    "usrid"       => $uid,
                    "stokawal"    => $pro->stok,
                    "stokakhir"   => $stok,
                    "variasi"     => 0,
                    "jumlah"      => $r->jumlah,
                    "tgl"         => date("Y-m-d H:i:s"),
                    "idtransaksi" => $idtransaksi
                );
                $this->db->insert("history_stok", $data);
            }
        }

        /**------------------------------------------------------------------------
         *                           Send Notification
         *------------------------------------------------------------------------**/
        $usrid    = $this->M_order->getUser($uid, "semua");
        $profil   = $this->M_order->getProfil($uid, "semua", "usrid");
        $alamat   = $this->Global_data->getAlamat($idalamat, "semua");
        $toko     = $this->Global_data->getSetting("semua");
        $diskon   = $diskon != 0 ? "Diskon: <b>Rp " . $this->Global_data->formUang(intval($diskon)) . "</b><br/>" : "";
        $diskonwa = $diskon != 0 ? "Diskon: *Rp " . $this->Global_data->formUang(intval($diskon)) . "*\n" : "";
        $pesan = "
        			Halo <b>" . $profil->nama . "</b><br/>" .
            "Terimakasih sudah membeli produk kami.<br/>" .
            "Saat ini kami sedang menunggu pembayaran darimu sebelum kami memprosesnya. Sebagai informasi, berikut detail pesananmu <br/>" .
            "No Invoice: <b>" . $invoice . "</b><br/> <br/>" .
            "Total Pesanan: <b>Rp " . $this->Global_data->formUang($total) . "</b><br/>" .
            "Ongkos Kirim: <b>Rp " . $this->Global_data->formUang(intval($ongkir)) . "</b><br/>" . $diskon .
            "Kurir Pengiriman: <b>" . strtoupper($kurir . " " . $paket) . "</b><br/> <br/>" .
            "Detail Pengiriman <br/>" .
            "Penerima: <b>" . $alamat->nama . "</b> <br/>" .
            "No HP: <b>" . $alamat->nohp . "</b> <br/>" .
            "Alamat: <b>" . $alamat->alamat . "</b>" .
            "<br/> <br/>" .
            "Untuk pembayaran silahkan langsung klik link berikut:<br/>" .
            "<a href='" . site_url("home/invoice") . "?inv=" . $this->Global_data->arrEnc($idbayar, "encode") . "'>Bayar Pesanan Sekarang &raquo;</a>
        		";
        $this->Global_data->sendEmail($usrid->username, $toko->nama . " - Pesanan", $pesan, "Pesanan");
        $pesan = "
        			Halo *" . $profil->nama . "*\n" .
            "Terimakasih sudah membeli produk kami.\n" .
            "Saat ini kami sedang menunggu pembayaran darimu sebelum kami memprosesnya. Sebagai informasi, berikut detail pesananmu \n \n" .
            "No Invoice: *" . $invoice . "*\n" .
            "Total Pesanan: *Rp " . $this->Global_data->formUang($total) . "*\n" .
            "Ongkos Kirim: *Rp " . $this->Global_data->formUang(intval($ongkir)) . "*\n" . $diskonwa .
            "Kurir Pengiriman: *" . strtoupper($kurir . " " . $paket) . "*\n \n" .
            "Detail Pengiriman \n" .
            "Penerima: *" . $alamat->nama . "*\n" .
            "No HP: *" . $alamat->nohp . "*\n" .
            "Alamat: *" . $alamat->alamat . "*\n \n" .
            "Untuk pembayaran silahkan langsung klik link berikut\n" . site_url("home/invoice") . "?inv=" .  $this->Global_data->arrEnc($idbayar, "encode") . "
        		";
        $this->Global_data->sendWA($profil->nohp, $pesan);
        $this->Global_data->sendWAOK($profil->nohp, $pesan);
        $pesan = "
        			<h3>Pesanan Baru</h3><br/>
        			<b>" . strtoupper(strtolower($profil->nama)) . "</b> telah membuat pesanan baru dengan total pembayaran 
        			<b>Rp. " . $this->Global_data->formUang($total) . "</b> Invoice ID: <b>" . $invoice . "</b>
        			<br/>&nbsp;<br/>&nbsp;<br/>
        			Cek Pesanan Pembeli di <b>Dashboard Admin " . $toko->nama . "</b><br/>
        			<a href='" . site_url("cdn") . "'>Klik Disini</a>
        		";
        $this->Global_data->sendEmail($toko->email, $toko->nama . " - Pesanan Baru", $pesan, "Pesanan Baru di " . $toko->nama);
        $pesan = "
        			*Pesanan Baru*\n" .
            "*" . strtoupper(strtolower($profil->nama)) . "* telah membuat pesanan baru dengan detail:\n" .
            "Total Pembayaran: *Rp. " . $this->Global_data->formUang($total) . "*\n" .
            "Invoice ID: *" . $invoice . "*" .
            "\n \n" .
            "Cek Pesanan Pembeli di *Dashboard Admin " . $toko->nama . "*
        			";
        $this->Global_data->sendWA($toko->wasap, $pesan);
        $this->Global_data->sendWAOK($toko->wasap, $pesan);
        /**------------------------------------------------------------------------
         *                           Send To Xendit
         *------------------------------------------------------------------------**/
        Xendit::setApiKey("xnd_production_35MgOPYqsrz0VIwkBZNf28GxiUXVk6tXkLEpPTH8CFYYX1JxXGQyFtjFBSqIX");



        // amount
        $bayar = $this->Global_data->getBayar($idbayar, "semua");

        // Param Xendit
        $success_redirect_url = base_url() . 'invoice/home/callback/' . $bayar->invoice;
        $params = [
            'external_id' => $bayar->invoice,
            'amount' => $total, // Total Harga
            'description' => 'Pembayaran pesanan di ' . $this->Global_data->getSetting("semua")->nama, // Optional 
            'customer' => [
                'given_names' => $profil->nama,
                'email' => $this->M_order->getUser($bayar->usrid, "username"),
                'mobile_number' => $profil->nohp
            ],
            'customer_notification_preference' => [
                'invoice_created' => ["email"],
                'invoice_reminder' => ["email"],
                'invoice_paid' => ["email"],
                'invoice_expired' => ["email"],
            ],
            'items' => $items,
            'success_redirect_url' => $success_redirect_url
        ];
        // Insert Xendit
        // $createInvoice = \Xendit\Invoice::create($params);
        $createInvoice = Invoice::create($params);

        // update no invoice
        $this->db->where("id", $idbayar);
        $this->db->update("invoice", [
            "xendit_id" => $createInvoice['id'],
            "xendit_url" => $createInvoice['invoice_url'],
        ]);
        // $url = $status == 0 ? site_url("invoice") . "?inv=" . $this->Global_data->arrEnc($idbayar, "encode") : site_url("account/order");
        if ($createInvoice) {
            $response = array(
                'success' => true,
                'url' => $createInvoice['invoice_url'],
                'xendit_id' => $createInvoice['id'],
            );
        } else {
            $response = array(
                'success' => false,
                'message' => "forbidden",
            );
        }
        $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

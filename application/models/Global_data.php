<?php if (!defined('BASEPATH')) exit('Hacking Attempt : Keluar dari sistem !! ');
class Global_data extends CI_Model
{


    // Get Setting
    function getSetting($data)
    {
        if ($data != "semua") {
            $this->db->where("field", $data);
        }
        $res = $this->db->get("@setting");
        $result = null;
        if ($data == "semua") {
            $result = array(null);
            foreach ($res->result() as $re) {
                $result[$re->field] = $re->value;
            }
            $result = (object)$result;
        } else {
            $result = "";
            foreach ($res->result() as $re) {
                $result = $re->value;
            }
        }
        return $result;
    }

    // GET ALAMAT
    function getAlamat($id, $what, $opo = "id")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get("user_alamat");

        if ($res->num_rows() > 0) {
            if ($what == "semua") {
                foreach ($res->result() as $key => $value) {
                    $result[$key] = $value;
                }
                $result = $result[0];
            } else {
                foreach ($res->result() as $re) {
                    $result = $re->$what;
                }
            }
        } else {
            $result = new stdClass();
            $result->nama = "";
            $result->alamat = "";
            $result->judul = "";
            $result->kodepos = "";
            $result->nohp = "";
            $result->idkec = 0;
            $result->usrid = 0;
            $result->status = 0;
        }
        return $result;
    }

    // Uang
    function formUang($format)
    {
        $result = number_format($format, 0, ",", ".");
        return $result;
    }

    // VERIFIKASI
    function sendEmail($tujuan, $judul, $pesan, $subyek, $pengirim = null)
    {
        $data = array(
            "jenis"        => 1,
            "tujuan"    => $tujuan,
            "judul"        => $judul,
            "pesan"        => $pesan,
            "subyek"    => $subyek,
            "pengirim"    => $pengirim,
            "tgl"        => date("Y-m-d H:i:s"),
            "status"    => 0
        );
        $this->db->insert("pesan_notifikasi", $data);

        return true;
    }
    function sendEmailOK($tujuan, $judul, $pesan, $subyek, $pengirim = null)
    {
        $this->load->library('email');
        $seting = $this->globalset("semua");
        if ($seting->email_jenis == 2) {
            $config['protocol'] = "smtp";
            $config['smtp_host'] = $seting->email_server;
            $config['smtp_port'] = $seting->email_port;
            $config['smtp_user'] = $seting->email_notif;
            $config['smtp_pass'] = $seting->email_password;

            if ($seting->email_port == 465) {
                $config['smtp_crypto'] = "ssl";
            }
        }
        $config['charset'] = "utf-8";
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
        $this->email->initialize($config);

        $this->email->from($seting->email_notif, $judul);
        $this->email->to($tujuan);
        if ($pengirim != null) {
            $this->email->cc($pengirim);
        }

        $pesan = $this->load->view("email_template", array("content" => $pesan), true);
        $this->email->subject($subyek);
        $this->email->message($pesan);

        if ($this->email->send()) {
            return true;
        } else {
            //show_error($this->email->print_debugger());
            return false;
        }
    }
    public function sendWA($nomer, $pesan)
    {
        $data = array(
            "jenis"        => 2,
            "tujuan"    => $nomer,
            "pesan"        => $pesan,
            "tgl"        => date("Y-m-d H:i:s"),
            "status"    => 0
        );
        $this->db->insert("pesan_notifikasi", $data);

        return true;
    }
    public function sendWAOK($nomer, $pesan)
    {
        $key = $this->globalset("woowa");
        $nomer = intval($nomer);
        $nomer = substr($nomer, 0, 2) != "62" ? "+62" . $nomer : "+" . $nomer;
        $url = 'http://116.203.92.59/api/send_message';
        $data = array(
            "phone_no"    => $nomer,
            "key"        => $key,
            "message"    => $pesan . "\n" . date("Y/m/d H:i:s")
        );
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 360);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $res = curl_exec($ch);
        curl_close($ch);

        if ($res == "success") {
            return true;
        } else {
            return false;
        }
    }

    function arrEnc($arr, $type = "encode")
    {
        if ($type == "encode") {
            $result = base64_encode(serialize($arr));
        } else {
            $result = unserialize(base64_decode($arr));
        }
        return $result;
    }



    function globalset($data)
    {
        if ($data != "semua") {
            $this->db->where("field", $data);
        }
        $res = $this->db->get("@setting");
        $result = null;
        if ($data == "semua") {
            $result = array(null);
            foreach ($res->result() as $re) {
                $result[$re->field] = $re->value;
            }
            $result = (object)$result;
        } else {
            $result = "";
            foreach ($res->result() as $re) {
                $result = $re->value;
            }
        }
        return $result;
    }

    function getHistoryOngkir($id, $what = "id", $opo = "id")
    {
        if (is_array($id)) {
            foreach ($id as $key => $val) {
                $this->db->where($key, $val);
            }
            $this->db->limit(1);
            $res = $this->db->get("history_ongkir");

            $result = "tidak ditemukan";
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        } else {
            $this->db->where($opo, $id);
            $this->db->limit(1);
            $res = $this->db->get("history_ongkir");

            $result = "tidak ditemukan";
            foreach ($res->result() as $re) {
                $result = $re->$what;
            }
        }
        return $result;
    }

    function beratkg($berat = 0, $kurir = "jne")
    {
        $beratkg = ($berat < 1000) ? 1 : round(intval($berat) / 1000, 0, PHP_ROUND_HALF_DOWN);
        if ($kurir == "jne") {
            $selisih = $berat - ($beratkg * 1000);
            if ($selisih > 300) {
                $beratkg = $beratkg + 1;
            }
        } elseif ($kurir == "jnt") {
            $selisih = $berat - ($beratkg * 1000);
            if ($selisih > 200) {
                $beratkg = $beratkg + 1;
            }
        } elseif ($kurir == "pos") {
            $selisih = $berat - ($beratkg * 1000);
            if ($selisih > 200) {
                $beratkg = $beratkg + 1;
            }
        } elseif ($kurir == "tiki") {
            $selisih = $berat - ($beratkg * 1000);
            if ($selisih > 299) {
                $beratkg = $beratkg + 1;
            }
        } else {
            $selisih = $berat - ($beratkg * 1000);
            if ($selisih > 0) {
                $beratkg = $beratkg + 1;
            }
        }
        return $beratkg;
    }

    function getBayar($id, $what, $opo = "id")
    {
        $this->db->where($opo, $id);
        $this->db->limit(1);
        $res = $this->db->get("invoice");
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


    public function updateTransaksi($where, $data)
    {
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } else {
            $this->db->where("id", $where);
        }
        $this->db->update("sales", $data);
    }
}
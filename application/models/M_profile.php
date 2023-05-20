<?php
class M_profile extends CI_Model
{
    private $tabel_user_alamat = "user_alamat";


    public function cek_user_by_email($email)
    {
        $this->db->where("username", $email);
        $this->db->limit(1);
        $user = $this->db->get($this->tabel_user);
        return $user;
    }

    public function getprofile($user_id)
    {
        $SQL = "SELECT
                a.id as id,
                a.username as username,
                a.password as password,
                a.nohp as nohp,
                b.id as alamat_id,
                b.idkab as idkab,
                b.alamat as alamat,
                b.kodepos as kodepos,
                b.nama as nama,
                c.saldo as saldo
                FROM blw_user a 
                LEFT JOIN blw_user_alamat b ON a.id=b.usrid
                LEFT JOIN blw_user_saldo c ON a.id=c.usrid
                WHERE a.id='" . $user_id . "'";
        return $this->db->query($SQL)->row();
    }

    function encode($string)
    {
        return $this->encryption->encrypt($string);
    }
    function decode($string)
    {
        return $this->encryption->decrypt($string);
    }

    public function register_alamat($data)
    {
        return $this->db->insert($this->tabel_user_alamat, $data);
    }

    public function baca_alamat($usrid)
    {
        $SQL = "SELECT * FROM blw_user_alamat WHERE usrid='" . $usrid . "'";
        return $this->db->query($SQL)->row();
    }

    public function update_alamat($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->tabel_user_alamat, $data);
    }
}

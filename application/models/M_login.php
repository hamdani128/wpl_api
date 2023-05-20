<?php
class M_login extends CI_Model
{
    private $tabel_user          = "user";
    private $user_profile          = "user_profil";

    public function cek_user_by_email($email)
    {
        $this->db->where("username", $email);
        $this->db->limit(1);
        $user = $this->db->get($this->tabel_user);
        return $user;
    }
    function encode($string)
    {
        return $this->encryption->encrypt($string);
    }
    function decode($string)
    {
        return $this->encryption->decrypt($string);
    }

    public function register($data)
    {
        $this->db->insert($this->tabel_user, $data);
        return $this->db->insert_id();
    }

    public function register_profile($data)
    {
        return $this->db->insert($this->user_profile, $data);
    }

    public function register_alamat($data)
    {
        return $this->db->insert($this->tabel_user_alamat, $data);
    }
}

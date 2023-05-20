<?php
class M_banner extends CI_Model
{
    public function getbanner()
    {
        $SQL = "SELECT * FROM `blw_@promo`";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            $response['error'] = false;
            $response['message'] = 'Successfully render data';
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = (int)$row->id;
                $tempArray['tgl'] = $row->tgl;
                $tempArray['tgl_selesai'] = $row->tgl_selesai;
                $tempArray['image'] = 'https://winnyputrilubis.id/uploads/promo/' . $row->gambar;
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            return false;
        }
    }
}

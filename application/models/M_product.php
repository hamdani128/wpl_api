<?php
class M_product extends CI_Model
{

    public function GetAllProduct()
    {
        $SQL = "SELECT
        a.id as id,
        a.kode as kode,
        a.nama as nama,
        a.deskripsi as deskripsi,
        a.berat as berat,
        a.harga as harga,
        a.hargacoret as hargacoret,
        a.stok as stok,
        c.nama as kategori,
        b.nama as image,
        a.url as url
        FROM
        blw_produk a
        LEFT JOIN blw_produk_upload b 
        ON a.id = b.idproduk
        LEFT JOIN `blw_@kategori` c 
        ON a.idcat = c.id
        GROUP BY 1,2,3,4,5,6,7,8,9";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            $response['error'] = false;
            $response['message'] = 'Successfully retrieved product data';
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = (int)$row->id;
                $tempArray['kode'] = $row->kode;
                $tempArray['nama'] = $row->nama;
                $tempArray['deskripsi'] = $row->deskripsi;
                $tempArray['berat'] = (int)$row->berat;
                $tempArray['harga'] = (int)$row->harga;
                $tempArray['hargacoret'] = (int)$row->hargacoret;
                $tempArray['stok'] = (int)$row->stok;
                $tempArray['kategori'] = $row->kategori;
                $tempArray['image'] = 'http://localhost/winy/uploads/produk/' . $row->image;
                $tempArray['url'] = $row->url;
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = 'Empty';
            return $response;
        }
        // return false;
    }

    public function GetProductByid($id)
    {
        $SQL = "SELECT
        a.id as id,
        a.kode as kode,
        a.nama as nama,
        a.deskripsi as deskripsi,
        a.berat as berat,
        a.harga as harga,
        a.hargacoret as hargacoret,
        a.stok as stok,
        c.nama as kategori,
        b.nama as image,
        a.url as url
        FROM
        blw_produk a
        LEFT JOIN blw_produk_upload b 
        ON a.id = b.idproduk
        LEFT JOIN `blw_@kategori` c 
        ON a.idcat = c.id
        WHERE a.id = '" . $id . "'
        GROUP BY 1,2,3,4,5,6,7,8,9";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            $response['error'] = false;
            $response['message'] = 'Successfully retrieved product data';
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = (int)$row->id;
                $tempArray['kode'] = $row->kode;
                $tempArray['nama'] = $row->nama;
                $tempArray['deskripsi'] = $row->deskripsi;
                $tempArray['berat'] = (int)$row->berat;
                $tempArray['harga'] = (int)$row->harga;
                $tempArray['hargacoret'] = (int)$row->hargacoret;
                $tempArray['stok'] = (int)$row->stok;
                $tempArray['kategori'] = $row->kategori;
                $tempArray['image'] = 'http://localhost/winy/uploads/produk/' . $row->image;
                $tempArray['url'] = $row->url;
                $response['data'][] = $tempArray;
            }
            return $response;
        } else {
            $response['error'] = false;
            $response['message'] = 'Empty';
            return $response;
        }
    }

    public function count_all_product()
    {
        $SQL = "SELECT
        a.id as id,
        a.kode as kode,
        a.nama as nama,
        a.deskripsi as deskripsi,
        a.berat as berat,
        a.harga as harga,
        a.hargacoret as hargacoret,
        a.stok as stok,
        c.nama as kategori,
        b.nama as image,
        a.url as url
        FROM
        blw_produk a
        LEFT JOIN blw_produk_upload b 
        ON a.id = b.idproduk
        LEFT JOIN `blw_@kategori` c 
        ON a.idcat = c.id
        GROUP BY 1,2,3,4,5,6,7,8,9";
        return $this->db->query($SQL)->num_rows();
    }

    public function GetAllProductPaginate($limit, $offset)
    {
        $SQL = "SELECT
    a.id as id,
    a.kode as kode,
    a.nama as nama,
    a.deskripsi as deskripsi,
    a.berat as berat,
    a.harga as harga,
    a.hargacoret as hargacoret,
    a.stok as stok,
    c.nama as kategori,
    b.nama as image,
    a.url as url
    FROM
    blw_produk a
    LEFT JOIN blw_produk_upload b 
    ON a.id = b.idproduk
    LEFT JOIN `blw_@kategori` c 
    ON a.idcat = c.id
    GROUP BY 1,2,3,4,5,6,7,8,9
    LIMIT ?, ?";
        $query = $this->db->query($SQL, array($offset, $limit))->result();
        if (count($query) > 0) {
            $response = array();
            $response['error'] = false;
            $response['message'] = 'Successfully retrieved product data';
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = (int)$row->id;
                $tempArray['kode'] = $row->kode;
                $tempArray['nama'] = $row->nama;
                $tempArray['deskripsi'] = $row->deskripsi;
                $tempArray['berat'] = (int)$row->berat;
                $tempArray['harga'] = (int)$row->harga;
                $tempArray['hargacoret'] = (int)$row->hargacoret;
                $tempArray['stok'] = (int)$row->stok;
                $tempArray['kategori'] = $row->kategori;
                $tempArray['image'] = 'http://localhost/winy/uploads/produk/' . $row->image;
                $tempArray['url'] = $row->url;
                $response['data'][] = $tempArray;
            }
            return $response;
        }
    }


    public function searchProduct($product)
    {
        $SQL = "SELECT
        a.id as id,
        a.kode as kode,
        a.nama as nama,
        a.deskripsi as deskripsi,
        a.berat as berat,
        a.harga as harga,
        a.hargacoret as hargacoret,
        a.stok as stok,
        c.nama as kategori,
        b.nama as image,
        a.url as url
        FROM
        blw_produk a
        LEFT JOIN blw_produk_upload b 
        ON a.id = b.idproduk
        LEFT JOIN `blw_@kategori` c 
        ON a.idcat = c.id
        WHERE a.nama LIKE '%" . $product . "%' or a.kode LIKE '%" . $product . "%'
        GROUP BY 1,2,3,4,5,6,7,8,9";
        $query = $this->db->query($SQL)->result();
        if (count($query) > 0) {
            $response = array();
            $response['error'] = false;
            $response['message'] = 'Successfully retrieved product data';
            foreach ($query as $row) {
                $tempArray = array();
                $tempArray['id'] = (int)$row->id;
                $tempArray['kode'] = $row->kode;
                $tempArray['nama'] = $row->nama;
                $tempArray['deskripsi'] = $row->deskripsi;
                $tempArray['berat'] = (int)$row->berat;
                $tempArray['harga'] = (int)$row->harga;
                $tempArray['hargacoret'] = (int)$row->hargacoret;
                $tempArray['stok'] = (int)$row->stok;
                $tempArray['kategori'] = $row->kategori;
                $tempArray['image'] = 'http://localhost/winy/uploads/produk/' . $row->image;
                $tempArray['url'] = $row->url;
                $response['data'][] = $tempArray;
            }
            return $response;
        }
    }
}

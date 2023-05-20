<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Product extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_product');
    }

    public function index()
    {
        $id = $this->input->get('id');
        if ($id == null) {
            $product = $this->m_product->GetAllProduct();
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($product));
        } else {
            $product = $this->m_product->GetProductByid($id);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($product));
        }
    }

    public function paginate()
    {
        $page = $this->input->get('page');
        $limit = (int) $this->input->get('limit'); // cast limit as integer
        $offset = ($page - 1) * $limit;
        $total_data = $this->m_product->count_all_product();
        $total_page = ceil($total_data / $limit);
        $product = $this->m_product->GetAllProductPaginate($limit, $offset);
        $response = [
            'status' => true,
            'message' => 'Data retrieved successfully',
            'data' => $product,
            'pagination' => [
                'total_data' => $total_data,
                'total_page' => $total_page,
                'current_page' => $page,
                'limit' => $limit,
            ],
        ];
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    public function search()
    {
        $product = $this->input->get('search');
        $result = $this->m_product->searchProduct($product);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}

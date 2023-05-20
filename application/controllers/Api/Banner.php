<?php

defined('BASEPATH') or exit('No direct script access allowed');
// require_once APPPATH . '/third_party/REST/RestController.php';
class Banner extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_banner');
    }

    public function index()
    {
        $value = $this->M_banner->getbanner();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($value));
    }
}

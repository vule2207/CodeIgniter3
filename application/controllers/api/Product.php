<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'helpers/customResponse.php';
/**
 *
 * Controller Product
 *
 * This controller for ...
 *
 * @package   CodeIgniter
 * @category  Controller REST
 * @author    Setiawan Jodi <jodisetiawan@fisip-untirta.ac.id>
 * @author    Raul Guerrero <r.g.c@me.com>
 * @link      https://github.com/setdjod/myci-extension/
 * @param     ...
 * @return    ...
 *
 */

class Product extends REST_Controller
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    // 
  }
}


/* End of file Product.php */
/* Location: ./application/controllers/Product.php */
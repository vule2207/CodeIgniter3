<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approval_model extends CI_Model
{


    public function __construct()
    {
        parent::__construct();

        // Load database library 
        $this->load->database();

        // Database table name 
        $this->tbl_name = 'autodocu_simple';
    }

    public function insert_data($data = array())
    {
		// print_r($data);
		// exit;

		$insert = $this->db->insert($this->tbl_name, $data);
        if ($insert) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }
}
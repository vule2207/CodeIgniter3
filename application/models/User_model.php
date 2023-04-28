<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
                        
class User_model extends CI_Model {
                        
  public function __construct() { 
    parent::__construct(); 
     
    // Load database library 
    $this->load->database(); 

    // Database table name 
    $this->tbl_name = 'users'; 
} 

/* 
 * Fetch user data 
 */ 
function getUser($id = ""){ 
    if(!empty($id)){ 
        $query = $this->db->get_where($this->tbl_name, array('id' => $id)); 
        return $query->row_array(); 
    }else{ 
        $query = $this->db->get($this->tbl_name); 
        return $query->result_array(); 
    } 
} 
 
/* 
 * Insert user data 
 */ 
public function insert($data = array()) { 
    if(!array_key_exists('created_at', $data)){ 
        $data['created_at'] = date("Y-m-d H:i:s"); 
    } 
    if(!array_key_exists('updated_at', $data)){ 
        $data['updated_at'] = date("Y-m-d H:i:s"); 
    } 
    $insert = $this->db->insert($this->tbl_name, $data); 
    if($insert){ 
        return $this->db->insert_id(); 
    }else{ 
        return false; 
    } 
} 
 
/* 
 * Update user data 
 */ 
public function update($data, $id) { 
    if(!empty($data) && !empty($id)){ 
        if(!array_key_exists('updated_at', $data)){ 
            $data['updated_at'] = date("Y-m-d H:i:s"); 
        } 
        $update = $this->db->update($this->tbl_name, $data, array('id' => $id)); 
        return $update?true:false; 
    }else{ 
        return false; 
    } 
} 
 
/* 
 * Delete user data 
 */ 
public function delete($id){ 
    $delete = $this->db->delete($this->tbl_name, array('id' => $id)); 
    return $delete?true:false; 
}                  
                            
                        
}
                        
/* End of file User.php */

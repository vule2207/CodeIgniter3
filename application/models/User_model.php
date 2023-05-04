<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{

    private $total_rows = 0;

    public function __construct()
    {
        parent::__construct();

        // Load database library 
        $this->load->database();

        // Database table name 
        $this->tbl_name = 'users';
    }

    /* 
     * Fetch user data 
     */
    function getUser($id = "", $fields = NULL, $where = NULL, $page = NULL, $limit = NULL, $order = 'name asc')
    {
        // Limit
        if ($page != NULL && $limit != NULL) {
            //start
            $start = ($page - 1) * $limit;
            //limit
            $this->db->limit($limit, $start);
        }


        if (!empty($id)) {
            $query = $this->db->get_where($this->tbl_name, array('id' => $id));
            return $query->row_array();
        } else {
            $query = $this->db->get($this->tbl_name);
            return $query->result_array();
        }
    }

    /* 
     * Insert user data 
     */
    public function insert($data = array())
    {
        if (!array_key_exists('created_at', $data)) {
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        if (!array_key_exists('updated_at', $data)) {
            $data['updated_at'] = date("Y-m-d H:i:s");
        }
        $insert = $this->db->insert($this->tbl_name, $data);
        if ($insert) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    /* 
     * Update user data 
     */
    public function update($data, $id)
    {
        if (!empty($data) && !empty($id)) {
            if (!array_key_exists('updated_at', $data)) {
                $data['updated_at'] = date("Y-m-d H:i:s");
            }
            $update = $this->db->update($this->tbl_name, $data, array('id' => $id));
            return $update ? true : false;
        } else {
            return false;
        }
    }

    /* 
     * Delete user data 
     */
    public function delete($id)
    {
        $delete = $this->db->delete($this->tbl_name, array('id' => $id));
        return $delete ? true : false;
    }

    public function _set_where($where)
    {
        if (is_array($where)) {
            foreach ($where as $con => $val) {
                if (is_array($val)) {
                    $this->db->where_in($con, $val);
                } else {
                    $this->db->where($con, $val);
                }
            }
        }
    }

    protected function _set_select($fields, $escape_flag = TRUE)
    {
        if (!empty($fields)) {
            $str_fields = is_array($fields) ? implode(', ', $fields) : $fields;
            $this->db->select($str_fields, $escape_flag);
        }
    }

    protected function _set_limit($limit, $offset = FALSE)
    {
        if (!empty($limit)) {
            if (is_array($limit)) {
                list($limit, $offset) = $limit;
            }

            (empty($offset)) ? $this->db->limit($limit) : $this->db->limit($limit, $offset);
        }
    }

    protected function _set_order_by($order_by)
    {
        if (is_array($order_by)) {
            foreach ($order_by as $sort => $order) {
                $this->db->order_by($sort, $order);
            }
        } else if (is_string($order_by)) {
            $this->db->order_by($order_by);
        }
    }

    protected function _set_join($tables)
    {
        if (!empty($tables['table'])) {
            $this->db->join($tables['table'], $tables['on'], $tables['join']);
        } else if (is_array($tables)) {
            foreach ($tables as $table) {
                if (!empty($table['table'])) {
                    $this->db->join($table['table'], $table['on'], $table['join']);
                }
            }
        }
    }

    public function get_total_rows()
    {
        return $this->total_rows;
    }

}

/* End of file User.php */
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
    public function getUser($id = "", $fields = NULL, $where = NULL, $page = NULL, $limit = NULL, $order = 'name asc')
    {
        $fields = $fields ? $fields : '*';
        $result = array();

        $this->db
            ->select($fields)
            ->from($this->tbl_name);

        if (!empty($where)) {
            foreach ($where as $key => $val) {
                $this->_set_where([$key => $val]);
            }
        }

        if ($order) {
            $this->_set_order_by($order);
        }

        // Count total rows
        $temp_db = clone $this->db;
        $temp_query = $temp_db->get();
        if ($temp_query && $temp_query->num_rows() > 0) {
            $this->total_rows = $temp_query->num_rows();
        }

        // Limit
        if ($page != NULL && $limit != NULL) {
            //start
            $start = ($page - 1) * $limit;
            //limit
            $this->db->limit($limit, $start);
        }

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
        }

        return $result;
    }

    public function get_user_by_id($user_id)
    {
        $this->db->from('users');
        $this->db->where('id', $user_id);
        return $this->db->get()->row();
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
        if (!array_key_exists('avatar', $data)) {
            $data['avatar'] = 'https://api.dicebear.com/6.x/big-ears-neutral/svg?seed=' . $data['name'];
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

    // auth
    public function create_user($name, $username, $email, $password)
    {

        $data = array(
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $this->hash_password($password),
            'created_at' => date('Y-m-j H:i:s'),
        );

        $this->db->insert('users', $data);
        return $this->db->insert_id();

    }

    public function resolve_user_login($username, $password)
    {

        $this->db->select('password');
        $this->db->from('users');
        $this->db->where('username', $username);
        $hash = $this->db->get()->row('password');

        return $this->verify_password_hash($password, $hash);

    }

    public function get_user_id_from_username($username)
    {

        $this->db->select('id');
        $this->db->from('users');
        $this->db->where('username', $username);

        return $this->db->get()->row('id');

    }

    private function hash_password($password)
    {

        return password_hash($password, PASSWORD_BCRYPT);

    }

    private function verify_password_hash($password, $hash)
    {

        return password_verify($password, $hash);

    }


    // pagination and filter
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
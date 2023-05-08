<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';


class Upload extends REST_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->database();
  }

  public function index_get()
  {
    $this->load->view('upload_form');
  }

  public function index_post($id)
  {
    if ($_FILES['avatar']) {
      $upload_path = 'public/images/avatars';
      $file_name = 'avatar-' . strtolower($this->input->post('name')) . '-' . time();
      $config = array(
        'upload_path' => $upload_path,
        'allowed_types' => 'jpg|jpeg|png|gif',
        'file_name' => $file_name,
      );

      $this->upload->initialize($config);

      if ($this->upload->do_upload('avatar')) {
        $uploadData = $this->upload->data();
        $data['avatar'] = base_url() . $upload_path . '/' . $uploadData['file_name'];
      } else {
        echo $this->upload->display_errors();
      }

      $update = $this->db->update('users', $data, array('id' => $id));

      if ($update) {
        $this->response(
          array(
            'success' => true,
            'message' => 'Update avatar successfull!',
            'data' => $id
          ), REST_Controller::HTTP_OK
        );
      }
    }
  }

  private function delete_old_avatar($id)
  {
    $full_path = $this->db->select('avatar')->from('users')->where('id', $id)->get();
    $old_path_avatar = str_replace(base_url(), '', $full_path);

    if (file_exists($old_path_avatar)) {
      unlink($old_path_avatar);
    }
  }
}
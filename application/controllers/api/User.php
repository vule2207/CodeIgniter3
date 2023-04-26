<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'helpers/customResponse.php';


class User extends REST_Controller
{

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_get($id = 0)
  {
    try {
      if (!empty($id)) {
        $data = $this->db->get_where("users", ['id' => $id])->row_array();
      } else {
        $data = $this->db->get("users")->result();
      }

      $response = CustomResponse::responseSuccess("Get user successfull!", $data);
      $this->response($response, REST_Controller::HTTP_OK);
    } catch (Exception $e) {
      $response = CustomResponse::responseError($e);
      $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_post()
  {
    try {
      $input = $this->input->post();
      if (!empty($input)) {
        $this->db->insert('users', $input);

        $response = CustomResponse::responseSuccess("User created successfully!", []);
        $this->response($response, REST_Controller::HTTP_CREATED);
      }
    } catch (Exception $e) {
      $response = CustomResponse::responseError($e);
      $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_put($id)
  {
    try {
      $input = $this->put();
      // print_r($input);
      // exit();

      if (isset($id) && !empty($input)) {
        $data = json_decode($input[0], true);
        $where = array(
          'id' => $id,
        );

        $this->db->where($where);
        $this->db->update('users', $data);

        $response = CustomResponse::responseSuccess("User updated successfully!", []);
        $this->response($response, REST_Controller::HTTP_OK);
      }
    } catch (Exception $e) {
      $response = CustomResponse::responseError($e);
      $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_delete($id)
  {
    try {
      if (!empty($id)) {
        $this->db->delete('users', array('id' => $id));
        $response = CustomResponse::responseSuccess("User deleted successfully!", []);
        $this->response($response, REST_Controller::HTTP_OK);
      }
    } catch (Exception $e) {
      $response = CustomResponse::responseError($e);
      $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }
}

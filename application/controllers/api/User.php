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
    $this->load->model('user_model');
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_get($id = 0)
  {
    $users = $this->user_model->getUser($id);
    if (!empty($users)) {
      $response = CustomResponse::responseSuccess("Get user successfull!", $users);
      $this->response($response, REST_Controller::HTTP_OK);
    } else {
      $response = CustomResponse::responseError("User not found!");
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
    $input = $this->input->post();
    if (!empty($input)) {
      $data = $this->user_model->insert($input);
      if (!empty($data)) {
        $response = CustomResponse::responseSuccess("User created successfully!", $data);
        $this->response($response, REST_Controller::HTTP_CREATED);
      } else {
        $response = CustomResponse::responseError("User created failed!");
        $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
      }
    }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_put($id)
  {

    $input = $this->put();

    if (!empty($id) && !empty($input)) {
      // $data = json_decode($input[0], true);

      $response = $this->user_model->update($input, $id);
      if ($response) {
        $cus_response = CustomResponse::responseSuccess("User updated successfully!", []);
        $this->response($cus_response, REST_Controller::HTTP_OK);
      } else {
        $cus_response = CustomResponse::responseError("User updated failed!", []);
        $this->response($cus_response, REST_Controller::HTTP_BAD_REQUEST);
      }
    } else {
      $cus_response = CustomResponse::responseError("User updated failed!", []);
      $this->response($cus_response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_delete($id)
  {

    if (!empty($id)) {
      $response = $this->user_model->delete($id);
      if ($response) {
        $response = CustomResponse::responseSuccess("User deleted successfully!", []);
        $this->response($response, REST_Controller::HTTP_OK);
      } else {
        $response = CustomResponse::responseError("User deleted failed!", []);
        $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
      }
    } else {
      $response = CustomResponse::responseError("User deleted failed!", []);
      $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
  }
}

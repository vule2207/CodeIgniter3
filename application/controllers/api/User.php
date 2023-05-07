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
    $this->load->library('upload');
    $this->load->helper('url');
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_get($id = 0)
  {
    $params = $this->input->get();
    $keywork = isset($params['keywork']) ? $params['keywork'] : '';
    $limit = isset($params['limit']) ? $params['limit'] : 10;
    $page = isset($params['page']) ? $params['page'] : 1;
    $order_by = isset($params['order_by']) ? $params['order_by'] : 'name';
    $sort_by = isset($params['sort_by']) ? $params['sort_by'] : 'asc';
    $order = $order_by && $sort_by ? array($order_by => $sort_by) : array('name' => 'asc');

    $where = array();

    if ($keywork) {
      $where["users.name LIKE '%$keywork%'"] = NULL;
    }

    $rows = $this->user_model->getUser($id, NULL, $where, $page, $limit, $order);
    $total_rows = $this->user_model->get_total_rows();
    $total_page = ceil((int) $total_rows / (int) $limit);

    /**
     * Set attribute
     *  - Loction
     *  - Pagination
     *  - Status
     */
    $attr['pagination'] = array(
      'current_page' => (int) $page,
      'per_page' => (int) $limit,
      'total_rows' => (int) $total_rows,
      'total_page' => (int) $total_page,
    );

    /**
     * Set ordering attribute
     */
    if (empty($sort_by) || empty($order_by)) {
      $attr['order'] = array(
        'order_by' => 'regdate',
        'sort_by' => 'desc'
      );
    } else {
      $attr['order'] = array(
        'order_by' => $order_by,
        'sort_by' => $sort_by
      );
    }

    $response = array(
      'success' => TRUE,
      'message' => "Get user successfull!",
      'rows' => $rows,
      'attr' => $attr
    );

    $this->response($response, REST_Controller::HTTP_OK);


    // if (!empty($users)) {
    //   if(isset($users['pagination'])) {
    //     $response = CustomResponse::responseSuccess("Get user successfully!", $users['data'], $users['pagination']);
    //   } else {
    //     $response = CustomResponse::responseSuccess("Get user successfully!", $users);
    //   }
    //   $this->response($response, REST_Controller::HTTP_OK);
    // } 
    // else {
    //   $response = CustomResponse::responseError("User not found!");
    //   $this->response($response, REST_Controller::HTTP_BAD_REQUEST);
    // }
  }

  /**
   * Get All Data from this method.
   *
   * @return Response
   */
  public function index_post()
  {
    $data_add = $this->post();

    if ($_FILES['avatar']) {
      $upload_path = 'public/images/avatar';
      $file_name = 'avatar-' . strtolower($this->input->post('name')) . '-' . time();
      $config = array(
        'upload_path' => $upload_path,
        'allowed_types' => 'jpg|jpeg|png|gif',
        'file_name' => $file_name,
      );

      $this->upload->initialize($config);

      if ($this->upload->do_upload('avatar')) {
        $uploadData = $this->upload->data();
        $data_add["avatar"] = base_url() . $upload_path . '/' . $uploadData['file_name'];
      } else {
        $data_add["avatar"] = '';
        echo $this->upload->display_errors();
      }
    }

    if (!empty($data_add)) {
      $data = $this->user_model->insert($data_add);
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
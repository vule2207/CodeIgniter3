<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'helpers/customResponse.php';

class User extends REST_Controller
{


  public function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->model('user_model');
    $this->load->library('upload');
    $this->load->helper('url');
    $this->load->library('Authorization_Token');
    $this->load->library('form_validation');
  }


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


  public function index_post()
  {
    $data_add = $this->post();

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


  public function register_post()
  {

    // set validation rules
    $this->form_validation->set_rules('username', 'Username', 'trim|required|alpha_numeric|min_length[4]|is_unique[users.username]', array('is_unique' => 'This username already exists. Please choose another one.'));
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
    //$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|required|min_length[6]|matches[password]');

    if ($this->form_validation->run() === false) {

      // validation not ok, send validation errors to the view
      $this->response(['Validation rules violated'], REST_Controller::HTTP_OK);

    } else {

      // set variables from the form
      $username = $this->input->post('username');
      $email = $this->input->post('email');
      $password = $this->input->post('password');

      if ($res = $this->user_model->create_user($username, $email, $password)) {

        // user creation ok
        $token_data['uid'] = $res;
        $token_data['username'] = $username;
        $tokenData = $this->authorization_token->generateToken($token_data);
        $final = array();
        $final['access_token'] = $tokenData;
        $final['status'] = true;
        $final['uid'] = $res;
        $final['message'] = 'Thank you for registering your new account!';
        $final['note'] = 'You have successfully register. Please check your email inbox to confirm your email address.';

        $this->response($final, REST_Controller::HTTP_OK);

      } else {

        // user creation failed, this should never happen
        $this->response(['There was a problem creating your new account. Please try again.'], REST_Controller::HTTP_OK);
      }

    }

  }

  /**
   * login function.
   * 
   * @access public
   * @return void
   */
  public function login_post()
  {

    // set validation rules
    $this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric');
    $this->form_validation->set_rules('password', 'Password', 'required');

    if ($this->form_validation->run() == false) {

      // validation not ok, send validation errors to the view
      $this->response(['Validation rules violated'], REST_Controller::HTTP_OK);

    } else {

      // set variables from the form
      $username = $this->input->post('username');
      $password = $this->input->post('password');

      if ($this->user_model->resolve_user_login($username, $password)) {

        $user_id = $this->user_model->get_user_id_from_username($username);
        $user = $this->user_model->get_user_by_id($user_id);

        // set session user datas
        $_SESSION['user_id'] = (int) $user->id;
        $_SESSION['username'] = (string) $user->username;
        $_SESSION['logged_in'] = (bool) true;
        $_SESSION['is_confirmed'] = (bool) $user->is_confirmed;
        $_SESSION['is_admin'] = (bool) $user->is_admin;

        // user login ok
        $token_data['uid'] = $user_id;
        $token_data['username'] = $user->username;
        $tokenData = $this->authorization_token->generateToken($token_data);
        $final = array();
        $final['access_token'] = $tokenData;
        $final['status'] = true;
        $final['message'] = 'Login success!';
        $final['note'] = 'You are now logged in.';

        $this->response($final, REST_Controller::HTTP_OK);

      } else {

        // login failed
        $this->response(['Wrong username or password.'], REST_Controller::HTTP_OK);

      }

    }

  }

  /**
   * logout function.
   * 
   * @access public
   * @return void
   */
  public function logout_post()
  {

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

      // remove session datas
      foreach ($_SESSION as $key => $value) {
        unset($_SESSION[$key]);
      }

      // user logout ok
      $this->response(['Logout success!'], REST_Controller::HTTP_OK);

    } else {

      // there user was not logged in, we cannot logged him out,
      $this->response(['There was a problem. Please try again.'], REST_Controller::HTTP_OK);
    }

  }


}
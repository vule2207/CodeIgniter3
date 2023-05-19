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
    $this->load->library('upload');
    $this->load->model('user_model');
    $this->load->helper('url');
    $this->load->library('Authorization_Token');
    $this->load->library('form_validation');

    // validate token 
    $headers = $this->input->request_headers();
    if (isset($headers['Authorization'])) {
      $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
      if (!$decodedToken['status']) {
        $this->response(
          array(
            'success' => false,
            'message' => 'Authentication failed'
          ), REST_Controller::HTTP_OK
        );
      }
    } else {
      $this->response(
        array(
          'success' => false,
          'message' => 'Authentication failed'
        ), REST_Controller::HTTP_OK
      );
    }
  }


  public function index_get($id = 0)
  {
    $headers = $this->input->request_headers();
    if (isset($headers['Authorization'])) {
      $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
      if ($decodedToken['status']) {
        // ------- Main Logic part -------
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
        // ------------- End -------------
      } else {
        $this->response($decodedToken);
      }
    } else {
      $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
    }

  }


  public function index_post()
  {
    $headers = $this->input->request_headers();
    if (isset($headers['Authorization'])) {
      $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
      if ($decodedToken['status']) {
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
      } else {
        $this->response($decodedToken);
      }
    } else {
      $this->response(
        array(
          'success' => false,
          'message' => 'Authentication failed'
        ), REST_Controller::HTTP_OK
      );
    }

  }


  public function index_put($id)
  {
    $headers = $this->input->request_headers();
    if (isset($headers['Authorization'])) {
      $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
      if ($decodedToken['status']) {
        $input = $this->put();

        if (!empty($id) && !empty($input)) {
          $data = json_decode($input[0], true);

          $response = $this->user_model->update($data, $id);
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
      } else {
        $this->response($decodedToken);
      }
    } else {
      $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
    }

  }


  public function index_delete($id)
  {
    $headers = $this->input->request_headers();
    if (isset($headers['Authorization'])) {
      $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
      if ($decodedToken['status']) {
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

      } else {
        $this->response($decodedToken);
      }
    } else {
      $this->response(['Authentication failed'], REST_Controller::HTTP_OK);
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
      $this->response(
        array(
          'success' => false,
          'message' => 'Validation rules violated'
        ), REST_Controller::HTTP_OK
      );

    } else {

      // set variables from the form
      $name = $this->input->post('name');
      $username = $this->input->post('username');
      $email = $this->input->post('email');
      $password = $this->input->post('password');

      if ($res = $this->user_model->create_user($name, $username, $email, $password)) {

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
      $response = array(
        'success' => false,
        'message' => 'Validation rules violated'
      );
      // validation not ok, send validation errors to the view
      $this->response($response, REST_Controller::HTTP_OK);

    } else {

      // set variables from the form
      $username = $this->input->post('username');
      $password = $this->input->post('password');

      if ($this->user_model->resolve_user_login($username, $password)) {

        $user_id = $this->user_model->get_user_id_from_username($username);
        $user = $this->user_model->get_user_by_id($user_id);

        // set session user datas
        $this->load->library('session');
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
        $final['success'] = true;
        $final['user_info'] = array(
          'access_token' => $tokenData,
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'session_id' => session_id()
        );
        $final['message'] = 'Login success!';
        $final['note'] = 'You are now logged in.';

        $this->response($final, REST_Controller::HTTP_OK);

      } else {
        $response = array(
          'success' => false,
          'message' => 'Wrong username or password.'
        );
        // login failed
        $this->response($response, REST_Controller::HTTP_OK);

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
    // $input = $this->post();
    // $data = json_decode($input[0], true);
    // $session_id = $data['session_id'];

    // $this->db->where('id', $session_id);
    // $query = $this->db->get('ci_sessions');

    if ($query->num_rows() > 0) {
      $row = $query->row();
      if (is_string($row->data)) {
        session_start();
        session_decode($row->data);
        $session_data = $_SESSION;
      }

      // print_r($session_data);
      // exit();

    } else {
      $this->response(
        array(
          'success' => false,
          'message' => "Authentication failed"
        ),
        401
      );
    }

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

      // remove session datas
      foreach ($_SESSION as $key => $value) {
        unset($_SESSION[$key]);
      }

      $this->db->delete('ci_sessions', array('id' => $session_id));

      // user logout ok
      $this->response(
        array(
          'success' => true,
          'message' => 'Logout success!'
        ), REST_Controller::HTTP_OK
      );

    } else {

      // there user was not logged in, we cannot logged him out,
      $this->response(
        array(
          'success' => false,
          'message' => 'There was a problem. Please try again.'
        ), REST_Controller::HTTP_OK
      );
    }

  }


}
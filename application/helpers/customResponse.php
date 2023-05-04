<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomResponse
{

  static function responseSuccess($message, $data, $pagination = null)
  {
    if(!empty($pagination)) {
      $response = [
        'success' => true,
        'message' => $message,
        'data' => $data,
        'pagination' => $pagination
      ];
    } else {
      $response = [
        'success' => true,
        'message' => $message,
        'data' => $data,
      ];
    }
    return $response;
  }

  static public function responseError($message)
  {
    $response = [
      'success' => false,
      'message' => $message,
    ];
    return $response;
  }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomResponse
{

  static function responseSuccess($message, $data)
  {
    $response = [
      'success' => true,
      'message' => $message,
      'data' => $data
    ];
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

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Paging {

  protected $CI;
  protected $page;
  protected $limit;

  public function __construct($params)
  {
    $this->CI =& get_instance();
    if(isset($params['page'])) {
      $this->page = $params['page'];
    }
    if(isset($params['limit'])) {
      $this->limit = $params['limit'];
    }
  }

  public function getPage($page)
  {
    $this->page = $page;
    return $this;
  }

  public function getLimit($limit)
  {
    $this->limit = $limit;
    return $this;
  }
}
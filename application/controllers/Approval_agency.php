<?php
require APPPATH . 'libraries/REST_Controller.php';

class Approval_agency extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        try {
            $this->load->model('Approval_model');
        } catch (Exception $e) {
            $this->response([
                'msg' => $e->getMessage(),
                'success' => FALSE,
            ], 200);
        }
    }

    public function insert_data_post()
    {
		$json_data = $this->post();
		$extra_data = json_decode($json_data[0], true);
		

		try {
			if($extra_data && !empty($extra_data))
			{
				$sortationEnum = array(
					'차변' => 3,
					'대변' => 4,
				);

				$data_insert = array();
		
				$data_insert['co_cd'] = $extra_data['filter']['cc_code']; // 01
				$data_insert['div_cd'] = $extra_data['filter']['au_code']; // 07
				$data_insert['ct_dept'] = $extra_data['filter']['emp_dept_cd']; // 17
				
				foreach ($extra_data['items_list'] as $index => $item)
				{
					$data_insert['in_dt'] = $this->date_format($item['date']); // 02
					$data_insert['in_sq'] = $index + 1; // 03
					$data_insert['reg_nb'] = $item['customer']['customer_code']; // 03
					$temp_ct_deal = '';

					switch ($item['type']['type_code'])
					{
						case '1':
							$temp_ct_deal = '21';
							break;
						case '2':
							$temp_ct_deal = '23';
							break;
						case '8':
							$temp_ct_deal = '27';
							break;
						default:
							break;
					}

					foreach ($item['journals_list'] as $index => $journal_item)
					{
						$data_insert['ln_sq'] = $index + 1; // 04
						$data_insert['drcr_fg'] = $sortationEnum[$journal_item['sortation']]; //10
						$data_insert['acct_cd'] = $journal_item['code']; // 11
						$data_insert['reg_nb'] = $journal_item['customer_code']; // 12
						$data_insert['acct_am'] = $journal_item['amount']; // 13
						$data_insert['pjt_cd'] = $data_insert['acct_cd'] == 13500 ? $data_insert['div_cd'] : $journal_item['project_code']; //19
						$data_insert['ct_deal'] = $data_insert['acct_cd'] == 13500 ? $temp_ct_deal : null; // 21
						$data_insert['nonsub_ty'] = ''; //22
						$data_insert['fr_dt'] = $this->date_format($data_insert['acct_cd'] == 13500 ? $data_insert['in_dt'] : $extra_data['filter']['req_date']); // 23
						$data_insert['jeonja_yn'] = ''; //27

						$this->Approval_model->insert_data($data_insert);
					}
				}

			}

			$this->response(array(
				'success' => true,
				'msg' => 'Insert data successfully'
			), 200);

		} catch (Exception $e) {
			$this->response(array(
				'success' => false,
				'error' => $e->getMessage()
			), 500);
		}

    }

	private function date_format($date)
	{
		return str_replace('/', '', $date);
	}
}
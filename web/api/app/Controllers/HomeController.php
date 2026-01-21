<?php

namespace tgui\Controllers;

class HomeController extends Controller
{
	public function getHome($req, $res)
	{
		//INITIAL CODE////START//
		$data=array();

		$data['info'] = array(
			'general' => [
				'type' => 'get',
				'object' => 'auth',
				'action' => 'singin',
				'time' => time()
			],
			'version' => [
				'TACVER' => TACVER,
				'APIVER' => APIVER,
			],
			'user' => [
				'id' => (isset($_SESSION['uid'])) ? $_SESSION['uid'] : 'empty',
			],
		);
		$data['error'] = array(
			'error' => [
				'status' => false,
			]
		);
		//INITIAL CODE////END//
		//////////////////////
		#check user auth#
		$this->auth->check();
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data));
			return $res;
		}
		//INITIAL CODE////END//
		$res->getBody()->write(json_encode($data));
		return $res->withStatus(200);
	}

	public function postHome($req, $res)
	{
		$data['info']='unset';

		$res->getBody()->write(json_encode($data));
		return $res->withStatus(200);
	}
}

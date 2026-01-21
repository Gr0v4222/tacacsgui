<?php
namespace tgui\Controllers\APIDev;

use tgui\Controllers\Controller;

class APIDevCtrl extends Controller
{
  public function getDevJS($req,$res)
  {
    //INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'get',
			'object' => 'api settings',
			'action' => 'info',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
    $path='/opt/tgui_data/dev/inc/js/';
    $data['files1']=scandir($path);
    unset($data['files1'][1]);
    unset($data['files1'][0]);
    $data['files1'] = array_values($data['files1']);
    $output='';
    foreach ($data['files1'] as $file) {
      $output.=file_get_contents($path.$file);
    }
    $res->getBody()->write($output); return $res->withStatus(200)->withHeader('Content-type', 'application/javascript');
  }
}

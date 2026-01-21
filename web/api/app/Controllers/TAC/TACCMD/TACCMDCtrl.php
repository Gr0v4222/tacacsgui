<?php

namespace tgui\Controllers\TAC\TACCMD;

use tgui\Models\TACCMD;
use tgui\Controllers\Controller;
use Respect\Validation\Validator as v;

class TACCMDCtrl extends Controller
{
################################################
########	Add New Service	###############START###########
	#########	GET Add New Service	#########

	#########	POST Add New CMD	#########
	public function postAdd($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'add',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
		//CHECK SHOULD I STOP THIS?//START//
		if( $this->shouldIStopThis() )
		{
			$data['error'] = $this->shouldIStopThis();
			$res->getBody()->write(json_encode($data)); return $res->withStatus(400);
		}
		//CHECK SHOULD I STOP THIS?//END//
		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$validation = $this->validator->validate($req, [
			'name' => v::noWhitespace()->notEmpty()->theSameNameUsed( '\tgui\Models\TACCMD' ),
			'cmd' => v::when( v::cmdType(0, $req->getParam('type')), v::notEmpty()->setName('Main Command'), v::alwaysValid() ),
			'junos' => v::when( v::cmdType(1, $req->getParam('type')), v::notEmpty()->setName('JunOS Commands'), v::alwaysValid() )
		]);

		if ($validation->failed()){
			$data['error']['status']=true;
			$data['error']['validation']=$validation->error_messages;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}

		$allParams = $req->getParams();
		$args = $allParams['cmd_attr'];
		unset($allParams['cmd_attr']);

		$data['cmd'] = TACCMD::create($allParams);
		$tempId = $data['cmd']->id;

		if ( count($args) )
		$this->db->table('tac_cmd_arg')->insert(
			array_map(
				function($x, $k)use ($tempId) {
					$x['tac_cmd_id'] = $tempId; $x['order'] = $k; return $x;
				}, $args, array_keys($args)
			)
		); //end of insert

		//$data['changeConfiguration']=$this->changeConfigurationFlag(['unset' => 0]);

		$logEntry=array('action' => 'add', 'obj_name' => $data['cmd']['name'], 'obj_id' => $data['cmd']['id'], 'section' => 'tacacs cmd', 'message' => 208);
		$data['logging']=$this->APILoggingCtrl->makeLogEntry($logEntry);

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	Add New CMD	###############END###########
################################################
########	Edit CMD	###############START###########
	#########	GET Edit CMD	#########
	public function getEdit($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'get',
			'object' => 'cmd',
			'action' => 'edit',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//

		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$data['cmd']=TACCMD::select()->where('id',$req->getParam('id'))->first();
		$data['cmd']->junos = explode(',', $data['cmd']->junos);
		$data['cmd_attr'] = $this->db->table('tac_cmd_arg')->
			select(['arg','action'])->orderBy('order','asc')->
			where('tac_cmd_id',$req->getParam('id'))->get();
		$data['cmd']->cmd_attr = ($data['cmd_attr']) ? $data['cmd_attr'] : [];

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}

	#########	POST Edit CMD	#########
	public function postEdit($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'edit',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
		//CHECK SHOULD I STOP THIS?//START//
		if( $this->shouldIStopThis() )
		{
			$data['error'] = $this->shouldIStopThis();
			$res->getBody()->write(json_encode($data)); return $res->withStatus(400);
		}
		//CHECK SHOULD I STOP THIS?//END//
		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$validation = $this->validator->validate($req, [
			'name' => v::notEmpty()->theSameNameUsed( '\tgui\Models\TACCMD', $req->getParam('id') ),
			'cmd' => v::when( v::cmdType(0, $req->getParam('type')), v::notEmpty()->setName('Main Command'), v::alwaysValid() ),
			'junos' => v::when( v::cmdType(1, $req->getParam('type')), v::notEmpty()->setName('JunOS Commands'), v::alwaysValid() )
		]);

		if ($validation->failed()){
			$data['error']['status']=true;
			$data['error']['validation']=$validation->error_messages;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}

		$allParams = $req->getParams();

		$args = $allParams['cmd_attr'];
		unset($allParams['cmd_attr']);
		$tempId = $req->getParam('id');

		$data['save']=TACCMD::where('id',$req->getParam('id'))->
			update($allParams);

		$this->db->table('tac_cmd_arg')->where('tac_cmd_id', $tempId)->delete();
		if ( count($args) )
		$this->db->table('tac_cmd_arg')->insert(
			array_map(
				function($x, $k)use ($tempId) {
					$x['tac_cmd_id'] = $tempId; $x['order'] = $k; return $x;
				}, $args, array_keys($args)
			)
		); //end of insert

		$data['save'] = 1;

		$data['changeConfiguration']=$this->changeConfigurationFlag(['unset' => 0]);

		$logEntry=array('action' => 'edit', 'obj_name' => $allParams['name'], 'obj_id' => $id, 'section' => 'tacacs services', 'message' => 308);
		$data['logging']=$this->APILoggingCtrl->makeLogEntry($logEntry);

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	Edit Service	###############END###########
################################################
	#########	POST Edit CMD	#########
	public function postEditType($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'edit',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
		//CHECK SHOULD I STOP THIS?//START//
		if( $this->shouldIStopThis() )
		{
			$data['error'] = $this->shouldIStopThis();
			$res->getBody()->write(json_encode($data)); return $res->withStatus(400);
		}
		//CHECK SHOULD I STOP THIS?//END//
		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$validation = $this->validator->validate($req, [
			'type' => v::numeric(),
		]);

		if ($validation->failed()){
			$data['error']['status']=true;
			$data['error']['validation']=$validation->error_messages;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}

		$data['result'] = $this->APIUsersCtrl::changeCmdType( $req->getParam('type') );

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	Edit CMD	###############END###########
################################################
########	Delete CMD	###############START###########
	#########	POST Delete CMD	#########
	public function postDelete($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'delete',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
		//CHECK SHOULD I STOP THIS?//START//
		if( $this->shouldIStopThis() )
		{
			$data['error'] = $this->shouldIStopThis();
			$res->getBody()->write(json_encode($data)); return $res->withStatus(400);
		}
		//CHECK SHOULD I STOP THIS?//END//
		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$data['result']=TACCMD::where('id',$req->getParam('id'))->delete();
		$data['id']=$req->getParam('id');
		$data['name']=$req->getParam('name');

		$data['changeConfiguration']=$this->changeConfigurationFlag(['unset' => 0]);

		$logEntry=array('action' => 'delete', 'obj_name' => $req->getParam('name'), 'obj_id' => $req->getParam('id'), 'section' => 'tacacs cmd', 'message' => 408);
		$data['logging']=$this->APILoggingCtrl->makeLogEntry($logEntry);

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	Delete Service	###############END###########
################################################
#########	POST CSV 	#########
	public function postCsv($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'csv',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//
		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//
		$data['clear'] = shell_exec( TAC_ROOT_PATH . '/main.sh delete temp');
		$path = TAC_ROOT_PATH . '/temp/';
		$filename = 'tac_cmd_'. $this->generateRandomString(8) .'.csv';

		$columns = $this->APICheckerCtrl->getTableTitles('tac_cmd');

	  $f = fopen($path.$filename, 'w');
		$idList = $req->getParam('idList');
		$array = [];
		$array = ( empty($idList) ) ? TACCMD::select($columns)->get()->toArray() : TACCMD::select($columns)->whereIn('id', $idList)->get()->toArray();

		fputcsv($f, $columns /*, ',)'*/);
	  foreach ($array as $line) {
		fputcsv($f, $line /*, ',)'*/);
	  }

		$data['filename']=$filename;
		sleep(3);
		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	CSV 	###############END###########
################################################
######## Datatables ###############START###########
	#########	POST Datatables	#########
	public function postDatatables($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'post',
			'object' => 'cmd',
			'action' => 'datatables',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//

		unset($data['error']);//BEACAUSE DATATABLES USES THAT VARIABLE//

		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13, true))
		{
			$data['data'] = [];
			$data['recordsTotal'] = 0;
			$data['recordsFiltered'] = 0;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//

		$params=$req->getParams(); //Get ALL parameters form Datatables

		$columns = $this->APICheckerCtrl->getTableTitles('tac_cmd'); //Array of all columnes that will used
		array_unshift( $columns, 'id' );
		array_push( $columns, 'created_at', 'updated_at' );
		$data['columns'] = $columns;
		$queries = (empty($params['searchTerm'])) ? [] : $params['searchTerm'];

		//Filter end
		$data['recordsTotal'] = TACCMD::count();
		//Get temp data for Datatables with Fliter and some other parameters
		$tempData = TACCMD::select($columns)->
		when( !empty($queries),
			function($query) use ($queries)
			{
				$query->where('name','LIKE', '%'.$queries.'%');
				return $query;
			});
		$data['recordsFiltered'] = $tempData->count();

		if (!empty($params['sortColumn']) and !empty($params['sortDirection']))
				$tempData = $tempData->orderBy($params['sortColumn'],$params['sortDirection']);

		$data['data'] = $tempData->get()->toArray();

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}

########	Service Datatables	###############END###########
################################################
################################################
########	List of Services	###############START###########
	#########	GET List Services#########
	public function getList($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'get',
			'object' => 'cmd',
			'action' => 'list',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//

		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13, true))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//
		///IF GROUPID SET///
		if ($req->getParam('id') != null){
			$id = explode(',', $req->getParam('id'));

			$data['results'] = TACCMD::select(['id','name AS text'])->where('type',0)->whereIn('id', $id)->get();
			// if (  !count($data['results']) ) $data['results'] = null;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}
		//////////////////////
		$query = TACCMD::select(['id','name as text'])->where('type',0);
		$data['total'] = $query->count();
		$search = $req->getParam('search');

		$query = $query->when( !empty($search), function($query) use ($search)
			{
				$query->where('name','LIKE', '%'.$search.'%');
			});

		$data['results']=$query->orderBy('name')->get();

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	List of Services	###############END###########
################################################
	#########	GET List Services#########
	public function getListJunos($req,$res)
	{
		//INITIAL CODE////START//
		$data=array();
		$data=$this->initialData([
			'type' => 'get',
			'object' => 'cmd',
			'action' => 'list',
		]);
		#check error#
		if ($_SESSION['error']['status']){
			$data['error']=$_SESSION['error'];
			$res->getBody()->write(json_encode($data)); return $res->withStatus(401);
		}
		//INITIAL CODE////END//

		//CHECK ACCESS TO THAT FUNCTION//START//
		if(!$this->checkAccess(13, true))
		{
			$res->getBody()->write(json_encode($data)); return $res->withStatus(403);
		}
		//CHECK ACCESS TO THAT FUNCTION//END//
		///IF GROUPID SET///
		if ($req->getParam('id') != null){
			$id = explode(',', $req->getParam('id'));

			$data['results'] = TACCMD::select(['id','name AS text'])->where('type',1)->whereIn('id', $id)->get();
			// if (  !count($data['results']) ) $data['results'] = null;
			$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
		}
		//////////////////////
		////LIST OF GROUPS////
		$query = TACCMD::select(['id','name as text'])->where('type',1);
		$data['total'] = $query->count();
		$search = $req->getParam('search');

		$query = $query->when( !empty($search), function($query) use ($search)
			{
				$query->where('name','LIKE', '%'.$search.'%');
			});

		$data['results']=$query->orderBy('name')->get();

		$res->getBody()->write(json_encode($data)); return $res->withStatus(200);
	}
########	List of Services	###############END###########
################################################

}//END OF CLASS//

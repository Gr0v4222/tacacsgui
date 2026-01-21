<?php
namespace tgui\Controllers\APIHA;

use tgui\Controllers\Controller;
use tgui\Controllers\APIHA\HAGeneral;
use tgui\Controllers\APIHA\HAMaster;

use tgui\Services\CMDRun\CMDRun as CMDRun;

use Respect\Validation\Validator as v;

class APIHACtrl extends Controller
{

  public function getSettings($req,$res)
  {
    //INITIAL CODE////START//
    $data=array();
    $data=$this->initialData([
      'type' => 'get',
      'object' => 'ha',
      'action' => 'settings',
    ]);
    #check error#
    if ($_SESSION['error']['status']){
      $data['error']=$_SESSION['error'];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(401);
    }
    //INITIAL CODE////END//
    //CHECK ACCESS TO THAT FUNCTION//START//
    if(!$this->checkAccess(1, true))
    {
      $res->getBody()->write(json_encode($data)); return $res->withStatus(403);
    }
    //CHECK ACCESS TO THAT FUNCTION//END//

    $data['db'] = $this->databaseHash()[0];
    $data['ha'] = $this->HAGeneral->getFullConfig();
    $data['slaves'] = $this->HAMaster->getSlaves();
		$data['master'] = $this->HASlave->getMaster();
    $data['rootpw_check'] = $this->HAGeneral->checkRoot();

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postSettings($req,$res)
  {
    //INITIAL CODE////START//
    $data=array();
    $data=$this->initialData([
      'type' => 'post',
      'object' => 'ha',
      'action' => 'settings',
    ]);
    #check error#
    if ($_SESSION['error']['status']){
      $data['error']=$_SESSION['error'];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(401);
    }
    //INITIAL CODE////END//
    //CHECK ACCESS TO THAT FUNCTION//START//
    if(!$this->checkAccess(1))
    {
      $res->getBody()->write(json_encode($data)); return $res->withStatus(403);
    }
    //CHECK ACCESS TO THAT FUNCTION//END//

    $params = $req->getParams();

    $validation = $this->validator->validate($req, [
      'role' => v::oneOf(v::notEmpty(), v::numeric()->between(0, 2)),
      'ip' => v::when( v::haRole() , v::ip()->setName('Master IP'), v::alwaysValid() ),
      'ip_m' => v::when( v::haRole($params['role'], 'slave') , v::notEmpty()->ip()->setName('Master IP'), v::alwaysValid() ),
      'psk' => v::when( v::haRole() , v::notEmpty()->setName('Preshared Key'), v::alwaysValid() ),
      'psk_s' => v::when( v::haRole($params['role'], 'slave') , v::notEmpty()->setName('Preshared Key'), v::alwaysValid() ),
      'slaves_ip' => v::when( v::haRole() , v::each(v::ip())->setName('Slaves IP'), v::alwaysValid() ),
      'emails' => v::when( v::haRole() , v::each(v::email())->setName('Emails'), v::alwaysValid() ),
    ]);

    if ($validation->failed()){
      $data['error']['status']=true;
      $data['error']['validation']=$validation->error_messages;
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    //$HA =  $this->HAGeneral;

    if ($this->HAGeneral->checkRoot()){
      $params['rootpw'] = $this->HAGeneral->getRootpw();
    }

    if (!$this->HAGeneral->checkRoot($params['rootpw'])){
      $data['error']['status']=true;
      $data['error']['validation']=['rootpw' => ['Incorrect MySQL Root Password!']];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    switch ($params['role']) {
      case '1':
        unlink($this->HASlave->masterFile);
        $data['result'] = $this->HAMaster->setup($params);
        break;
      case '2':
        unlink($this->HAMaster->slavesFile);
        unlink($this->HASlave->masterFile);
        $data['result'] = $this->HASlave->setup($params);
        break;
      default:
        $data['result'] = $this->HAGeneral->setRootpw($params['rootpw'])->disable();
        break;
    }

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function getStatus($req,$res)
  {
    //INITIAL CODE////START//
    $data=array();
    $data=$this->initialData([
      'type' => 'get',
      'object' => 'ha',
      'action' => 'status',
    ]);
    #check error#
    if ($_SESSION['error']['status']){
      $data['error']=$_SESSION['error'];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(401);
    }
    //INITIAL CODE////END//
    //CHECK ACCESS TO THAT FUNCTION//START//
    if(!$this->checkAccess(1, true))
    {
      $res->getBody()->write(json_encode($data)); return $res->withStatus(403);
    }
    //CHECK ACCESS TO THAT FUNCTION//END//

    $data['ha'] = $this->HAGeneral->getFullConfig();
    // $psk = (@$data['ha']['role'] == 1) ? $data['ha']['psk'] : $data['ha']['psk_s'];
    // $data['psk'] = $psk;
    $data['status'] = $this->HAGeneral->getStatus($data['ha']['role']);

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postSlaveDel($req,$res)
  {
    //INITIAL CODE////START//
    $data=array();
    $data=$this->initialData([
      'type' => 'get',
      'object' => 'ha',
      'action' => 'status',
    ]);
    #check error#
    if ($_SESSION['error']['status']){
      $data['error']=$_SESSION['error'];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(401);
    }
    //INITIAL CODE////END//
    //CHECK ACCESS TO THAT FUNCTION//START//
    if(!$this->checkAccess(1))
    {
      $res->getBody()->write(json_encode($data)); return $res->withStatus(403);
    }
    //CHECK ACCESS TO THAT FUNCTION//END//

    $this->HAMaster->delSlave($req->getParam('ip'));

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postSlaveUpdate($req,$res)
  {
    //INITIAL CODE////START//
    $data=array();
    $data=$this->initialData([
      'type' => 'get',
      'object' => 'ha',
      'action' => 'slave upgrade',
    ]);
    #check error#
    if ($_SESSION['error']['status']){
      $data['error']=$_SESSION['error'];
      $res->getBody()->write(json_encode($data)); return $res->withStatus(401);
    }
    //INITIAL CODE////END//
    //CHECK ACCESS TO THAT FUNCTION//START//
    if(!$this->checkAccess(1))
    {
      $res->getBody()->write(json_encode($data)); return $res->withStatus(403);
    }
    //CHECK ACCESS TO THAT FUNCTION//END//

    $allParams = $req->getParams();
    $this->HAGeneral->getFullConfig();

    $data['test'] = $allParams['ip'];
    $data['resp'] = $this->HAMaster->slaveRequest(
      $allParams['ip'],
      'upgrade',
      $this->HAGeneral->psk,
      $this->databaseHash()[0]
    );


    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postSlaveUpdateDo($req,$res)
  {
    $data = [ 'error' => false, 'messages' => [] ];

    $allParams = $req->getParams();

    $config = $this->HAGeneral->authorization($req->getParams());

    if (empty($config))
      $res->getBody()->write(json_encode([])); return $res->withStatus(401);

    $data['upgrade'] = $this->APIUpdateCtrl->gitPull();

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postInitFromSlave($req,$res)
  {
    $data = [ 'error' => true, 'messages' => [] ];

    $allParams = $req->getParams();

    $config = $this->HAGeneral->authorization($req->getParams());
    if (empty($config))
      $res->getBody()->write(json_encode($allParams['api'].$allParams['action'].$allParams['dbHash'])); return $res->withStatus(401);
      //$res->getBody()->write(json_encode([])); return $res->withStatus(401);
    if ($allParams['api'] !== APIVER){
      $data['messages'][] = 'Different versions used!';
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }
    if (empty($allParams['key'])){
      $data['messages'][] = 'Where is a installation key?!';
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    switch ($allParams['action']) {
      case 'init':
        $data['messages'][] = 'Master Key: '.Controller::uuid_hash();
        $data['messages'][] = 'Slave Key: '. $allParams['key'];
        if (!$this->HAGeneral->checkActivation([ Controller::uuid_hash(), $allParams['key'] ])){
          $data['messages'][] = 'Error: Can\'t check activation';
          $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
        }
        $this->HAMaster->setSlave(['status' => 0, 'api' => $allParams['api'], 'db' => $allParams['dbHash']]);
        $data['mysql'] = $this->HAMaster->getMysqlParams($config['psk']);
        $data['sid'] = $this->HAMaster->makeSlaveId();
        $data['api'] = APIVER;
        $data['db'] = $this->databaseHash()[0];
        break;
      case 'dump':

        $this->HAMaster->setSlave(['status' => 1, 'api' => $allParams['api'], 'db' => $allParams['dbHash']]);
        if ( $this->HAMaster->makeDump() !== '1'){
          $data['messages'][] = 'Can not create dump file!';
          $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
        }
        $file = TAC_ROOT_PATH . '/temp/'.'tgui_dump.sql';
        header("X-Sendfile: $file");
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="tgui_dump.sql"');
        exit(0);


      default:
        list($data['db'], $data['dbList']) = $this->databaseHash();
        $data['emails'] = $config['emails'];
        if ($data['db'] !== $allParams['dbHash']){
          $this->HAMaster->setSlave(['status' => 2, 'api' => $allParams['api'], 'db' => $allParams['dbHash']]);
          $data['messages'][] = 'Database error!';
          $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
        }
        $this->HAMaster->setSlave(['status' => 99, 'api' => $allParams['api'], 'db' => $allParams['dbHash']]);
        break;
    }

    $data['error'] = false;
    $this->changeConfigurationFlag(['unset' => 0]);

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postCheck($req,$res)
  {
    $data = [ 'error' => true, 'messages' => [] ];

    $allParams = $req->getParams();

    $config = $this->HAGeneral->authorization($req->getParams());
    if (empty($config))
      $res->getBody()->write(json_encode($allParams['api'].$allParams['action'].$allParams['dbHash'].$_SERVER['REMOTE_ADDR'])); return $res->withStatus(401);
      //$res->getBody()->write(json_encode([])); return $res->withStatus(401);
    $data['db'] = $this->databaseHash()[0];
    $data['api'] = APIVER;
    $data['cfg'] = $config['cfg'];
    if ($config['role'] == 1)
      $this->HAMaster->setSlave(['status' => 99, 'api' => $allParams['api'], 'db' => $allParams['dbHash']]);
    if ($config['role'] == 2) {
      $data['status'] = $this->HASlave->status($this->HAGeneral->psk, 'brief');
      $this->HASlave->setMaster(['status' => 99, 'api' => $allParams['api'], 'db' => $allParams['dbHash'], 'emails' => $allParams['emails']]);
      if (!$data['status']) {
        if (!$this->HAGeneral->checkRoot())
          $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
        $config['rootpw'] = $this->HAGeneral->getRootpw();
        $data['result'] = $this->HASlave->setup($config);
      }
    }

    $data['error'] = false;

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postLoggingEvent($req,$res)
  {
    $data = [ 'error' => false, 'messages' => [] ];

    $allParams = $req->getParams();

    $config = $this->HAGeneral->authorization($req->getParams());

    if (empty($config))
      $res->getBody()->write(json_encode([])); return $res->withStatus(401);

    $data['cmd'] = CMDRun::init()->setCmd('php')->setAttr( [
      TAC_ROOT_PATH."/parser/parser.php",
      $allParams['action'],
      $allParams['entry'],
      $_SERVER['REMOTE_ADDR']
    ] )->showCmd();
    $data['done'] = CMDRun::init()->setCmd('php')->setAttr( [
      TAC_ROOT_PATH."/parser/parser.php",
      $allParams['action'],
      $allParams['entry'],
      $_SERVER['REMOTE_ADDR']
    ] )->get();

    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

  public function postApply($req,$res)
  {
    //INITIAL CODE////START//
    $data = [ 'error' => true, 'messages' => [] ];

    $allParams = $req->getParams();

    $config = $this->HAGeneral->authorization($req->getParams());
    if (empty($config))
      $res->getBody()->write([]); return $res->withStatus(401);
    $data['apply'] = ['error'=>true, 'message'=>''];
    $data['test'] = ['error'=>true, 'message'=>''];
    $data['status'] = $this->HASlave->status($this->HAGeneral->psk, 'brief');
    $data['db'] = $this->databaseHash()[0];
    $data['api'] = APIVER;
    $data['cfg'] = $this->HAGeneral->config['cfg'];

    if (!$data['status']){
      $data['apply']['message'] .= "\n Status out of sync!";
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    if ($data['db'] != $allParams['dbHash']){
      $data['apply']['message'] .= "\n Database out of sync!";
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    if ($allParams['api'] != APIVER){
      $data['apply']['message'] .= "\n Version doesn't match!";
      $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
    }

    $data['test'] = $this->TACConfigCtrl->testConfiguration($this->TACConfigCtrl->createConfiguration());
    if ( ! $data['testStatus']['error'] )
      $data['apply'] = $this->TACConfigCtrl->applyConfiguration($this->TACConfigCtrl->createConfiguration());
    if (!$data['apply']['error'])
      $this->HAGeneral->setCfg();

    $this->HAGeneral->setCfg();
    $data['cfg'] = $this->HAGeneral->config['cfg'];


    $res->getBody()->write(json_encode($data)); return $res->withStatus(200);
  }

}

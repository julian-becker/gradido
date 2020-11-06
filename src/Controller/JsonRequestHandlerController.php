<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Http\Client;
use Cake\Core\Configure;

use Model\Transactions\TransactionTransfer;
use Model\Transactions\Transaction;
use Model\Transactions\Record;
/*!
 * @author: Dario Rekowski#
 * 
 * @date: 03.11.2019
 * 
 * @desc: Handle requests from other server put or request transaction
 */

class JsonRequestHandlerController extends AppController {
  
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('JsonRequestClient');
        $this->loadComponent('JsonRpcRequestClient');
        //$this->Auth->allow(['add', 'edit']);
        $this->Auth->allow('index');
    }
    
  
    public function index()
    {
        if($this->request->is('get')) {
          $method = $this->request->getQuery('method');
          switch($method) {
            case 'getRunningUserTasks': return $this->getRunningUserTasks();
          }
          return $this->returnJson(['state' => 'error', 'msg' => 'unknown method for get', 'details' => $method]);
        }
        else if($this->request->is('post')) {
          $jsonData = $this->request->input('json_decode');
          //var_dump($jsonData);
          if($jsonData == NULL || !isset($jsonData->method)) {
            return $this->returnJson(['state' => 'error', 'msg' => 'parameter error']);
          }
          $method = $jsonData->method;
        
          switch($method) {
            case 'putTransaction': 
              if(!isset($jsonData->transaction)) {
                return $this->returnJson(['state' => 'error', 'msg' => 'parameter error']);
              } else {
                return $this->putTransaction($jsonData->transaction);
              }
            case 'userDelete': return $this->userDelete($jsonData->user);
            case 'moveTransaction': return $this->moveTransaction($jsonData->pubkeys, $jsonData->memo, $jsonData->session_id);
            case 'checkUser': return $this->checkUser($jsonData->email, $jsonData->last_name);
            case 'getUsers' : return $this->getUsers($jsonData->page, $jsonData->limit);
            case 'getUserBalance': return $this->getUserBalance($jsonData->email, $jsonData->last_name);
            case 'errorInTransaction': return $this->errorInTransaction($jsonData);
            case 'updateReadNode': return $this->updateReadNode();
          }
          return $this->returnJson(['state' => 'error', 'msg' => 'unknown method for post', 'details' => $method]);
        }
        return $this->returnJson(['state' => 'error', 'msg' => 'no post or get']);
    }

    // Called from login server like a cron job every 10 minutes or after sending transaction to hedera
    private function updateReadNode()
    {
      $this->autoRender = false;
      $response = $this->response->withType('application/json');
      
      $transactionsTable = TableRegistry::getTableLocator()->get('Transactions');
      $last_transaction = $transactionsTable->find('all')->order(['id' => 'DESC'])->first();
      $group_alias = Configure::read('GroupAlias');
      $result = $this->JsonRpcRequestClient->request('getTransactions', ['groupAlias' => $group_alias, 'lastKnownSequenceNumber' => $last_transaction->id]);
      if(isset($result['state']) && $result['state'] == 'error') {
        return $this->returnJson(['state' => 'error', 'msg' => 'jsonrpc error', 'details' => $result]);
      }
      $part_count = -1;
      $temp_record = new Record;
      $errors = [];
      foreach($result as $_record) {
          $parse_result = $temp_record->parseRecord($_record);
          if($parse_result == true) {
            $sequenceNumber = $temp_record->getSequenceNumber();
            if($part_count == -1) {
              $part_count = $temp_record->getPartCount();
            }
            $part_count--;
           
            if($part_count == 0) {
                $finalize_result = $temp_record->finalize();
                if($finalize_result != true) {
                  $errors[] = ['msg' => 'error in finalize', 'record' => $_record, 'details' => $finalize_result, 'sequenceNumber' => $sequenceNumber];
                }
                $temp_record = new Record;
                $part_count = -1;
            }
          } else {
                $temp_record = new Record;
                $part_count = -1;
                $errors[] = ['msg' => 'error in parse record', 'record' => $_record, 'details' => $parse_result];
          }
      }
      if(count($errors)) {
        return $this->returnJson(['state' => 'error', 'msg' => 'error in parsing records', 'details' => $errors]);
      }
      return $this->returnJson(['state' => 'success']);
    }

    /*
     * payload.set("created", created);
     * payload.set("id", task_model->getID());
     * payload.set("type", task_model->getTaskTypeString());
     * payload.set("public_key", user_model->getPublicKeyHex());
     * payload.set("error", error);
     * payload.set("errorMessage", errorDetails);
     */
    //! \param $transactionCreated creation of transaction in timestamp in seconds
    //!        -1 if transaction couldn't decode
    //! \param $transactionBodyBase64Sha256 generic hash from transaction body serialized and converted to base64 
    //!        using sodium_crypto_generichash to calculate
    //         hash also in base64 format
    //! \param $error short error name in user language
    //! \param $errorDetails more detailed error message in user language
    private function errorInTransaction($jsonData) {
      $stateErrorTable = TableRegistry::getTableLocator()->get('StateErrors');
      $stateUsersTable = TableRegistry::getTableLocator()->get('StateUsers');
      $transactionTypesTable = TableRegistry::getTableLocator()->get('TransactionTypes');
      $stateError = $stateErrorTable->newEntity();
      //
      $pubkey = hex2bin($jsonData->public_key);
      $user_query = $stateUsersTable->find('all')->select(['id'])->where(['public_key' => $pubkey]);
      if($user_query->count() != 1) {
        return $this->returnJson(['state' => 'error', 'msg' => 'user not found', 'details' => 'user pubkey hex:' . $jsonData->public_key]);
      }
      $stateError->state_user_id = $user_query->first()->id;
      //$stateError->transaction_type_id
      // TODO:
      // - show state errors in navi_notify.ctp
      $transaction_type_query = $transactionTypesTable->find('all')->select(['id'])->where(['name' => $jsonData->type]);
      if($transaction_type_query->count() != 1) {
        return $this->returnJson(['state' => 'error', 'msg' => 'transaction type not found', 'details' => 'transaction type name: ' . $jsonData->type]);
      }
      $stateError->transaction_type_id = $transaction_type_query->first()->id;
      $stateError->created = $jsonData->created;
      $stateError->message_json = json_encode(['task_id' => $jsonData->id, 'error' => $jsonData->error, 'errorMessage' => $jsonData->errorMessage]);
      if(!$stateErrorTable->save($stateError)) {
        $this->returnJsonSaveError($stateError, [
            'state' => 'error', 
            'msg' => 'error saving state_error in db', 
            'details' => json_encode($stateError->getErrors())
        ]);
      }
      return $this->returnJson(['state' => 'success']);
    }
  
    private function putTransaction($transactionBase64) {
      $transaction = new Transaction($transactionBase64);
      //echo "after new transaction<br>";
      if($transaction->hasErrors()) {
        return $this->returnJson(['state' => 'error', 'msg' => 'error parsing transaction', 'details' => $transaction->getErrors()]);
      }
      //echo "after check on errors<br>";
      if(!$transaction->validate()) {
        return $this->returnJsonSaveError($transaction, ['state' => 'error', 'msg' => 'error validate transaction', 'details' => $transaction->getErrors()]);
      }
      //echo "after validate <br>";
      
      if ($transaction->save()) {
        
        
        // success
        return $this->returnJson(['state' => 'success']);
      } else {
        return $this->returnJsonSaveError($transaction, [
            'state' => 'error', 
            'msg' => 'error saving transaction in db', 
            'details' => json_encode($transaction->getErrors())
        ]);
      }
      
      return $this->returnJson(['state' => 'success']);
    }
    
    private function moveTransaction($pubkeys, $memo, $session_id) {
      //$pubkeys->sender
      //$pubkeys->receiver
      $stateUserTable = TableRegistry::getTableLocator()->get('StateUsers');
      $user = $stateUserTable->find('all')->where(['public_key' => hex2bin($pubkeys->sender)])->contain(['StateBalances']);
      if(!$user->count()) {
        return $this->returnJson(['state' => 'not found', 'msg' => 'user not found or empty balance']);
      }
      $amountCent = $user->first()->state_balances[0]->amount;
      //var_dump($user->first());
      $builderResult = TransactionTransfer::build(
                    $amountCent, 
                    $memo,
                    $pubkeys->receiver,
                    $pubkeys->sender
            );
      if($builderResult['state'] === 'success') {

        $http = new Client();
        try {
          $loginServer = Configure::read('LoginServer');
          $url = $loginServer['host'] . ':' . $loginServer['port'];
       
          $response = $http->post($url . '/checkTransaction', json_encode([
              'session_id' => $session_id,
              'transaction_base64' => base64_encode($builderResult['transactionBody']->serializeToString()),
              'balance' => $amountCent
          ]), ['type' => 'json']);
          $json = $response->getJson();
          if($json['state'] != 'success') {
            if($json['msg'] == 'session not found') {
              return $this->returnJson(['state' => 'error', 'msg' => 'session not found']);
            } else {
              //$this->Flash->error(__('login server return error: ' . json_encode($json)));
              return $this->returnJson(['state' => 'error', 'msg' => 'login server return error', 'details' => $json]);
            }
          } else {
            return $this->returnJson(['state' => 'success']);
          }

        } catch(\Exception $e) {
            $msg = $e->getMessage();
            //$this->Flash->error(__('error http request: ') . $msg);
            return $this->returnJson(['state' => 'error', 'msg' => 'error http request', 'details' => $msg]);
        }

      } else {
        return $this->returnJson(['state' => 'error', 'msg' => 'error building transaction']);
      }
    }
    
    private function userDelete($userPubkeyHex) {
      $stateUserTable = TableRegistry::getTableLocator()->get('StateUsers');
      $user = $stateUserTable->find('all')->where(['public_key' => hex2bin($userPubkeyHex)]);
      if(!$user || $user->count == 0) {
        return $this->returnJson(['state' => 'error', 'msg' => 'user not found']);
      }
      
    }
    
    private function checkUser($email, $last_name) {
      $userTable = TableRegistry::getTableLocator()->get('Users');
      $user = $userTable->find('all')
              ->where(['email' => $email])
              ->contain([])
              ->select(['first_name', 'last_name', 'email']);
      if(!$user->count()) {
        return $this->returnJson(['state' => 'not found', 'msg' => 'user not found']);
      }
      if($user->count() == 1 && $user->first()->last_name == $last_name) {
        return $this->returnJson(['state' => 'success']);
      }
      return $this->returnJson(['state' => 'not identical', 'user' => $user->toArray()]);
    }
    
    private function getUserBalance($email, $last_name) {
     
      $stateUserTable = TableRegistry::getTableLocator()->get('StateUsers');
      $stateUsers = $stateUserTable->find('all')->where(['OR' => ['email' => $email, 'last_name' => $last_name]])->contain(['StateBalances']);
      $gdds  = [];
      foreach($stateUsers as $stateUser) {
        foreach($stateUser->state_balances as $stateBalance) {
          if(!isset($gdds[$stateUser->email])) {
            $gdds[$stateUser->email] = [];
          }
          if(!isset($gdds[$stateUser->email][$stateUser->last_name])) {
            $gdds[$stateUser->email][$stateUser->last_name] = 0;
          }
          $gdds[$stateUser->email][$stateUser->last_name] += $stateBalance->amount;
        }
      }
      return $this->returnJson(['state' => 'success', 'gdds' => $gdds]);
    }
    
    private function getUsers($page, $count) {
      
      $userTable = TableRegistry::getTableLocator()->get('Users');
      $this->paginate = [
        'limit' => $count,
        'page' => $page
      ];
      $usersQuery = $userTable->find('all')
                              ->select(['first_name', 'last_name', 'email'])
                              ->order(['id']);
      try {
        return $this->returnJson(['state' => 'success', 'users' => $this->paginate($usersQuery)]);
      } catch (Exception $ex) {
        return $this->returnJson(['state' => 'exception', 'msg' => 'error paginate users', 'details' => $ex->getMessage()]);
      }
       
      
      //return $this->returnJson(['state' => 'success', 'users' => $users->toArray()]);
       
    }
    
    private function getRunningUserTasks() {
      $session = $this->getRequest()->getSession();
      $state_user_email = $session->read('StateUser.email');
      $requestResult = $this->JsonRequestClient->getRunningUserTasks($state_user_email);;
      return $this->returnJson($requestResult);
    }
    
    
    private function returnJsonSaveError($transaction, $errorArray) {
      $json = json_encode($errorArray);
      $stateUserTable = TableRegistry::getTableLocator()->get('StateUsers');
      $pub = $transaction->getFirstPublic();
      $stateUserQuery = $stateUserTable
                            ->find('all')
                            ->where(['public_key' => $pub])
                            ->contain(false);
      if($stateUserQuery->count() == 1) {
        $stateErrorsTable = TableRegistry::getTableLocator()->get('StateErrors');
        $stateErrorEntity = $stateErrorsTable->newEntity();
        $stateErrorEntity->state_user_id = $stateUserQuery->first()->id;
        $stateErrorEntity->transaction_type_id = $transaction->getTransactionBody()->getTransactionTypeId();
        $stateErrorEntity->message_json = $json;
        $stateErrorsTable->save($stateErrorEntity);
      } else {
	$errorArray['user_error'] = "user with $pub not found";
	$json = json_encode($errorArray);
      }

      return $this->returnJsonEncoded($json);
    }
    
    
    
}

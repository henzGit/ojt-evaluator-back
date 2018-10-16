<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Validation\Validator;
use Cake\Event\Event;


/**
 * Task Controller
 *
 * @property \App\Model\Table\TaskTable $Task
 */
class TaskController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(); // allow all
    }

    /**
     * Function to add a phase for a user
     */
    public function addApi()
    {
        date_default_timezone_set('Asia/Tokyo');

        // get post data
        $postData  = $this->request->data();

        // init params
        $inputTaskName = '';
        $inputStartDate = '';
        $inputEndDate = '';
        $accountId = '';
        $inputPhaseId = '';

        // get input params from post data
        if(!empty($postData)){
            $inputTaskName = $postData["inputTaskName"];
            $inputStartDate = $postData["inputStartDate"];
            $inputEndDate = $postData["inputEndDate"];
            $accountId = $postData["id"];
            $inputPhaseId = $postData["inputPhaseId"];
        }

        // validations of input params
        $validator = new Validator();
        $validator
            ->requirePresence('inputTaskName')
            ->notBlank('inputTaskName', 'We need the task name.')
            ->alphaNumeric('inputTaskName')
            ->requirePresence('inputStartDate')
            ->notBlank('inputStartDate', 'We need the start date.')
            ->requirePresence('inputEndDate')
            ->notBlank('inputEndDate', 'We need the end date.')
            ->requirePresence('id')
            ->notBlank('id', 'We need the account id.')
            ->numeric('id')
            ->requirePresence('inputPhaseId')
            ->notBlank('inputPhaseId', 'We need the phase id.')
            ->numeric('inputPhaseId')
        ;

        $errors = $validator->errors($postData);

        if($errors){
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'validaton error(s) in the back-end',
                'code' => 0,
                'content' => $errors,
            );
            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }

        // validate secret
        $status = $this->validateSecret();

        if ($status)
        {
            // save entity to DB
            $task = $this->Task->newEntity();

            $datetime = new \DateTime(
                "now", new \DateTimeZone('Asia/Tokyo')
            );
            $timeNow =  $datetime->format('Y-m-d H:i:s');

            $task->account_id = $accountId;
            $task->phase_id = $inputPhaseId;
            $task->name = $inputTaskName;
            $task->start_date = $inputStartDate;
            $task->end_date = $inputEndDate;
            $task->created_at = $timeNow;
            $task->updated_at = $timeNow;

            // check if task  with the same name exists already in DB
            if($this->Task->exists(['name'=>$inputTaskName])){
                $responseJson = array(
                    'status'=> 'nok',
                    'msg' =>
                        'This name has already been used. Please use another name',
                    'code' => 1,
                    'content' => $inputTaskName
                );
                $this->response->body(json_encode($responseJson));
                $this->response->statusCode(400);
                $this->response->type('application/json');
                return $this->response;
            }

            $id = '';
            if ($this->Task->save($task)) {
                $id = $task->id;
            }

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'Task has been successfully created',
                'content' => $id,

            );
            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        }
        else
        {
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'authentication error in the back end',
            );
            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }
    }

    /**
     * Function to validate secret
     */
    private function validateSecret(){
        $status = false;

        if($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // init params
            $id = '';
            $secret = '';
            $email = '';
            $password = '';

            // get input params from post data
            if(!empty($postData)){
                $id = $postData["id"];
                $secret = $postData["secret"];

                $account = $this->Task->Account;

                // find user by email and password
                $query = $account->findById($id);
                $user = $query->toArray()[0];

                $email = $user->email;
                $password = $user->password;

                $expectedSecret = $this->createSecret($email, $password);

                if($secret ==  $expectedSecret){
                    $status = true;
                }
            }
        }
        return $status;
    }

    /**
     * Function to create a secret sequence for authentication
     * @param string|null $email Account email.
     * @param string|null $password Account password.
     * @return string
     */
    private function createSecret($email, $password){
        return md5($email.$password);
    }

}

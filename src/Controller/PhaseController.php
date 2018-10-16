<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Validation\Validator;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Phase Controller
 *
 * @property \App\Model\Table\PhaseTable $Phase
 */
class PhaseController extends AppController
{
    private $amqpConn;
    private $queueToMentee = 'queue_to_mentee';
    private $queueToMentor = 'queue_to_mentor';
    private $rabbitMqPort = 5672;
    private $paramsRabbitMq;

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(); // allow all
    }

    public function initialize()
    {
        parent::initialize();

        $this->paramsRabbitMq = parse_url(getenv('CLOUDAMQP_URL'));

        $this->amqpConn = new AMQPStreamConnection(
            $this->paramsRabbitMq['host'],
            $this->rabbitMqPort,
            $this->paramsRabbitMq['user'],
            $this->paramsRabbitMq['pass'],
            substr($this->paramsRabbitMq['path'], 1)
        );
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
        $inputPhaseName = '';
        $inputStartDate = '';
        $inputEndDate = '';
        $accountId = '';

        // get input params from post data
        if(!empty($postData)){
            $inputPhaseName = $postData["inputPhaseName"];
            $inputStartDate = $postData["inputStartDate"];
            $inputEndDate = $postData["inputEndDate"];
            $accountId = $postData["id"];
        }

        // validations of input params
        $validator = new Validator();
        $validator
            ->requirePresence('inputPhaseName')
            ->notBlank('inputPhaseName', 'We need the phase name.')
            ->alphaNumeric('inputPhaseName')
            ->requirePresence('inputStartDate')
            ->notBlank('inputStartDate', 'We need the start date.')
            ->requirePresence('inputEndDate')
            ->notBlank('inputEndDate', 'We need the end date.');

        $errors = $validator->errors($postData);
        if($errors) {
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

        if ($status) {
            // save entity to DB
            $phase = $this->Phase->newEntity();
            $datetime = new \DateTime(
                "now", new \DateTimeZone('Asia/Tokyo')
            );
            $timeNow =  $datetime->format('Y-m-d H:i:s');

            $phase->account_id = $accountId;
            $phase->name = $inputPhaseName;
            $phase->start_date = $inputStartDate;
            $phase->end_date = $inputEndDate;
            $phase->created_at = $timeNow;
            $phase->updated_at = $timeNow;

            // check if phase  with the same name exists already in DB
            if($this->Phase->exists(['name'=>$inputPhaseName])){
                $responseJson = array(
                    'status'=> 'nok',
                    'msg' =>
                        'This name has already been used. Please use another name',
                    'code' => 1,
                    'content' => $inputPhaseName
                );
                $this->response->body(json_encode($responseJson));
                $this->response->statusCode(400);
                $this->response->type('application/json');
                return $this->response;
            }

            $id = '';
            if ($this->Phase->save($phase)) {
                $id = $phase->id;
            }

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'Phase has been successfully created',
                'content' => $id,
            );

            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        } else {
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
     * Function to get details of phase
     */
    public function getDetailsPhase()
    {
        // params validations
        if($this->request->is('post')) {
            // get post data
            $postData  = $this->request->data();

            // validations of input params
            $validator = new Validator();
            $validator
                ->requirePresence('id')
                ->notBlank('id', 'We need the account id.')
                ->numeric('id', 'Value needs to be numeric')
                ->requirePresence('secret')
                ->notBlank('secret', 'We need the authorisation cookie.')
                ->requirePresence('inputPhaseId')
                ->notBlank('inputPhaseId', 'We need the phase id.')
                ->numeric('inputPhaseId', 'Value needs to be numeric');
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
        }

        // validate secret
        $status = $this->validateSecret();

        // return json response 400
        if(!$status){
            // return success
            $responseJson = array(
                'status'=> 'nok',
                'msg' => 'authentication error in the back-end',
                'code' => -99,
                'content' => $status,
            );
            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }

        // return success
        if($this->request->is('post')){
            $postData = $this->request->data();
            $inputPhaseId = $postData['inputPhaseId'];
            $phaseController = new PhaseController();
            $phase = $phaseController->Phase->findById($inputPhaseId)->toArray()[0];
            $tasks = [];
            $taskController = new TaskController();

            if($inputPhaseId){
                $tasks = $taskController->Task
                    ->findByPhaseId($inputPhaseId)->toArray();
            }

            $otherData = array(
                'tasks' => $tasks,

            );

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'Get details of phase',
                'content' => $phase,
                'otherData' => $otherData,

            );
            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        }
    }

    /**
     * Function to evaluate a phase
     */
    public function evaluate()
    {
        // get post data
        $postData  = $this->request->data();

        // init params
        $inputPhaseId = '';
        $selectPhaseStatus = '';

        // get input params from post data
        if(!empty($postData)){
            $inputPhaseId = $postData["inputPhaseId"];
            $selectPhaseStatus = $postData["selectPhaseStatus"];
        }

        // validations of input params
        $validator = new Validator();
        $validator
            ->requirePresence('inputPhaseId')
            ->notBlank('inputPhaseId', 'We need the phase id.')
            ->numeric('inputPhaseId')
            ->requirePresence('selectPhaseStatus')
            ->notBlank('selectPhaseStatus', 'We need the phase status.')
            ->numeric('selectPhaseStatus');

        $errors = $validator->errors($postData);
        if($errors) {
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

        if(!$status) {
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'authentication error in the back-end',
                'code' => -99,
                'content' => $status,
            );

            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }

        // check if rabbitmq server is alive
        $socket = $this->isRabbitMqUp();

        if(!$socket) {
            $this->log('rabbitmq not active: '.print_r($socket, true));
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'rabbitmq server is down',
                'code' => 1,
                'content' => $socket,
            );

            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }

        // get entity phase
        $phase = $this->Phase->findById($inputPhaseId)->toArray()[0];

        $menteeId = $phase->account_id;
        $mentorId = '';

        $accountController = new AccountController();
        $mentee = $accountController->Account
            ->findById($menteeId)->toArray()[0];

        $mentorId = $mentee->mentor;

        // send a message to RabbitMQ
        $connection = $this->amqpConn;
        $channel = $connection->channel();

        $queue = $this->queueToMentee;
        $channel->queue_declare($queue, false, false, false, false);

        $postData  = array(
            'senderAccountId' => $mentorId,
            'receiverAccountId' => $menteeId,
            'phaseId' => $inputPhaseId
        );
        $postData = json_encode($postData);

        $msg = new AMQPMessage($postData);
        $channel->basic_publish($msg, '', $queue);

        // update state of evaluated phase
        $phase->status = $selectPhaseStatus;
        $phase = $this->Phase->save($phase);

        // return success
        $responseJson = array(
            'status'=> 'ok',
            'msg' => 'Phase has been successfully evaluated',
            'content' => $phase,

        );

        $this->response->body(json_encode($responseJson));
        $this->response->statusCode(200);
        $this->response->type('application/json');
        return $this->response;
    }

    /**
     * Function to submit a phase
     */
    public function submit()
    {
        // get post data
        $postData  = $this->request->data();

        // init params
        $inputPhaseId = '';

        // get input params from post data
        if(!empty($postData)){
            $inputPhaseId = $postData["inputPhaseId"];
        }

        // validations of input params
        $validator = new Validator();
        $validator
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

        if(!$status){
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'authentication error in the back-end',
                'code' => -99,
                'content' => $status,
            );

            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }

        // check if rabbitmq server is alive
        $socket = $this->isRabbitMqUp();

        if(!$socket) {
            $this->log('rabbitmq not active: '.print_r($socket, true));
            // return failure
            $errorMsg = array(
                'status'=> 'nok',
                'msg' => 'rabbitmq server is down',
                'code' => 1,
                'content' => $socket,
            );

            $this->response->body(json_encode($errorMsg));
            $this->response->statusCode(400);
            $this->response->type('application/json');

            return $this->response;
        }

        // get entity phase
        $phase = $this->Phase->findById($inputPhaseId)->toArray()[0];

        $menteeId = $phase->account_id;
        $mentorId = '';

        $accountController = new AccountController();
        $mentee = $accountController->Account->findById($menteeId)->toArray()[0];

        $mentorId = $mentee->mentor;

        // send a message to RabbitMQ
        $connection = $this->amqpConn;
        $channel = $connection->channel();

        $queue = $this->queueToMentor;
        $channel->queue_declare(
            $queue,
            false,
            false,
            false,
            false
        );

        $postData  = array(
            'senderAccountId' => $menteeId,
            'receiverAccountId' => $mentorId,
            'phaseId' => $inputPhaseId
        );
        $postData = json_encode($postData);

        $msg = new AMQPMessage($postData);
        $channel->basic_publish($msg, '', $queue);

        // update state of submitted phase
        $phase->submitted = true;
        $phase = $this->Phase->save($phase);

        // return success
        $responseJson = array(
            'status'=> 'ok',
            'msg' => 'Phase has been successfully submitted',
            'content' => $phase,
        );

        $this->response->body(json_encode($responseJson));
        $this->response->statusCode(200);
        $this->response->type('application/json');
        return $this->response;
    }

    /**
     * Check if rabbitmq server is up or not
     * @return resource
     */
    private function isRabbitMqUp()
    {
        return fsockopen(
            $this->paramsRabbitMq['host'],
            $this->rabbitMqPort
        );
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

                $account = $this->Phase->Account;

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

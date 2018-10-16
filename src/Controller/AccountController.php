<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

/**
 * Account Controller
 *
 * @property \App\Model\Table\AccountTable $Account
 */
class AccountController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(); // allow all
    }

    /**
     * Add account API using POST
     *
     */
    public function addApi()
    {
        date_default_timezone_set('Asia/Tokyo');

        // get post data
        $postData  = $this->request->data();

        // init params
        $inputFirstName = '';
        $inputLastName = '';
        $inputEmail = '';
        $inputPassword = '';
        $inputConfirmPassword = '';
        $selectAccountType = '';

        // get input params from post data
        if (!empty($postData)){
            $inputFirstName = $postData["inputFirstName"];
            $inputLastName = $postData["inputLastName"];
            $inputEmail = $postData["inputEmail"];
            $inputPassword = $postData["inputPassword"];
            $inputConfirmPassword = $postData["inputConfirmPassword"];
            $selectAccountType = $postData["selectAccountType"];
        }

        // data for validations
        $data = array(
            'inputFirstName'=> $inputFirstName,
            'inputLastName'=> $inputLastName,
            'inputEmail'=> $inputEmail,
            'inputPassword'=> $inputPassword,
            'inputConfirmPassword'=> $inputConfirmPassword,
            'selectAccountType'=> $selectAccountType,
        );

        // validations of input params
        $validator = new Validator();
        $validator
            ->requirePresence('inputFirstName')
            ->notBlank('inputFirstName', 'We need your first name.')
            ->alphaNumeric('inputFirstName')
            ->requirePresence('inputLastName')
            ->notBlank('inputLastName', 'We need your last name.')
            ->alphaNumeric('inputLastName')
            ->requirePresence('inputEmail')
            ->notBlank('inputEmail', 'We need your email.')
            ->email('inputEmail')
            ->requirePresence('inputPassword')
            ->notBlank('inputPassword', 'We need your password.')
            ->requirePresence('inputConfirmPassword')
            ->notBlank('inputConfirmPassword', 'We need your confirm password.')
            ->requirePresence('selectAccountType')
            ->notBlank('selectAccountType', 'We need your account type.')
            ->numeric('selectAccountType', 'Value needs to be numeric');

        $errors = $validator->errors($data);

        if (empty($errors)) {
            // save entity to DB
            $accountTable = TableRegistry::get('Account');
            $account = $accountTable->newEntity();

            $datetime = new \DateTime(
                "now", new \DateTimeZone('Asia/Tokyo')
            );
            $timeNow =  $datetime->format('Y-m-d H:i:s');

            $account->first_name = $inputFirstName;
            $account->last_name = $inputLastName;
            $account->account_type = $selectAccountType;
            $account->last_name = $inputLastName;
            $account->email = $inputEmail;
            $account->password = md5($inputPassword);
            $account->created_at = $timeNow;
            $account->updated_at = $timeNow;

            // check if account with the same email exists already in DB
            if ($this->Account->exists(['email'=>$inputEmail])){
                $responseJson = array(
                    'status'=> 'nok',
                    'msg' =>
                        'This email has already been used. Please use another email',
                    'code' => 1,
                    'content' => $inputEmail
                );
                $this->response->body(json_encode($responseJson));
                $this->response->statusCode(400);
                $this->response->type('application/json');
                return $this->response;
            }

            if ($accountTable->save($account)) {
                $id = $account->id;
            }

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => $data,
            );

            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        } else {
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

    /**
     * Login account API using POST
     */
    public function loginApi()
    {
        date_default_timezone_set('Asia/Tokyo');

        // get post data
        $postData  = $this->request->data();

        // init params
        $inputEmail = '';
        $inputPassword = '';

        // get input params from post data
        if (!empty($postData)){
            $inputEmail = $postData["inputEmail"];
            $inputPassword = $postData["inputPassword"];
        }

        // data for validations
        $data = array(
            'inputEmail'=> $inputEmail,
            'inputPassword'=> $inputPassword,
        );

        // validations of input params
        $validator = new Validator();
        $validator
            ->requirePresence('inputEmail')
            ->notBlank('inputEmail', 'We need your email.')
            ->email('inputEmail')
            ->requirePresence('inputPassword')
            ->notBlank('inputPassword', 'We need your password.');
        $errors = $validator->errors($data);

        if (empty($errors))
        {
            $accounts = TableRegistry::get('Account');

            // find user by email and password
            $query = $accounts
                ->findAllByEmailAndPassword($inputEmail, md5($inputPassword));
            $resultQuery = $query->toArray();

            // return error if cannot find user
            if (count($resultQuery) !== 1){
                // return failure
                $errorMsg = array(
                    'status'=> 'nok',
                    'msg' =>
                        'The email or the password is not correct. Please try again',
                    'code' => 1,
                    'content' => [],
                );

                $this->response->body(json_encode($errorMsg));
                $this->response->statusCode(400);
                $this->response->type('application/json');
                return $this->response;
            }

            $user = $resultQuery[0];
            // set user for the session
            $this->Auth->setUser($user);

            // create secret
            $secret = $this->createSecret($inputEmail, md5($inputPassword));
            $accountType = $user->account_type;

            // init param
            $otherData = array(
                'partnerFirstName'=> '',
                'partnerLastName'=> ''
            );

            if ($accountType === 1){
                $mentorId = $user->mentor;
                if ($mentorId){
                    $mentorEntity = $this->Account
                        ->findById($mentorId)->toArray()[0];

                    if ($mentorEntity){
                        $otherData = array(
                            'partnerFirstName'=> $mentorEntity->first_name,
                            'partnerLastName'=> $mentorEntity->last_name
                        );
                    }
                }
            } elseif ($accountType === 2){
                $menteeId = $user->mentee;
                if ($menteeId){
                    $menteeEntity = $this->Account
                        ->findById($menteeId)->toArray()[0];

                    if ($menteeEntity){
                        $otherData = array(
                            'partnerFirstName'=> $menteeEntity->first_name,
                            'partnerLastName'=> $menteeEntity->last_name
                        );
                    }
                }
            }

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'login is succesful',
                'content' => $user,
                'secret' => $secret,
                'otherData' =>  $otherData
            );

            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        } else{
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

    /**
     * Function to get list of available mentors
     */
    public function getAvailableMentors(){

        // validations
        if ($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // validations of input params
            $validator = new Validator();
            $validator
                ->requirePresence('id')
                ->notBlank('id', 'We need the account id.')
                ->requirePresence('secret')
                ->notBlank('secret',
                    'We need the authorisation cookie.');

            $errors = $validator->errors($postData);
            if ($errors){
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

        $status = $this->validateSecret();
        $mentors = [];

        if ($status){
            $query = $this->Account->find(
                'all',
                array('conditions'=>array('account_type = 2', 'mentee is null'))
            );
            $mentors = $query->toArray();
            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'get list of available mentors',
                'content' => $mentors,
            );
        } else {
            // return success
            $responseJson = array(
                'status'=> 'nok',
                'msg' => 'no mentor is available',
                'content' => $mentors,
            );
        }
        $this->response->body(json_encode($responseJson));
        $this->response->statusCode(200);
        $this->response->type('application/json');
        return $this->response;
    }

    /**
     * Function to register mentor
     */
    public function registerMentor(){

        // validations
        if ($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // validations of input params
            $validator = new Validator();
            $validator
                ->requirePresence('id')
                ->notBlank('id', 'We need the account id.')
                ->requirePresence('secret')
                ->notBlank(
                    'secret',
                    'We need the authorisation cookie.')
                ->requirePresence('selectMentorId')
                ->notBlank('selectMentorId', 'We need the mentor id.');
            $errors = $validator->errors($postData);

            if ($errors){
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

        $status = $this->validateSecret();

        if ($status){
            // get post data
            $postData  = $this->request->data();

            // init params
            $id = '';
            $mentorId  = '';

            // get input params from post data
            if (!empty($postData)){
                $id = $postData["id"];

                $selectMentorId = $postData["selectMentorId"];

                // find mentee account by id
                $query = $this->Account->findById($id);
                $mentee = $query->toArray()[0];

                // set mentee data
                $mentee->mentor = $selectMentorId;
                $statusUpdate = $this->Account->save($mentee);

                // find mentor account by id
                $query = $this->Account->findById($selectMentorId);
                $mentor = $query->toArray()[0];

                // set mentee data
                $mentor->mentee = $id;
                $statusUpdate = $this->Account->save($mentor);
            }

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'mentor registration is successful',
            );
        } else {
            // return success
            $responseJson = array(
                'status'=> 'nok',
                'msg' => 'mentor registration failed',
            );
        }

        $this->response->body(json_encode($responseJson));
        $this->response->statusCode(200);
        $this->response->type('application/json');
        return $this->response;
    }

    /**
     * Function to check validity of secret (called from outside)
     * @return boolean
     */
    public function authenticateSecret(){

        // validations
        if ($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // validations of input params
            $validator = new Validator();
            $validator
                ->requirePresence('id')
                ->notBlank('id', 'We need the account id.')
                ->requirePresence('secret')
                ->notBlank('secret',
                    'We need the authorisation cookie.');
            $errors = $validator->errors($postData);

            if ($errors){
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

        $status = $this->validateSecret();

        if ($status){
            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'user is authenticated',
            );
            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        } else{
            // return success
            $responseJson = array(
                'status'=> 'nok',
                'msg' => 'authentication fails',
            );

            $this->response->body(json_encode([]));
            $this->response->statusCode(400);
            $this->response->type('application/json');
            return $this->response;
        }
    }

    /**
     * Function to get details of an account
     */
    public function getDetailsAccount()
    {
        // params validations
        if ($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // validations of input params
            $validator = new Validator();
            $validator
                ->requirePresence('id')
                ->notBlank('id', 'We need the account id.')
                ->requirePresence('secret')
                ->notBlank('secret', 'We need the authorisation cookie.')
            ;
            $errors = $validator->errors($postData);

            if ($errors){
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
        if (!$status){
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
        if ($this->request->is('post')){
            $postData = $this->request->data();
            $id = $postData['id'];
            $user = $this->Account->findById($id)->toArray()[0];
            // data for mentor
            $accountType = $user->account_type;

            // init params
            $partnerFirstName = '';
            $partnerLastName = '';
            $partnerId = '';
            $phases = [];

            $phaseController = new PhaseController();
            if ($accountType === 1)
            {
                $partnerId = $user->mentor;
                $phases = $phaseController->Phase->findByAccountId($id)->toArray();

            } elseif ($accountType === 2) {
                $partnerId = $user->mentee;
                $phases = $phaseController->Phase
                    ->findByAccountIdAndSubmitted($partnerId, 1)->toArray();
            }

            if ($partnerId){
                $partner = $this->Account->findById($partnerId)->toArray()[0];
                $partnerFirstName = $partner->first_name;
                $partnerLastName = $partner->last_name;
            }

            $otherData = array(
                'partnerFirstName' => $partnerFirstName,
                'partnerLastName' => $partnerLastName,
                'phases' => $phases,
            );

            // return success
            $responseJson = array(
                'status'=> 'ok',
                'msg' => 'Get details of user',
                'content' => $user,
                'otherData' => $otherData,
            );

            $this->response->body(json_encode($responseJson));
            $this->response->statusCode(200);
            $this->response->type('application/json');
            return $this->response;
        }
    }

    /**
     * Function to validate secret
     */
    private function validateSecret(){
        $status = false;

        if ($this->request->is('post')){
            // get post data
            $postData  = $this->request->data();

            // init params
            $id = '';
            $secret = '';
            $email = '';
            $password = '';

            // get input params from post data
            if (!empty($postData)){
                $id = $postData["id"];
                $secret = $postData["secret"];

                $account = TableRegistry::get('Account');

                // find user by email and password
                $query = $account->findById($id);
                $user = $query->toArray()[0];

                $email = $user->email;
                $password = $user->password;

                $expectedSecret = $this->createSecret($email, $password);

                if ($secret ==  $expectedSecret){
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

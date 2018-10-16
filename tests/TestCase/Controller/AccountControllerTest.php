<?php
namespace App\Test\TestCase\Controller;

use App\Controller\AccountController;
use Cake\Http\Client;
use Cake\TestSuite\IntegrationTestCase;

use Cake\Http\Client\Request;

/**
 * App\Controller\AccountController Test Case
 */
class AccountControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.account',
        'app.phase',
        'app.task'
    ];


    /**
     * Test add-api method
     *
     * @return void
     */
    public function testAddApi()
    {
        $urlPath = 'http://localhost:8888/account/add-api';

        // Test params validation
        $http = new Client();

        // one field is missing
        $response = $http->post($urlPath, [
            'inputFirstName' => '',
            'inputLastName' => 'henz',
            'inputEmail' => 'henz2@gmail.com',
            'inputPassword' => '123',
            'inputConfirmPassword' => '123',
            'selectAccountType' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // wrong email format
        $response = $http->post($urlPath, [
            'inputFirstName' => 'albet',
            'inputLastName' => 'henz',
            'inputEmail' => 'henz2@gmailcom',
            'inputPassword' => '123',
            'inputConfirmPassword' => '123',
            'selectAccountType' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // account type needs to be numeric
        $response = $http->post($urlPath, [
            'inputFirstName' => 'albet',
            'inputLastName' => 'henz',
            'inputEmail' => 'henz2@gmail.com',
            'inputPassword' => '123',
            'inputConfirmPassword' => '123',
            'selectAccountType' => 'x',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // account type needs to be numeric
        $response = $http->post($urlPath, [
            'inputFirstName' => 'albet',
            'inputLastName' => 'henz',
            'inputEmail' => 'henz2@gmail.com',
            'inputPassword' => '123',
            'inputConfirmPassword' => '123',
            'selectAccountType' => '1',
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test login-api method
     *
     * @return void
     */
    public function testLoginApi(){
        $urlPath = 'http://localhost:8888/account/login-api';

        // Test params validation
        $http = new Client();

        // input email is missing
        $response = $http->post($urlPath, [
            'inputEmail' => '',
            'inputPassword' => '123',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // input email is missing
        $response = $http->post($urlPath, [
            'inputEmail' => 'adte@test.com',
            'inputPassword' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // email format is incorrect
        $response = $http->post($urlPath, [
            'inputEmail' => 'rwerewr',
            'inputPassword' => '123',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

    }

    /**
     * Test authenticate secret method
     *
     * @return void
     */
    public function testAuthenticateSecret(){
        $urlPath = 'http://localhost:8888/account/authenticate-secret';

        // Test params validation
        $http = new Client();

        // id is missing
        $response = $http->post($urlPath, [
            'id' => '',
            'secret' => '123',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // secret is missing
        $response = $http->post($urlPath, [
            'id' => '2',
            'secret' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test get available mentors method
     *
     * @return void
     */
    public function testGetAvailableMentors(){
        $urlPath = 'http://localhost:8888/account/get-available-mentors';

        // Test params validation
        $http = new Client();

        // id is missing
        $response = $http->post($urlPath, [
            'id' => '',
            'secret' => '123',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // secret is missing
        $response = $http->post($urlPath, [
            'id' => '2',
            'secret' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test register mentor method
     *
     * @return void
     */
    public function testRegisterMentor(){
        $urlPath = 'http://localhost:8888/account/register-mentor';

        // Test params validation
        $http = new Client();

        // id is missing
        $response = $http->post($urlPath, [
            'id' => '',
            'secret' => '123',
            'selectMentorId' => '1',

        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // secret is missing
        $response = $http->post($urlPath, [
            'id' => '2',
            'secret' => '',
            'selectMentorId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // mentor id is missing
        $response = $http->post($urlPath, [
            'id' => '2',
            'secret' => '4544546454',
            'selectMentorId' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test get account details method
     *
     * @return void
     */
    public function testGetDetailsAccount(){
        $urlPath = 'http://localhost:8888/account/get-details-account';

        // Test params validation
        $http = new Client();

        // id is missing
        $response = $http->post($urlPath, [
            'id' => '',
            'secret' => '123',

        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // secret is missing
        $response = $http->post($urlPath, [
            'id' => '2',
            'secret' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }
}

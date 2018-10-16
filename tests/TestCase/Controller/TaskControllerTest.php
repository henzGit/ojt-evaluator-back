<?php
namespace App\Test\TestCase\Controller;

use App\Controller\TaskController;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Http\Client;

/**
 * App\Controller\TaskController Test Case
 */
class TaskControllerTest extends IntegrationTestCase
{
    /**
     * Test add-api method
     *
     * @return void
     */
    // Problem with this function test (always return 200 instead of 400)
    public function testAddApi()
    {
        $urlPath = 'http://localhost:8888/task/add-api';

        // Test params validation
        $http = new Client();

        // task name is missing
        $response = $http->post($urlPath, [
            'inputTaskName' => '',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // start date is missing
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // end date is missing
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '',
            'id' => '1',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // account id is missing
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // account id is not numeric
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => 'fdsf',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase id is missing
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
            'inputPhaseId' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase id is not numeric
        $response = $http->post($urlPath, [
            'inputTaskName' => 'task1',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
            'inputPhaseId' => 'fsfdsf',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }
}

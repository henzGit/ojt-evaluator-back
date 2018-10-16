<?php
namespace App\Test\TestCase\Controller;

use App\Controller\PhaseController;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Http\Client;


/**
 * App\Controller\PhaseController Test Case
 */
class PhaseControllerTest extends IntegrationTestCase
{
    /**
     * Test add-api method
     *
     * @return void
     */
    // Problem with this function test (always return 200 instead of 400)
    public function testAddApi()
    {
        $urlPath = 'http://localhost:8888/phase/add-api';

        // Test params validation
        $http = new Client();

        // phase name is missing
        $response = $http->post($urlPath, [
            'inputPhaseName' => '',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '1',

        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase name contains special char
        $response = $http->post($urlPath, [
            'inputPhaseName' => '2222#',
            'inputStartDate' => '2016/12/01',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // start date is missing
        $response = $http->post($urlPath, [
            'inputPhaseName' => 'phase1',
            'inputStartDate' => '',
            'inputEndDate' => '2016/12/05',
            'id' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // end date is missing
        $response = $http->post($urlPath, [
            'inputPhaseName' => 'phase1',
            'inputStartDate' => '2016/12/05',
            'inputEndDate' => '',
            'id' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test submit method
     *
     * @return void
     */
    public function testSubmit()
    {
        $urlPath = 'http://localhost:8888/phase/submit';

        // Test params validation
        $http = new Client();

        // phase id is missing
        $response = $http->post($urlPath, [
            'inputPhaseId' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase id is not numeric
        $response = $http->post($urlPath, [
            'inputPhaseId' => 're',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }


    /**
     * Test get details phase method
     */
    public function testGetDetailsPhase()
    {
        $urlPath = 'http://localhost:8888/phase/get-details-phase';

        // Test params validation
        $http = new Client();

        // phase id is missing
        $response = $http->post($urlPath, [
            'inputPhaseId' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase id is not numeric
        $response = $http->post($urlPath, [
            'inputPhaseId' => 're',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test evaluate phase method
     *
     * @return void
     */
    public function testEvaluate()
    {
        $urlPath = 'http://localhost:8888/phase/evaluate';

        // Test params validation
        $http = new Client();

        // phase id is missing
        $response = $http->post($urlPath, [
            'inputPhaseId' => '',
            'selectPhaseStatus' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase id is not numeric
        $response = $http->post($urlPath, [
            'inputPhaseId' => 're',
            'selectPhaseStatus' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        // phase status is missing
        $response = $http->post($urlPath, [
            'inputPhaseId' => '1',
            'selectPhaseStatus' => '',
        ]);
        $this->assertEquals(400, $response->getStatusCode());


        // phase id is not numeric
        $response = $http->post($urlPath, [
            'selectPhaseStatus' => 're',
            'inputPhaseId' => '1',
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

}

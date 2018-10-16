<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PhaseTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PhaseTable Test Case
 */
class PhaseTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\PhaseTable
     */
    public $Phase;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.phase',
        'app.accounts',
        'app.task'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Phase') ?
            [] : ['className' => 'App\Model\Table\PhaseTable'];
        $this->Phase = TableRegistry::get('Phase', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Phase);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

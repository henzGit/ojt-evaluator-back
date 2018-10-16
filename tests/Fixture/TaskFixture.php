<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TaskFixture
 *
 */
class TaskFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'task';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => [
            'type' => 'biginteger',
            'length' => 20,
            'unsigned' => true,
            'null' => false,
            'default' => null,
            'comment' => '',
            'autoIncrement' => true,
            'precision' => null
        ],
        'account_id' => [
            'type' => 'integer',
            'length' => 8,
            'unsigned' => true,
            'null' => true,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'phase_id' => [
            'type' => 'integer',
            'length' => 10,
            'unsigned' => true,
            'null' => true,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
        ],
        'name' => [
            'type' => 'text',
            'length' => 255,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'precision' => null
        ],
        'start_date' => [
            'type' => 'date',
            'length' => null,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        'end_date' => [
            'type' => 'date',
            'length' => null,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        'created_at' => [
            'type' => 'datetime',
            'length' => null,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        'updated_at' => [
            'type' => 'datetime',
            'length' => null,
            'null' => false,
            'default' => null,
            'comment' => '',
            'precision' => null
        ],
        '_indexes' => [
            'account_id_in_task' => [
                'type' => 'index',
                'columns' => ['account_id'],
                'length' => []
            ],
            'phase_id_in_task' => [
                'type' => 'index',
                'columns' => ['phase_id'],
                'length' => []
            ],
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
                'length' => []
            ],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'account_id' => 1,
            'phase_id' => 1,
            'name' => 'Task 1',
            'start_date' => '2016-12-14',
            'end_date' => '2016-12-14',
            'created_at' => '2016-12-14 05:59:04',
            'updated_at' => '2016-12-14 05:59:04'
        ],
    ];
}

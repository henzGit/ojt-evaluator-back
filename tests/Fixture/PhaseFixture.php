<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PhaseFixture
 *
 */
class PhaseFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'phase';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 10,
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
        'name' => [
            'type' => 'text',
            'length' => 255,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'precision' => null
        ],
        'submitted' => [
            'type' => 'boolean',
            'length' => null,
            'null' => true,
            'default' => '0',
            'comment' => '',
            'precision' => null
        ],
        'status' => [
            'type' => 'integer',
            'length' => 4,
            'unsigned' => false,
            'null' => true,
            'default' => null,
            'comment' => '',
            'precision' => null,
            'autoIncrement' => null
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
            'account_id_in_phase' => [
                'type' => 'index',
                'columns' => ['account_id'],
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
            'name' => 'phase 1',
            'submitted' => 1,
            'status' => 1,
            'start_date' => '2016-12-08',
            'end_date' => '2016-12-08',
            'created_at' => '2016-12-08 04:29:45',
            'updated_at' => '2016-12-08 04:29:45'
        ],
    ];
}

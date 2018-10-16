<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Phase Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Account
 * @property \Cake\ORM\Association\HasMany $Task
 *
 * @method \App\Model\Entity\Phase get($primaryKey, $options = [])
 * @method \App\Model\Entity\Phase newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Phase[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Phase|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Phase patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Phase[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Phase findOrCreate($search, callable $callback = null)
 */
class PhaseTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('phase');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->belongsTo('Account', [
            'foreignKey' => 'account_id'
        ]);
        $this->hasMany('Task', [
            'foreignKey' => 'phase_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->boolean('submitted')
            ->allowEmpty('submitted');

        $validator
            ->integer('status')
            ->allowEmpty('status');

        $validator
            ->date('start_date')
            ->requirePresence('start_date', 'create')
            ->notEmpty('start_date');

        $validator
            ->date('end_date')
            ->requirePresence('end_date', 'create')
            ->notEmpty('end_date');

        $validator
            ->dateTime('created_at')
            ->requirePresence('created_at', 'create')
            ->notEmpty('created_at');

        $validator
            ->dateTime('updated_at')
            ->requirePresence('updated_at', 'create')
            ->notEmpty('updated_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['account_id'], 'Account'));

        return $rules;
    }
}

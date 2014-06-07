<?php
namespace Backend\Form;

use Models\Model\User;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
//use User
class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
	
	protected $dbAdapter;
	
	protected $userId;
	
	public function init()
	{
		parent::__construct('user');
		
		$this->setHydrator(new ClassMethodsHydrator(false))
		->setObject(new User());
		
		$this->add(array(
				'name' => 'fld_name',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Name',
				)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Email',
				'name' => 'email',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Email',
				)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Password',
				'name' => 'old_password',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Old Password',
				)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Password',
				'name' => 'password',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Password',
				)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Password',
				'name' => 'confirm_password',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Confirm Password',
				)
		));
		
		$this->add(array(
				'name' => 'phone',
				'attributes' => array(
						'class' => 'formInput',
						'placeholder' => 'Phone'
				)
		));
		
		
//		$this->add(array(
//				'type' => 'Zend\Form\Element\Collection',
//				'name' => 'roles',
//				'options'=> array(
//					'count'=>1,
//					'should_create_template'=>true,
//					'allow_add'=>true,
//					'target_element'=> array(
//						'type'=>'UserRoleFieldset'
//					)
//		)));
	}
	
	
	/**
	 * @return array
	 */
	public function getInputFilterSpecification(){
		return array(
				'displayName' => array(
						'required' => true,
						'validators' => array(
								array(
									'name' => 'NotEmpty',
									'options' => array(
										'messages' => array(
												\Zend\Validator\NotEmpty::IS_EMPTY => 'Please fill the user name',
										),
									 ),
									'break_chain_on_failure' => true
								),
								array(
										'name'    => 'Zend\Validator\StringLength',
										'options' => array(
												'encoding' => 'UTF-8',
												'min'      => 3,
												'max'      => 30,
												'messages' => array(
														\Zend\Validator\StringLength::TOO_LONG => 'Username can not be more than 30 characters long',
														\Zend\Validator\StringLength::TOO_SHORT => 'Username can not be less than 3 characters.')
										),
										'break_chain_on_failure' => true
								)
						),
				),
				
				'email' => array(
						'required' => true,
						'validators' => array(
								array(
										'name' => 'NotEmpty',
										'options' => array(
												'messages' => array(
														\Zend\Validator\NotEmpty::IS_EMPTY => 'Please fill the E-Mail',
												),
										),
										'break_chain_on_failure' => true
								),
								array(
										'name' => 'Zend\Validator\Db\NoRecordExists',
										'options' => array(
									        'table' => 'users',
									        'field' => 'email',
											'adapter' => $this->getDbAdapter(),
									        'exclude' => array(
									            'field' => 'user_id',
									            'value' => $this->getUserId(),
									        ),
											'break_chain_on_failure' => true
										),
								),
						),
				),
				
				'password' => array(
						'required' => true,
						'validators' => array(
								array(
										'name' => 'NotEmpty',
										'options' => array(
												'messages' => array(
														\Zend\Validator\NotEmpty::IS_EMPTY => 'Please fill the password',
												),
										),
										'break_chain_on_failure' => true
								),
								array(
										'name'    => 'Zend\Validator\StringLength',
										'options' => array(
												'encoding' => 'UTF-8',
												'min'      => 6,
												//'max'      => 30,
												'messages' => array(
														//\Zend\Validator\StringLength::TOO_LONG => 'Password can not be more than 30 characters long',
														\Zend\Validator\StringLength::TOO_SHORT => 'Password can not be less than 6 characters.')
										),
										'break_chain_on_failure' => true
								),
						),
				),
				
				'confirm_password' => array(
						'validators' => array(
								array(
										'name' => 'Identical',
										'options' => array(
												'token' => 'password', // name of first password field
										),
								),
						),
				),
		);
	}
	
	public function getUserId(){
		return $this->userId;
	}
	
	public function setUserId( $userId ){
		return $this->userId = $userId;
	}
	
	public function setDbAdapter( \Zend\Db\Adapter\Adapter $dbAdapter ){
		$this->dbAdapter = $dbAdapter;
		return $this;
	}
	
	public function getDbAdapter(){
		return $this->dbAdapter;
	}
}
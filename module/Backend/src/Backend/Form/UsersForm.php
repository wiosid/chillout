<?php
namespace Backend\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterInterface;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;


class UsersForm extends Form
{
	
	public function init()
	{
		
		parent::__construct('create_user');
		$this->setAttribute('method', 'post')
			 ->setHydrator(new \Models\Mapper\UserHydrator(false))
             ->setInputFilter(new InputFilter())
			 ->setAttribute('enctype', 'multipart/form-data');
		
		$this->add(array(
				'type' => 'UserFieldset',
				'options' => array(
						'use_as_base_fieldset' => true
					)
		));
		
		$this->add(array(
				'type' => 'Zend\Form\Element\Csrf',
				'name' => 'csrf'
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
						'type' => 'submit',
						'value' => 'Save',
						'class' => 'submit-btn'
				)
		));
		
		$this->add(array(
				'name' => 'cancel',
				'attributes' => array(
						'type' => 'reset',
						'value' => 'Cancel',
						'class' => 'btnGray ml10'
				)
		));
	}
}
<?php

namespace Models\Model;

use ZfcUser\Entity\UserInterface;
use ZfcRbac\Identity\IdentityInterface;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class AdminUser implements UserInterface, IdentityInterface
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $displayName;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var int
	 */
	protected $state;
	
	protected $phone;
	/**
	 * @var array
	 */
	protected $roles;

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param int $id
	 * @return UserInterface
	 */
	public function setId($id)
	{
		$this->id = (int) $id;
		return $this;
	}

	/**
	 * Get username.
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set username.
	 *
	 * @param string $username
	 * @return UserInterface
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string $email
	 * @return UserInterface
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Get displayName.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}

	/**
	 * Set displayName.
	 *
	 * @param string $displayName
	 * @return UserInterface
	 */
	public function setDisplayName($displayName)
	{
		$this->displayName = $displayName;
		return $this;
	}

	/**
	 * Get password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Set password.
	 *
	 * @param string $password
	 * @return UserInterface
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * Get state.
	 *
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set state.
	 *
	 * @param int $state
	 * @return UserInterface
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}

	public function getPhone(){
		return $this->phone;
	}
	
	public function setPhone($phone){
		$this->phone = $phone;
		return $this;
	}
	/**
	 * @param array $roles
	 * @return array
	 */
	public function setRoles( $roles ){ 
		$this->roles = $roles;
		return $this;
	}
	
	/**
	 * @return array
	 * @see \ZfcRbac\Identity\IdentityInterface::getRoles()
	 */
	public function getRoles(){
		return $this->roles;
	}
	
	public function exchangeArray($data)
	{ 
		$this->displayName  = (isset($data['displayName'])) ? $data['displayName'] : "";
		$this->email = (isset($data['email'])) ? $data['email'] : "";
		$this->password  = (isset($data['password']))  ? $data['password']  : "";
		$this->phone  = (isset($data['phone']))  ? $data['phone']  : "";
		$this->roles = (isset($data['roles'])) ? $data['roles'] :"";
		if($data['display_name']){
			$this->displayName = $data['display_name']; 
		}
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}
	
	
}

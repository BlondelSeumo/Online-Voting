<?php

/**
 * 
 */
class Staff {
	private $_db,
	        $_data,
			$_sessionName,
			$_cookieName,
			$_isLoggedIn;
	
	public function __construct($staff = null) 
	{
	 $this->_db = DB::getInstance();
	 
	 $this->_sessionName = Config::get('session/session_staff');	
	 $this->_cookieName = Config::get('remember/cookie_name');	
	 
	 if (!$staff) {
		if (Session::exists($this->_sessionName)) {
		  $staff = Session::get($this->_sessionName);
		  if ($this->find($staff)) {
			 $this->_isLoggedIn = true;
		  }else {
			  //Process logout
		  }		
		} 
	 } else {
		 $this->find($staff);
	 }
	}
	
	public function update($fields = array(), $where)
	{
	  if (!$where && $this->isLoggedIn()) {
		  $staffid = $this->data()->staffid; 
	  }
		
	  if (!$this->_db->update('staff', $fields, $where)) {
		throw new Exception('There was an a problem updating.');
		 
	  }	
	}
	
	public function create($fields = array())
	{
	  if (!$this->_db->insert('staff', $fields)) {
		throw new Exception('There was an a problem creating an account.');
		 
	  }	
	}
	
	public function find($staff = null)
	{
	  if ($staff) {
		 $field = (is_numeric($staff)) ? 'staffid' : 'email';
		 $data = $this->_db->get("staff", "*", [$field => $staff]);
		 
		 if ($data->count()) {
		 	$this->_data = $data->first();
			return true;  
		 }
	  }	
	 return false;	
	}
	
	public function login($email = null, $password = null, $remember = false)
	{
	 if (!$email && !$password && $this->exists()) {
	 	Session::put($this->_sessionName, $this->data()->staffid);
		 
	 } else {
		 
	  $staff = $this->find($email);
	  if ($staff) {
		 if ($this->data()->password === Hash::make($password, $this->data()->salt)) {
			Session::put($this->_sessionName, $this->data()->staffid);
			
			if ($remember) {
			 $hash = Hash::unique();
			 $hashCheck = $this->_db->get("users_session", "*", ["user_id" => $this->data()->staffid]);
			 
			 if (!$hashCheck->count()) {
				$this->_db->insert('users_session', array(
				  'user_id' => $this->data()->staffid,
				  'hash' => $hash
				)); 
			 } else {
			   $hash = $hashCheck->first()->hash;
			 }
			 Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
			} 
			 
			return true; 
		 } 
	  }
	 }
	  return false;
	}
	
	public function hasPermission($key)
	{
	 $group = $this->_db->get("groups", "*", ["id" => $this->data()->user_type]);
	 
	 if ($group->count()) {
	   $permissions = json_decode($group->first()->permissions, true);
	   
	   if (isset($permissions[$key]) == true) {
		  return true;
	   }	 
		 
	 }
	  return false;	
	}
	
	public function exists()
	{
	 return (!empty($this->_sessionName)) ? true : false;	
	}
	
	public function logout()
	{
	  $this->_db->delete("users_session", ["user_id" => $this->data()->staffid]);
		
	  Session::delete($this->_sessionName);
	  Cookie::delete($this->_cookieName);
	}
	
	
	public function data()
	{
	  return $this->_data;
	}
	
    public function isLoggedIn()
	{
	  return $this->_isLoggedIn;
	}
}




?>
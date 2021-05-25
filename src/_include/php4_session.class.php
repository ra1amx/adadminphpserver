<?php
//////////////////////////////////////////////////////////////////////
// CLASS NAME:  SESSION                                               //
// LANGUAGE:    PHP                                                 //
// AUTHOR:      Julien PACHET                                       //
// EMAIL:       clifden@anarchie.net                                //
// VERSION:     1.0                                                 //
// DATE:        06/10/2002                                          //
//////////////////////////////////////////////////////////////////////
// History:                                                         //
//----------                                                        //
//	Date				Version		Actions                                   //
// ---------------------------------------------------------------- //
//	06/10/2002	1.0				Final version                             //

//////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Need to work: No other file / documents
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// What the class need:
// * Nothing
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// What the class do:
// * Let manipulate the Session variables trought Session system
//   * Store variables
//   * Get Variables
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


class Session
{
	var $status;

	function Session ()
	{
		//$expireTime = 60*30; // secondi
		//session_set_cookie_params($expireTime);
		$this->status="started";
		return session_start();
	}

	function register($var,$value)
	{
	  $_SESSION[$var]=$value;  
	}

	function unregister($var)
	{
		return session_unregister($var);
	}

	function is_registered($var)
	{
		return(session_is_registered($var));
	}

	function get($var)
	{
		if ($this->is_registered($var))
			$this->$var=$_SESSION[$var];
		else
			if(isset($GLOBALS[$var]))
				$this->$var = $GLOBALS[$var];
			else
				if (isset($_REQUEST[$var]))
					$this->$var=$_REQUEST[$var];
				else
					$this->$var="";
		return($this->$var);
	}

	function id()
	{
		return(session_id());
	}

	function self()
	{
		return($_SERVER["PHP_SELF"]);
	}

	function finish()
	{
		if(session_id()) session_destroy();
		$_COOKIE=array(); // to change identity of the cookie
	}

}

?>
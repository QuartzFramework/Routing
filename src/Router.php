<?php
/**
	Default authenticaton wrapper, this wrapper will be used to call all provider child functions
**/

namespace quartz\Routing;



class Router extends \quartz{

	/**
	 * Matches Protocol, and path
	 * @param  String $protocol The protocol that should've been used
	 * @param  String $path     The path that should be matched against the $route
	 * @param  Function $function The function that should be executed when there is a match
	 * @return boolean  Will return false on no match, will return true and run your funciton on match
	 */
	public function match($protocol, $path, $function = false, $method = NULL){

		if($protocol === $_SERVER['REQUEST_METHOD']):

			$params = (explode('/',static::$route));
			array_shift($params);

			$reqs = (explode('/',$path));
			array_shift($reqs);

			// break down, when the param count does not match
			if(count($reqs) != count($params)):return false; endif;

			$regex = '/{(.*)}/';
			$vars = array();

			foreach($reqs as $key => $req):
				preg_match($regex,$req,$matches);
				if(isset($matches[1]) && !empty($matches[1]) && isset($params[$key]) && $params[$key] != ''):
					$vars[$matches[1]] = $params[$key];
				elseif(!isset($params[$key]) || $req != $params[$key]):
					return false;
				else:
					// not variable
				endif;
			endforeach;

			// check if the function wants to call indexed controller

			// run the given function
			if (is_string($function)):
				$this->stack['prints'][] = call_user_func(
					array('\app\controllers\\' . $function, $method),
					$vars,$this->stack['resources']
				);

			elseif($function):
				$this->stack['prints'][] = $function($vars,$this->stack['resources']);
			endif;

			// return true, you made it!
			return true;
		endif;
		// nop protocol miss match
		return false;
	}

	/**
	 * run $this->match with an 'GET' protocol
	 * @param  String $path               The string that will be matched
	 * @param  Function [$function = false] The function that will be executed on succesfull match
	 * @return boolean  returns match response
	 */
	public function get($path, $function = false, $method = false){
		if($this->match('GET',$path,$function,$method)): return true; endif;
		return false;
	}

	/**
	 * Get all match count
	 * @return int count of all matches
	 */
	public function matches(){
		if(isset($this->stack['prints']) && !empty($this->stack['prints'])):
			return count($this->stack['prints']);
		endif;
		return 0;
	}

	/**
	 * Define the current routing
	 * @param  [[Type]] $route [[Description]]
	 * @return [[Type]] [[Description]]
	 */
	public function setRoute($route){
		static::$route = $route;
		return $this;
	}

}

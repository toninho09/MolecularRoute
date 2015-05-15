<?php
	namespace MolecularRoute;
	class RouteMolecule{
		protected $class="\MolecularRoute\Route\Route";

		protected static $instance;
		
	    public static function __callStatic($name, $arguments)
	    {
			return call_user_func_array(array(self::getInstance(),$name),$arguments);
		}
		
		public function __call($name, $arguments)
	    {
			return call_user_func_array(array(self::getInstance(),$name),$arguments);
		}
		
		public function register(\MolecularCore\Core &$app){
			if (!isset(self::$instance)) {
	            self::$instance = new $this->class($app);
	        }
		}
		
		public static function getInstance(){
			return self::$instance;
		}

		public function run(){
			if(!self::getInstance()->executeRoute()){
				throw new \Exception("Route not Found.");
			}
		}
	}
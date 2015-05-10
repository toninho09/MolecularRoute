<?php
	namespace MolecularRoute\Route;
	class Route{
		
		private $routes;
		private $request;
		private $group;
		
		public function __construct($app){
			$this->request = $app->request;
		}
		
		public function post($name,$function){
			$this->registerRoute('POST',$name,$function);
		}
		
		public function get($name,$function){
			$this->registerRoute('GET',$name,$function);
		}
		
		public function group($nameGroup,$callback){
			$oldGroup = $this->group;
			$this->group .= $nameGroup;
			$callback();
			$this->group = $oldGroup;
		}
		
		public function executeRoute(){
			$this->routes[$this->request->getMethod()][$this->request->getRequestURI()]();
		}
		
		private function registerRoute($method,$name,$function){
			$this->routes[$method][$this->group.$name]= $function;
		}
	}
	
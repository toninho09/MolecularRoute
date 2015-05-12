<?php
	namespace MolecularRoute\Route;
	class Route{
		
		private $routes;
		private $request;
		private $group;
		
		public function __construct($app){
			$this->request = $app->request;
			$this->group = '';
		}
		
		public function post($name,$function){
			$this->registerRoute('POST',$name,$function);
		}
		
		public function get($name,$function){
			$this->registerRoute('GET',$name,$function);
		}
		
		public function put($name,$function){
			$this->registerRoute('PUT',$name,$function);
		}
		
		public function delete($name,$function){
			$this->registerRoute('DELETE',$name,$function);
		}
		
		public function option($name,$function){
			$this->registerRoute('OPTION',$name,$function);
		}
		
		public function path($name,$function){
			$this->registerRoute('PATH',$name,$function);
		}
		
		public function head($name,$function){
			$this->registerRoute('HEAD',$name,$function);
		}
		
		public function any($name,$function){
			$this->registerRoute($this->request->getMethod(),$name,$function);
		}
		
		public function custom($method,$name,$function){
			if(is_array($method)){
				foreach($method as $value){
					$this->registerRoute($value,$name,$function);
				}
			}else{
				$this->registerRoute($method,$name,$function);
			}
		}
		
		public function group($nameGroup,$callback){
			$oldGroup = $this->group;
			$this->group .= $nameGroup;
			$callback();
			$this->group = $oldGroup;
		}
		
		public function executeRoute(){
			foreach($this->routes as $key => $value){
				if(preg_match("/^".$key."$/",$this->request->getRequestURI(),$match)){
					unset($match[0]);
					$this->runFunction($value,$match);
				}
			}
		}
		
		private function runFunction($function,$match){
			if(is_callable( $function )){
				call_user_func_array($function,$match);
			}elseif(is_string($function)){
				$this->runNameFunction($function,$match);
			}elseif(is_array($function)){
				$this->runArrayFunction($function,$match);
			}
		}
		
		private function runArrayFunction($function,$match){
			if(!empty($function['before'])){
				$this->runFunction($function['before'],$match);
			}
			if(!empty($function['uses'])){
				$this->runFunction($function['uses'],$match);
			}
			if(!empty($function['after'])){
				$this->runFunction($function['after'],$match);
			}
		}
		
		private function runNameFunction($function,$match){
			preg_match("/(\w+)@(\w+)/",$function,$funcParams);
			unset($funcParams[0]);
			if(count($funcParams) != 2){
				throw new \Exception("Method call is not 'CLASS@METHOD' ");
			}
			if(class_exists($funcParams[1])){
				$class = new $funcParams[1]();
				if(method_exists($class,$funcParams[2])){
					call_user_func_array([$class,$funcParams[2]],$match);
				}else{
					throw new \Exception('Method '.$funcParams[1][1].' Not Found');
				}
			}else{
				throw new \Exception('Class '.$funcParams[1][0].' Not Found');
			}
		}
		
		private function putRegex($name){
			return preg_replace(['/{\w+}/','/\/{\w+\?}/','/\\//','/\//'],['(\w+)','(\/\w+)?','/','\\/'],$name);
		}
		
		private function registerRoute($method,$name,$function){
			if($method == $this->request->getMethod()){
				if(($name == '' && $this->group == '' )||
				   ($name == '' && $this->group{strlen($this->group)} != '/' ) ||
				   ($name{0} == '' && $this->group == '' )){
					   $name = '/' . $name;
				   }
				if($this->group != '' && $this->group{0} != '/'){
					$this->group = '/' .$this->group;
				}
				$this->routes[$this->putRegex($this->group.$name)] = $function;	
			}
		}
	}
	
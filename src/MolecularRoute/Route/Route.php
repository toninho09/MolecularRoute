<?php
	namespace MolecularRoute\Route;
	class Route{
		
		private $routes;
		private $group;
		private $notFound;
		private $next;
		private $app;
		private $filterAfter;
		private $filterBefore;		
		private $filters;
		public function __construct($app = null){
			$this->app = $app;
			$this->group = '';
			$this->next = false;
			$filters = [];
		}

		public function after($function){
			$this->filterAfter = $function;
		}

		public function before($function){
			$this->filterBefore = $function;
		}
		
		public function filter($name,$function){
			if(isset($this->filters[$name]))
				throw new Exception("The route has already been defined above.", 1);
			$this->filters[$name] = $function;
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
			$this->registerRoute($_SERVER['REQUEST_METHOD'],$name,$function);
		}
		
		public function setNotFound($function){
			$this->notFound = $function;
		}
		
		public function next(){
			$this->next = true;
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
			$findRoute = false;
			if(!empty($this->filterBefore))
				$this->runFunction($this->filterBefore,[isset($this->app->request)?$this->app->request:null,isset($this->app->response)?$this->app->response:null]);
			foreach($this->routes as $key => $value){
				if($this->executeIndividualRoute($value)){
					$findRoute = true;
					break;
				}
			}
			if(!$findRoute){
				try{
					$this->runFunction($this->notFound);
				}catch(\Exception $e){
					throw new \Exception("Route not Found or Default error route is not a callable or not is a valid function name");
				}
			}
			if(!empty($this->filterAfter))
				$this->runFunction($this->filterAfter,[isset($this->app->request)?$this->app->request:null,isset($this->app->response)?$this->app->response:null]);
			return true;
		}

		private function executeIndividualRoute($route){
			if(preg_match("/^".$route['route']."$/",$_SERVER['REQUEST_URI'],$match)){
				unset($match[0]);
				if (isset($this->app->response))
					$this->app->response->setResponseContent($this->runFunction($route["function"],$match));
				else
					echo $this->runFunction($route["function"],$match);
				if(!$this->next)
					return true;
				$this->next = false;
			}
		}
		
		private function runFunction($function,$match = []){
			if(is_callable( $function )){
				return call_user_func_array($function,$match);
			}elseif(is_string($function)){
				return $this->runNameFunction($function,$match);
			}elseif(is_array($function)){
				return $this->runArrayFunction($function,$match);
			}else{
				throw new \Exception("The method not is callable or a valid function name.");
			}
		}

		private function runFilters($name){
			try {
				$this->runFunction($this->filters[$name],[isset($this->app->request)?$this->app->request:null,isset($this->app->response)?$this->app->response:null]);
			} catch (\Exception $e) {
				throw new \Exception("The Filters not is callable or a valid function name.");	
			}	
		}
		
		private function runArrayFunction($function,$match){
			$return = null;
			if(!empty($function['before'])){
				ob_start();
				$temp = $this->runFilters($function['before']);
				$return .= ob_get_clean();
				$return .= $temp;
			}
			if(!empty($function['uses'])){
				ob_start();
				$temp = $this->runFunction($function['uses'],$match);
				$return .= ob_get_clean();
				$return .= $temp;
			}
			if(!empty($function['after'])){
				ob_start();
				$temp = $this->runFilters($function['after']);
				$return .= ob_get_clean();
				$return .= $temp;
			}
			return $return;
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
					return call_user_func_array([$class,$funcParams[2]],$match);
				}else{
					throw new \Exception('Method '.$funcParams[2].' Not Found');
				}
			}else{
				throw new \Exception('Class '.$funcParams[1].' Not Found');
			}
		}
		
		private function putRegex($name){
			return preg_replace(['/{\w+}/','/\/{\w+\?}/','/\\//','/\//'],['(\w+)','(\/\w+)?','/','\\/'],$name);
		}
		
		private function registerRoute($method,$name,$function){
			if($method == $_SERVER['REQUEST_METHOD']){
				if(($name == '' && $this->group == '' )||
				   ($name == '' && $this->group{strlen($this->group)} != '/' ) ||
				   ($name{0} == '' && $this->group == '' )){
					   $name = '/' . $name;
				   }
				if($this->group != '' && $this->group{0} != '/'){
					$this->group = '/' .$this->group;
				}
				$this->routes[] = ["route"=>$this->putRegex($this->group.$name),"function"=> $function];	
			}
		}
	}
	
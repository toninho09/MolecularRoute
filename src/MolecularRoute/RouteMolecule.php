<?php
	namespace MolecularRoute;
	class RouteMolecule extends \MolecularCore\Molecule{
		protected $class="\MolecularRoute\Route\Route";
		
		public function run(){
			if(!self::$instance->executeRoute()){
				throw new \Exception("Route not Found.");
			}
		}
	}
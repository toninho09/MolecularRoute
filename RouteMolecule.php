<?php
	namespace MolecularRoute;
	class RouteMolecule extends \MolecularCore\Molecule{
		protected $class="\MolecularRoute\Route\Route";
		
		public function run(){
			self::$instance->executeRoute();
		}
	}
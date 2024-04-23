<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of classRelation
 *
 * @author papa
 */
class Relation {

	private $master;
	private $detail;
	private $relation = array();	// Master => Detail
	
	function __construct(array $relation, Table $master, Table $detail) {
		$this->relation = $relation;
		$this->master = $master;
		$this->detail = $detail;
	}
	
	function get_relation() {
		return $this->relation;
	}
	
	function get_keys() {
		return array_keys($this->relation);
	}
	
	function get_values() {
		return array_values($this->relation);
	}

	function get_master() {
		return $this->master;
	}

	function get_detail() {
		return $this->detail;
	}
}
?>

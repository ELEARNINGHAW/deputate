<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of encodedParameterlist
 *
 * @author papa
 */
class EncodedParameterlist {
	
	protected $m_ParameterList;
	protected $m_EncodedList;
	
	public function __construct (array $ParameterList) {
		
		$this->m_ParameterList = $ParameterList == NULL? array(): $ParameterList;
		$this->m_EncodedList = array();
		foreach ($this->m_ParameterList as $Parameter) {
			$this->m_EncodedList[] = rawurlencode($Parameter);
		}
	}
	
	public function getEncodedList() {
		return $this->m_EncodedList;
	}
	
	public function implodeParameterList($glue) {
		return implode($glue, $this->m_EncodedList);
	}
}

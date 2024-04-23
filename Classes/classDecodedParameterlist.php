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
class DecodedParameterlist {
	
	protected $m_ParameterList;
	protected $m_DecodedList;
	
	public function __construct ($delimiter, $implodedParameterList) {
		$this->m_ParameterList = $implodedParameterList == NULL? array(): explode($delimiter, $implodedParameterList);
		$this->m_DecodedList = array();
		foreach ($this->m_ParameterList as $Parameter) {
			$this->m_DecodedList[] = rawurldecode($Parameter);
		}
	}
	
	public function getDecodedList() {
		return $this->m_DecodedList;
	}
}


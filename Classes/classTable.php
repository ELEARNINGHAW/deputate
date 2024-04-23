<?php
/**
 * Description of classTable
 *
 * @author papa
 */
class Table {
	private $header;	// Überschrift auf der Web-Seite
	private $table;		// Datenbankname der Tabelle mit Hochkommata
	private $cols;		// Selektionskriterium für Spalten: Liste der Spalten oder *
	private $where;		// nummeriertes Array mit Bedingungen
	private $order;		// nummeriertes Array mit Spaltennamen, nach denen sortiert werden soll
	private $ID;		// nummeriertes Array mit Spaltennamen, die einen eindeutigen Schlüssel (z.B. den Primärschlüssel) darstellen
	private $optionlist;	// assoziiertes Array mit Spaltennamen und einem Array möglicher Werte für diese Spalte

	function  __construct($header, $table, $cols = '*', array $where = array(), array $order = array(), array $ID =array(), array $optionlist = array()) {
		$this->header = $header;
		$this->table = $table;
		$this->cols = $cols;
		$this->where = $where;
		$this->order = $order;
		$this->ID = $ID;
		$this->optionlist = $optionlist;
	}

	function get_header () {
		return $this->header;
	}

	function get_table () {
		return $this->table;
	}

	function get_rows () {
		return $this->cols;
	}
	
	function get_cols () {
		return $this->cols;
	}

	function get_where () {
		return $this->where;
	}
	
	function get_order () {
		return $this->order;
	}

	function get_ID () {
		return $this->ID;
	}

	function get_optionlist () {
		return $this->optionlist;
	}
	
}

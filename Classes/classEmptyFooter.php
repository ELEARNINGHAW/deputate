<?php
class EmptyFooter extends PDF_Footer {

	function  __construct(PDF_Document $document, $text = "", $optlist = "") {
		parent::__construct($document, $optlist);
	}

	function text () {
		$pdf = $this->pdf;
		$document = $this->document;
		$font = $this->font;

	}

}
?>

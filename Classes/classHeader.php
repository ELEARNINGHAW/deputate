<?php

/**
 * Description of classHeader
 *
 * @author papa
 */

class PDF_Header extends PDF_Template {

	protected $logo;

	function  __construct(PDF_Document $document, $optlist = "") {
		$this->logo = $document->getPDF()->load_image("auto", "HAW-Logo.png", "");
		if ($this->logo == 0) {
			die("Error: " . $document->getPDF()->get_errmsg());
		}
		// kann erst nach dem Laden des Bildes aufgerufen werden!
		parent::__construct($document, $document->getBodywidth(), $document->getHeaderheight(), $optlist);
	}
	
	function finish ($optlist = "") {
		$this->handle = $this->pdf->begin_template_ext($this->textwidth, $this->textheight, $optlist);
		$this->text();
		$this->pdf->end_template_ext(0, 0);
	}

	function text () {
		$this->default_text();
	}
	
	protected function default_text($offset = 0) {
		$pdf = $this->pdf;
		$font = $this->font;
		$handle = 0;

		$handle = $pdf->add_textflow($handle, "Hochschule für Angewandte Wissenschaften\nFakultät Life Sciences\nDekanat\n", "font=$font fontsize=8");
		$pdf->fit_textflow($handle, 0, $offset, $this->textwidth, $this->textheight, "verticalalign=bottom");
		
		$option = 'boxsize={' . $this->textwidth . ' ' . 0.5 * $this->textheight . '} position={bottom right} fitmethod=meet';
		$pdf->fit_image($this->logo, 0, $offset, $option);
	}

}


class EmptyHeader extends PDF_Header {
	function  __construct(PDF_Document $document, $text = "", $optlist = "") {
		parent::__construct($document, $optlist);
		$this->text = $text;
	}

	function text() {
	}

}


class HeaderWithSemester extends PDF_Header {

	protected $text;


	function  __construct(PDF_Document $document, $text = "", $optlist = "") {
		parent::__construct($document, $optlist);
		$this->text = $text;
	}

	function text() {
		$pdf = $this->pdf;
		$font = $this->font;

		parent::default_text(15);
		
		$handle = $pdf->create_textflow($this->text, "font=$font fontsize=10 alignment=center");
		$pdf->fit_textflow($handle, 0, 2, $this->textwidth, $this->textheight, "verticalalign=bottom");



	}



}

?>

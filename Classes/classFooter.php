<?php

/**
 * Description of classFooter
 *
 * @author papa
 */

class PDF_Footer extends PDF_Template {

	function  __construct(PDF_Document $document, $optlist = "") {
		parent::__construct($document, $document->getBodywidth(), $document->getFooterheight(), $optlist);
	}

	function finish ($optlist = "") {
		$this->handle = $this->pdf->begin_template_ext($this->textwidth, $this->textheight, $optlist);
		$this->text();
		$this->pdf->end_template_ext(0, 0);
	}
	
	function text () {
		$pdf = $this->pdf;
		$document = $this->document;
		$font = $this->font;

		$pdf->fit_textline(date("m.d.y"), $this->textwidth / 2, $this->textheight, "font=$font fontsize=8 position={top center}");
	}

}


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

class FooterWithCounter extends PDF_Footer {

	protected $text;

	function  __construct(PDF_Document $document, $text = "", $optlist = "") {
		parent::__construct($document, $optlist);
		$this->text = $text;
	}

	function text () {
		$pdf = $this->pdf;
		$document = $this->document;
		$font = $this->font;

		$pdf->fit_textline($this->text, 0, $this->textheight, "font=$font fontsize=8 position={top left}");
		$pdf->fit_textline("Seite ". $document->getPagenumber(), $this->textwidth / 2, $this->textheight - 3, "font=$font fontsize=8 position={top center}");
		$pdf->fit_textline(date("d.m.Y"), $this->textwidth, $this->textheight, "font=$font fontsize=8 position={top right}");
	}

}

?>

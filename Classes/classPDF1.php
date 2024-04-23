<?php
/**
 * Description of classPDF
 *
 * @author papa
 */
class PDF extends pdflib {
	private $document;
	private $file;

	public function __construct ($file = 'test.pdf') {

		parent::__construct();

		$this->file = $file;

		# This means we must check return values of load_font() etc.
		$this->set_option ("errorpolicy = return");

		# This line is required to avoid problems on Japanese systems
		$this->set_option ("hypertextencoding = host");

		# This line ensures umlaute
		$this->set_option ("textformat = utf8");

		# Include license ...
		$this->set_option("license=L900102-010093-142222-SVQ522-2CXUC2");
		
	}

	function createDocument ($title, $author, $creator, $file = "",
			$pagewidth = 595, $pageheight = 842,
			$leftmargin = 55, $rightmargin = 55,
			$headerheight = 90, $footerheight = 50,
			$headersep = 5, $footersep = 5,
			$pagetop = 10, $pagebottom = 10) {
		$this->document = new PDF_Document($this, $title, $author, $creator, $file, $pagewidth, $pageheight, $leftmargin, $rightmargin, 
				$headerheight, $footerheight, $headersep, $footersep, $pagetop, $pagebottom);
		return $this->document;
	}

	function printOut () {

		$buf = $this->get_buffer();
		$len = strlen($buf);

		header("Content-type: application/pdf");
		header("Content-Length: $len");
		header("Content-Disposition: inline; filename=$this->file");

		print $buf;
	}

}

class PDF_Document {

	private $pdf;
	private $page;
	private $header;
	private $footer;
	private $body;
	private $file;

	private $font;
	private $boldfont;
	private $italicfont;
	private $pagewidth;
	private $pageheight;
	private $headerheight;
	private $footerheight;
	private $headersep;
	private $footersep;
	private $leftmargin;
	private $rightmargin;
	private $pagetop;
	private $pagebottom;

	private $pagenumber = 1;


	function __construct (PDF $pdf, $title, $author, $creator, $file,
			$pagewidth, $pageheight,
			$leftmargin, $rightmargin,
			$headerheight, $footerheight,
			$headersep, $footersep,
			$pagetop, $pagebottom) {

		$this->pdf = $pdf;
		$this->file = $file;
		$this->pagewidth = $pagewidth;
		$this->pageheight = $pageheight;
		$this->leftmargin = $leftmargin;
		$this->rightmargin = $rightmargin;
		$this->headerheight = $headerheight;
		$this->headersep = $headersep;
		$this->footerheight = $footerheight;
		$this->footersep = $footersep;
		$this->pagetop = $pagetop;
		$this->pagebottom = $pagebottom;

		if ($this->pdf->begin_document($file, "") == 0) {
			die("Error: " . $this->pdf->get_errmsg());
		}

		$this->pdf->set_info("Creator", $creator);
		$this->pdf->set_info("Author", $author);
		$this->pdf->set_info("Title", $title);
		$this->font = $pdf->load_font('Arial', 'host', '');
		$this->boldfont = $pdf->load_font('Arial', 'host', 'fontstyle=bold');
		$this->italicfont = $pdf->load_font('Arial', 'host', 'fontstyle=italic');
		$this->header = new PDF_Header($this);
		$this->header->finish();

		$this->footer = new PDF_Footer($this);
		$this->footer->finish();
	}

	function finish () {
		$this->pdf->end_document("labels={{pagenumber=1 style=D}}");
	}

	function createBody() {
		$this->body = new PDF_Body($this);
		return $this->body;
	}

	function createPage (PDF_Body $body, PDF_Header $header = NULL, PDF_Footer $footer = NULL, $optlist = '') {
		$width = $this->pagewidth;
		$height = $this->pageheight;

		if ($header == NULL) {
			$header = $this->header;
		}

		if ($footer == NULL) {
			$footer = $this->footer;
		}
		
		$this->pagenumber++;

		$this->page = new PDF_Page($this, $width, $height, $body, $header, $footer, $optlist);
		return $this->page;
	}

	function createTable () {
		$table = new PDF_Table($this);
		return $table;
	}
	
	function createTextflow ($text, $optionlist = NULL) {
		$textflow = new PDF_Textflow($this);
		$textflow->addTextflow($text, $optionlist);
		return $textflow;
	}
	
	function createImage ($file) {
		$image = new PDF_Image($this);
		$image->loadImage($file);
		return $image;
	}
	
	function createPagebreak () {
		return new PDF_Pagebreak ($this);
	}
	
	function getFont () {
		return $this->font;
	}

	function getBoldfont () {
		return $this->boldfont;
	}

	function getItalicfont () {
		return $this->italicfont;
	}

	function getPagewidth () {
		return $this->pagewidth;
	}

	function getPageheight () {
		return $this->pageheight;
	}
	
	function getHeaderllx () {
		return $this->leftmargin;
	}

	function getHeaderlly () {
		return $this->getHeaderury() - $this->headerheight;
	}

	function getHeaderurx () {
		return $this->getPagewidth() - $this->rightmargin;
	}
	
	function getHeaderury () {
		return $this->getPageheight() - $this->pagetop;
	}
	
	function getHeaderheight () {
		return $this->getHeaderury() - $this->getHeaderlly();
	}

	function getBodyllx () {
		return $this->leftmargin;
	}
	
	function getBodylly () {
		return $this->getFooterury() + $this->footersep;
	}

	function getBodyurx () {
		return $this->getPagewidth() - $this->rightmargin;
	}

	function getBodyury () {
		return $this->getHeaderlly() - $this->headersep;
	}

	function getBodywidth () {
		return $this->getBodyurx() - $this->getBodyllx();
	}

	function getBodyheight() {
		return $this->getBodyury() - $this->getBodylly();
	}

	function getFooterllx () {
		return $this->leftmargin;
	}

	function getFooterlly () {
		return $this->pagebottom;
	}
	
	function getFooterurx () {
		return $this->getPagewidth() - $this->rightmargin;
	}
	
	function getFooterury () {
		return $this->getFooterlly() + $this->footerheight;
	}

	function getFooterheight () {
		return $this->getFooterury() - $this->getFooterlly();
	}

	function getPagenumber () {
		return $this->pagenumber;
	}

	function getPDF () {
		return $this->pdf;
	}

}

class PDF_Page {

	private $pdf;
	private $document;
	private $header;
	private $footer;
	private $body;
	private $pagewidth;
	private $pageheight;

	function  __construct(PDF_Document $document, $width, $height, PDF_Body $body, PDF_Header $header, PDF_Footer $footer, $optlist = '') {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->header = $header;
		$this->footer = $footer;
		$this->body = $body;
		$this->pagewidth = $width;
		$this->pageheight = $height;
		$this->pagecounter = $pagecounter;

		$this->pdf->begin_page_ext($this->pagewidth, $this->pageheight, $optlist);

	}

	function finish () {
		$this->pdf->fit_image($this->header->getHandle(), $this->document->getHeaderllx(), $this->document->getHeaderlly(), "");
		$this->pdf->fit_image($this->body->getHandle(), $this->document->getBodyllx(), $this->document->getBodylly(), "");
		$this->pdf->fit_image($this->footer->getHandle(), $this->document->getFooterllx(), $this->document->getFooterlly(), "");

		$this->pdf->end_page_ext("");
	}

	function getBody () {
		return $this->body;
	}

	function getHeader () {
		return $this->header;
	}

	function getFooter () {
		return $this->footer;
	}
}

abstract class PDF_Template {

	protected $pdf;
	protected $document;
	protected $handle = 0;
	protected $textwidth;
	protected $textheight;
	protected $font;

	abstract function text();

	function  __construct(PDF_Document $document, $textwidth, $textheight, $optlist = "") {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->font = $document->getFont();
		$this->textwidth = $textwidth;
		$this->textheight = $textheight;

		$this->handle = $this->pdf->begin_template_ext($this->textwidth, $this->textheight, $optlist);
//		echo "in TemplateMode.. " . debug_print_backtrace() . "<br><br>";
	}

	function finish () {
		$this->text();
		$this->pdf->end_template_ext(0, 0);
//		echo "out of TemplateMode.. " . debug_print_backtrace() . "<br><br>";
	}

	function getHandle() {
		return $this->handle;
	}

	protected function getFont() {
		return $this->font;
	}

	function getWidth () {
		return $this->textwidth;
	}

	function getHeight () {
		return $this->textheight;
	}

}

class PDF_Body extends PDF_Template {

	function  __construct(PDF_Document $document, $optlist = "") {
		parent::__construct($document, $document->getBodywidth(), $document->getBodyheight(), $optlist);
	}

	function text () {

	}

}

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

	function text () {
		$pdf = $this->pdf;
		$font = $this->font;
		$handle = 0;

		$handle = $pdf->add_textflow($handle, "Hochschule für Angewandte Wissenschaften\nFakultät Life Sciences\nDekanat\n", "font=$font fontsize=8");
		$pdf->fit_textflow($handle, 0, 0, $this->textwidth, $this->textheight, "verticalalign=bottom");
		$height = $pdf->info_textflow($handle, 'textheight');
		$imageh = $pdf->info_image($this->logo, 'height', '');
//		$scale = $height / $imageh;
		$scale = ($this->textheight - 20) / $imageh;
		$sizex = $pdf->info_image($this->logo, 'width', "scale=$scale fitmethod=meet");
//		echo "$sizex, $scale, $height, $imageh<br>";
		$pdf->fit_image($this->logo, $this->textwidth - $sizex, 0, "scale=$scale fitmethod=meet");
	}

}

class PDF_Footer extends PDF_Template {

	function  __construct(PDF_Document $document, $optlist = "") {
		parent::__construct($document, $document->getBodywidth(), $document->getFooterheight(), $optlist);
	}

	function text () {
		$pdf = $this->pdf;
		$document = $this->document;
		$font = $this->font;

		$pdf->fit_textline(date("m.d.y"), $this->textwidth, $this->textheight / 2, "font=$font fontsize=8 position={center}");
	}

}

abstract class PDF_Printable {
	
	private $pdf;
	private $document;
	private $handle = 0;
	private $regularfont;
	private $boldfont;
	private $fontsize = 10;
	private $margin = 1.5;
	
	public function __construct (PDF_Document $document) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->regularfont = $document->getFont();
		$this->boldfont = $document->getBoldfont();		
	}
	
	abstract function fit($x, $y, $width, $height);
	abstract function getHeight();
	abstract function destroy();
	abstract function getHandle();
	
}

class PDF_Textflow extends PDF_Printable {
	
	public function __construct (PDF_Document $document) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->regularfont = $document->getFont();
		$this->boldfont = $document->getBoldfont();		
	}
	
	function fit($x, $y, $width, $height) {
		return $this->fitTextflow($x, $y, $width, $height);
	}
	
	function getHeight() {
		return $this->infoTextflow("textheight");
	}
	
	function destroy() {
	}
	
	function getHandle() {
		return "Textflow: " . $this->handle;
	}
	function addTextflow ($text, $optionlist = NULL) {
		$this->handle = $this->pdf->add_textflow($this->handle, $text, $optionlist);
		if ($this->handle == 0) {
			die("Error: " . $this->pdf->get_errmsg());
		}
		
	}
	
	function fitTextflow ($x, $y, $width, $height, $option = " ") {
		if ($this->handle == 0) {
			die("No Textflow created.");
		}
		return $this->pdf->fit_textflow($this->handle, $x, $y, $width, $height, $option);
	}
	
	function infoTextflow ($keyword) {
		if ($this->handle == 0) {
			die("No Textflow created.");
		}
		return $this->pdf->info_textflow($this->handle, $keyword);
	}
}

class PDF_Table extends PDF_Printable {

	public function __construct (PDF_Document $document) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->regularfont = $document->getFont();
		$this->boldfont = $document->getBoldfont();		
	}
	
	function fit($x, $y, $width, $height) {
		return $this->fitTable($x, $y, $width, $height);
	}
	
	function getHeight() {
		return $this->infoTable("height");
	}
	
	function destroy() {
		$this->destroyTable();
	}
	
	function destroyTable () {
		if ($this->handle == 0) {
			die("No Table creatred.");
		}		
		$this->pdf->delete_table($this->table, '');
	}

	function getHandle() {
		return "Table: ". $this->handle;
	}
	function addCell ($col, $row, $value, $option = NULL) {
		if (!$option) {
			$option = "fittextline={position={left center} font=$this->regularfont fontsize=$this->fontsize} margin=$this->margin";
		}
		$this->handle = $this->pdf->add_table_cell($this->handle, $col, $row, $value, $option);
		if ($this->handle == 0) {
			die("Error: " . $this->pdf->get_errmsg());
		}
	}
	
	function fitTable ($llx, $lly, $urx, $ury, $optlist = "fill={{area=rowodd fillcolor={gray 0.95}}} stroke={{line=other}} ") {

		/* Shade every other row; draw lines for all table cells.
		 * Add "showcells showborder" to visualize cell borders
		 */
		
		if ($this->handle == 0) {
			die("No Table creatred.");
		}
		$result = $this->pdf->fit_table($this->handle, $llx, $lly, $urx, $ury, $optlist);
		if ($result == "_error") {
			die ("Couldn't place table : " . $this->pdf->get_errmsg());
		}
		return $result;
	}

	function infoTable ($keyword) {
		if ($this->handle == 0) {
			die("No Table creatred.");
		}
		return $this->pdf->info_table($this->handle, $keyword);
	}

}

class PDF_Image {

	private $pdf;
	private $document;
	private $signature = 0;
	private $regularfont;
	private $boldfont;
	private $fontsize = 10;
	private $margin = 1.5;

	public function __construct (PDF_Document $document) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->regularfont = $document->getFont();
		$this->boldfont = $document->getBoldfont();		
	}
	
	public function loadImage ($file) {
		$this->signature = $this->pdf->load_image("auto", $file, "");		
	}
	
	public function infoImage ($keyword, $option = "") {
		if ($this->signature == 0) {
			die("No Image loaded.");
		}
		return $this->pdf->info_image($this->signature, $keyword, $option);		
	}
	
	public function fitImage ($width, $height, $option) {
		if ($this->signature == 0) {
			die("No Image loaded.");
		}
		return $this->pdf->fit_image($this->signature, $width, $height, $option);		
	}
}

class PDF_Pagebreak extends PDF_Printable {
	
	public function __construct (PDF_Document $document) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->regularfont = $document->getFont();
		$this->boldfont = $document->getBoldfont();		
	}
	
	function fit($x, $y, $width, $height) {
		return '_pagebreak';
	}
	
	function getHeight() {
		return 0;
	}
	
	function destroy() {
	}
	
	function getHandle() {
		return "Pagebreak: " . $this->handle;
	}
	
}

?>

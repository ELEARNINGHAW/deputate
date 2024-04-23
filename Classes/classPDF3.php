<?php
include_once 'classHeader.php';
include_once 'classFooter.php';

/**
 * Description of classPDF
 *
 * @author papa
 */
class PDF extends pdflib {
	private $file;

	private $font;
	private $boldfont;
	private $italicfont;
	
	public function __construct ($file = 'test.pdf') {

		parent::__construct();

		$this->file = $file;

		# This means we must check return values of load_font() etc.
		$this->set_parameter ("errorpolicy", "return");

		# This line is required to avoid problems on Japanese systems
		$this->set_parameter ("hypertextencoding", "host");

		# This line ensures umlaute
		$this->set_parameter ("textformat", "utf8");

		# Include license ...
		$this->set_option("license=L900102-010093-142222-SVQ522-2CXUC2");
		
		$this->font = $this->load_font('Arial', 'host', '');
		$this->boldfont = $this->load_font('Arial', 'host', 'fontstyle=bold');
		$this->italicfont = $this->load_font('Arial', 'host', 'fontstyle=italic');
		
	}

	function createDocument ($title, $author, $creator, $file = NULL,
			$pagewidth = 595, $pageheight = 842,
			$leftmargin = 55, $rightmargin = 55,
			$headerheight = 90, $footerheight = 50,
			$headersep = 5, $footersep = 5,
			$pagetop = 10, $pagebottom = 10) {
		return new PDF_Document($this, $title, $author, $creator, $file, $pagewidth, $pageheight, $leftmargin, $rightmargin, 
				$headerheight, $footerheight, $headersep, $footersep, $pagetop, $pagebottom);
	}

	function printOut ($file = NULL) {

		$buf = $this->get_buffer();
		$len = strlen($buf);

		if ($file) {
			$handle = fopen($file, 'wb');
			if ($handle) {
				fwrite($handle, $buf);
				fclose($handle);
				?>
				<script type='text/javascript' language='javascript'>
				<!--
					alert('Bericht gespeichert unter <?=$file?>.')
				-->
				</script>
				<?php
			}
		} else {
			header("Content-type: application/pdf");
			header("Content-Length: $len");
			header("Content-Disposition: inline; filename=$this->file");
			print $buf;
		}
	}
	
	function sendMail ($to, $text) {
		
		// Daten lesen
		$buf = $this->get_buffer();
		$len = strlen($buf);
		$data = chunk_split(base64_encode($buf)); 
		
		$semi_rand = md5(time()); 
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 		
		
#		$to = "papa@miraculix.wittreem";
		$to = "rainer@sawatzki.eu";
#		$to = "rainer.sawatzki@haw-hamburg.de";
		
		$subject = "Deputatsverwaltung";
		$headers = "MIME-Version: 1.0\n" . 
			"Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"\n" .
			"From: Martin.Holle@haw-hamburg.de\n" .
			"X-Mailer: PHP/" . phpversion() . "\n";

		$message = "This is a multi-part message in MIME format.\n" . 
			"--{$mime_boundary}\n" . 
//			"Content-Type:text/plain; charset=\"iso-8859-1\" \n" . 
			"Content-Type:text/plain; charset=utf-8 \n" . 
			"Content-Transfer-Encoding: 7bit \n\n" . 
			"{$text}\n\n" .
			"--{$mime_boundary}\n" . 
			"Content-Type:application/pdf; name=\"{$this->file}\"\n" . 
			"Content-Disposition: attachment; filename=\"{$this->file}\"\n" . 
			"Content-Transfer-Encoding: base64\n\n" . 
			"{$data}\n\n" . 
			"--{$mime_boundary}--\n"; 
		
		if (mail($to, $subject, $message, $headers)){
			echo "Mail sent to: $to <br>";
		}
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

}

class PDF_Document {

	private $pdf;
	private $page;
	private $header;
	private $footer;
	private $body;
	private $file;

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

	private $pagenumber = 0;
	
	private $inhalt = array();


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
		$this->header = new PDF_Header($this);
		$this->header->finish();

		$this->footer = new PDF_Footer($this);
		$this->footer->finish();
	}

	function finish () {
		$this->pdf->end_document("labels={{pagenumber=1 style=D}}");
		unset($this);
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

	function createTable ($header = 0, $footer = 0) {	// benötigt document-scope
		$table = new PDF_Table($this->pdf, $header, $footer);
		return $table;
	}
	
	function createTextflow ($text, $optionlist = NULL) {	// benötigt document-scope
		$textflow = new PDF_Textflow($this->pdf, $text, $optionlist);
		return $textflow;
	}
	
	function createImage ($file, $option = "") {	// benötigt document-scope
		$image = new PDF_Image($this->pdf);
		$image->loadImage($file, $option);
		return $image;
	}
	
	function createPagebreak () {	// benötigt document-scope
		return new PDF_Pagebreak ($this->pdf);
	}
	
	function createBlock () {
		return new PDF_Block($this->pdf);
	}
	
	function addLine(PDF_Printable $printable, $space = 0) {
		$this->inhalt[] = array(printable => $printable, space => $space);
	}
	
	function printAll (PDF_Header $header = NULL, PDF_Footer $footer = NULL) {

		if ($footer == NULL) {
			$footer = new EmptyFooter($this, $aktuellesSemester['Text']);
			$footer->finish();
		}
		
		$body = $this->createBody();
		
		$texty = $body->getHeight() - 20;

		foreach ($this->getInhalt() as $line) {
			$printable = $line['printable'];

			/*
			* Loop until all of the array is placed; create new pages
			* as long as more table instances need to be placed.
			*/

			do {

				/* Place the printable instance */
				
				$result = $printable->fit(0, 0, $body->getWidth(), $texty, FALSE);
				
				//echo $printable->getHandle() . ' -> ' . $result . '<br>';

				switch($result) {
					case '_boxfull':
					case '_boxempty':
					case '_nextpage':
					case '_pagebreak':
						$body->finish();

						$page = $this->createPage($this->body, $header, $footer, "");
						$page->finish();

						$body = $this->createBody();
						$texty = $body->getHeight();
						break;
					case '_stop':
						$texty -= $printable->getHeight() + $line['space'];
						break;
					default:
						die("Kann die Zeile nicht platzieren: $result");
				}
			} while ($result == '_boxfull' || $result == '_boxempty' || $result == '_nextpage');

			/* Check the result; "_stop" means all is ok. */

			if ($result != "_stop" && $result != '_pagebreak') {
				if ($result == "_error") {
					die ("Error when placing printable: " . PDF_get_errmsg($pdf));
				} else {

					/* Any other return value is a user exit caused by
					 * the "return" option; this requires dedicated code to
					 * deal with.

					 * 				 */
					die ("User return found in printable: $result");
				}
			}

		}
		
		
		$body->finish();

		$page = $this->createPage($body, $header, $footer, "");
		$page->finish();

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

	function getInhalt () {
		return $this->inhalt;
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
		$this->header->finish();
		$this->footer->finish();
		
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
	protected $textx, $texty;
	protected $font;

	function  __construct(PDF_Document $document, $textwidth, $textheight) {
		$this->document = $document;
		$this->pdf = $document->getPDF();
		$this->font = $this->pdf->getFont();
		$this->textwidth = $textwidth;
		$this->textheight = $textheight;
		$this->textx = 0;
		$this->texty = $this->textheight;

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
		$this->handle = $this->pdf->begin_template_ext($this->textwidth, $this->textheight, $optlist);
	}

	function finish() {
		$this->pdf->end_template_ext(0, 0);
	}
}

?>

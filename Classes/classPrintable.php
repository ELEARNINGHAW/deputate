<?php


/**
 * Description of classPrintable
 *
 * @author papa
 */
abstract class PDF_Printable {
	
	protected $pdf;
	protected $document = null;
	protected $handle = 0;
	protected $regularfont;
	protected $boldfont;
	protected $fontsize = 10;
	protected $margin = 1.5;
	
	public function __construct (PDF $pdf) {
//		error_log("PDF_Printable - Memory usage before construction: " . number_format(memory_get_usage()) . "\n");
		$this->pdf = $pdf;
		$this->regularfont = $this->pdf->getFont();
		$this->boldfont = $this->pdf->getBoldfont();		
	}
	
	public function __destruct() {
		$this->cleanup();
//		error_log("PDF_Printable - Memory usage after destruction: " . number_format(memory_get_usage()) . "\n");
	}
	
	abstract function fit($x, $y, $width, $height);
	abstract function getHeight();
	abstract function destroy();
	abstract function getHandle();
	
	public function cleanup() {
		//cleanup everything from attributes
		foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
			unset($this->$clsVar);
		}
	}
}

class PDF_Textflow extends PDF_Printable {
	
	private $rewind = 0;
	
	public function __construct (PDF $pdf, $text = "", $optlist = NULL) {
		parent::__construct($pdf);
		$this->handle = $this->pdf->create_textflow($text, $optlist);
	}
	
	function fit($x, $y, $width, $height, $blind = FALSE) {
		$option = "";
		if ($blind) {
			$option .= " blind";
		}
		if ($this->rewind == 0) {
			$this->rewind = -1;
		} else {
			$option .= " rewind={$this->rewind}";
		}
		return $this->fitTextflow($x, $y, $x + $width, $y + $height, $option);
	}
	
	function getHeight() {
		return $this->infoTextflow("textheight");
	}
	
	function destroy() {
	}
	
	function getHandle() {
		return "Textflow: " . $this->handle;
	}
	function addTextflow ($text = "", $optionlist = NULL) {
		$this->handle = $this->pdf->add_textflow($this->handle, $text, $optionlist);
		if ($this->handle == 0) {
			die("Error: " . $this->pdf->get_errmsg());
		}
		
	}
	
	private function fitTextflow ($llx, $lly, $urx, $ury, $option = " ") {
		if ($this->handle == 0) {
			die("No Textflow created.");
		}
		return $this->pdf->fit_textflow($this->handle, $llx, $lly, $urx, $ury, $option);
	}
	
	private function infoTextflow ($keyword) {
		if ($this->handle == 0) {
			die("No Textflow created.");
		}
		return $this->pdf->info_textflow($this->handle, $keyword);
	}
}

class PDF_Table extends PDF_Printable {

	private $header = 0;
	private $footer = 0;
	private $rewind = 0;
			
	public function __construct (PDF $pdf, $header = 0, $footer = 0) {
		parent::__construct($pdf);
		$this->header = $header;
		$this->footer = $footer;
	}
	
	function fit($x, $y, $width, $height, $blind = FALSE) {
		return $this->fitTable($x, $y, $x + $width, $x + $height, $blind);
	}
	
	function getHeight() {
		$height = $this->infoTable("height");
		return $height;
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
		return "Table: {$this->handle}";
		
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
	
	private function fitTable ($llx, $lly, $urx, $ury, $blind = FALSE, $optlist = NULL) {
		
		if (!$optlist) {
			$optlist = "header=$this->header footer=$this->footer fill={{area=rowodd fillcolor={gray 0.95}}} stroke={{line=other}}";
			$optlist .= " debugshow=true";
		}
		
		if ($this->rewind != 0) {
			$optlist .= " rewind={$this->rewind}";			
		}
		
		if ($blind) {
			$optlist .= " blind=true";
			$this->rewind = -1;
		}
		
		/* Shade every other row; draw lines for all table cells.
		 * Add "showcells showborder" to visualize cell borders
		 */
		
		if ($this->handle == 0) {
			die("No Table creatred.");
		}
		
		$result = $this->pdf->fit_table($this->handle, $llx, $lly, $urx, $ury, $optlist);
		if ($result == "_error") {
			die ("Couldn't place table : {$this->pdf->get_errmsg()}");
		}
		return $result;
	}

	private function infoTable ($keyword) {
		if ($this->handle == 0) {
			die("No Table created.");
		}
		return $this->pdf->info_table($this->handle, $keyword);
	}

}

class PDF_Image extends PDF_Printable {
	
	private $size = 40;

	public function __construct (PDF $pdf) {
		parent::__construct($pdf);
	}
	
	function fit($x, $y, $width, $height, $blind = FALSE) {
		if ($height < $this->size) {
			return '_boxempty';
		}
		if ($blind) {
			return '_stop';
		} else {
			$option = "boxsize={200 $this->size} position={top left} fitmethod=meet";
			return $this->fitImage($x, $y + $height - $this->size, $option);
		}
	}
	
	function getHeight() {
//		return $this->infoImage('height');
		return $this->size;
	}
	
	function destroy() {
	}
	
	function getHandle() {
		return "Image: {$this->handle}";
	}

	public function loadImage ($file, $option = "") {
		$this->handle = $this->pdf->load_image("auto", $file, $option);
		if ($this->handle == 0) {
			die("Error: " . $this->pdf->get_errmsg());
		}
	}
	
	private function infoImage ($keyword, $option = "") {
		if ($this->handle == 0) {
			die("InfoImage: No Image loaded.");
		}
		return $this->pdf->info_image($this->handle, $keyword, $option);		
	}
	
	private function fitImage ($x, $y, $option) {
		if ($this->handle == 0) {
			die("FitImage: No Image loaded.");
		}
		$this->pdf->fit_image($this->handle, $x, $y, $option);
		return '_stop';
	}
}

class PDF_Pagebreak extends PDF_Printable {
	
	public function __construct (PDF $pdf) {
		parent::__construct($pdf);
	}
	
	function fit($x, $y, $width, $height, $blind = FALSE) {
		return '_pagebreak';
	}
	
	function getHeight() {
		return 0;
	}
	
	function destroy() {
	}
	
	function getHandle() {
		return "Pagebreak: {$this->handle}";
	}
	
}

class PDF_Block extends PDF_Printable {
	
	private $inhalt = array();
	
	public function __construct(PDF $pdf) {
		parent::__construct($pdf);
	}
	
	function fit($x, $y, $width, $height, $blind = FALSE) {
		$rc = '_boxempty';
		$texty = $height;
		foreach ($this->inhalt as $line) {
			$printable = $line['printable'];
			$return = $printable->fit(0, 0, $width, $texty, TRUE);
			if (is_numeric($line['space'])) {
				$texty -= $printable->getHeight() + $line['space'];
			} else {
				$texty -= $printable->getHeight();
				error_log(print_r($line, true));
			}
			if ($texty < 0) {
				return $rc;
			}
			$rc = '_boxfull';
		}
		
		if (!$blind) {
			$texty = $height;
			foreach ($this->inhalt as $line) {
				$printable = $line['printable'];
				$return = $printable->fit(0, 0, $width, $texty, FALSE);
			if (is_numeric($line['space'])) {
				$texty -= $printable->getHeight() + $line['space'];
			} else {
				$texty -= $printable->getHeight();
				error_log(print_r($line, true));
			}
				if ($texty < 0) {
					die ("Text passt doch nicht.");
				}
			}
		}
		return '_stop';
	}
	
	function getHeight() {
		$height = 0;
		foreach ($this->inhalt as $line) {
			if (is_numeric($line['space'])) {
				$height += $line['printable']->getHeight() + $line['space'];
			} else {
				$height += $line['printable']->getHeight();
				error_log(print_r($line, true));
			}
		}
		return $height;
	}
	
	function destroy() {
		;
	}
	
	function finish() {
	}
	
	function getHandle() {
		return "Block: {$this->handle}";
	}

	function addLine(PDF_Printable $printable, $space = 0) {
		$this->inhalt[] = array('printable' => $printable, 'space' => $space, 'printed' => FALSE);
	}
	
	
}


<?php
/**
 * Description of classReports
 *
 * @author papa
 */

include_once '../Classes/classPDF2.php';
include_once '../Classes/classPrintable.php';
include_once '../Classes/classDBConnect.php';
include_once '../Classes/classTable.php';
include_once '../Classes/classRelation.php';
include_once '../Classes/classHeader.php';
include_once '../Classes/classFooter.php';

include_once '../spezielle Funktionen/aktuellesSemester.php';

abstract class Report {

	protected $pdf;
	protected $font, $boldfont, $regularfont;
	protected $fontsize, $fontsizesmall, $margin;
	protected $option, $optionlist, $optionlistsmall;

	protected $head_opts_right, $head_opts_left, $head_opts_center, $body_opts_left, $body_opts_center, $body_opts_right, $foot_opts_left, $foot_opts_right;
	
	protected $dbTable, $document;
	protected $aktuellesSemester;
	protected $first = TRUE;

	private $connection;
	private $file = NULL;
	private $result;
	
	abstract public function add(array $DozKurz, $attribut, $deadline);

	protected function __construct(DBConnect $connection, Table $dbTable, $file) {
		
		$this->connection = $connection;
		$this->dbTable = $dbTable;
		$this->file = $file;

		$this->pdf = new PDF($file);
		$this->document = $this->pdf->createDocument($dbTable->get_header(), 'Martin Holle', 'Report Bilanz', NULL);
		$this->aktuellesSemester = getAktuellesSemester();
		
		// Druck initialisieren
		$this->font = $this->pdf->getFont();
		$this->boldfont = $this->pdf->getBoldfont();
		$this->regularfont = $this->pdf->getFont();

		// Formatierungen
		$this->fontsize = 9;
		$this->fontsizesmall = 7;
		$this->margin = 2;

		$this->option = "";
		$this->optionlist = "font=$this->font fontsize=$this->fontsize nextparagraph=true alignment=left leading=120%";
		$this->optionlistsmall = "font=$this->font fontsize=$this->fontsizesmall nextline=true alignment=left leading=100%";

		$this->head_opts_right = "fittextline={position={right center} font=$this->boldfont fontsize=$this->fontsize} margin=$this->margin";
		$this->head_opts_left = "fittextline={position={left center} font=$this->boldfont fontsize=$this->fontsize} margin=$this->margin";
		$this->head_opts_center = "fittextline={position={center center} font=$this->boldfont fontsize=$this->fontsize} margin=$this->margin";
		$this->body_opts_left = "fittextline={position={left center} font=$this->regularfont fontsize=$this->fontsize} margin=$this->margin";
		$this->body_opts_center = "fittextline={position={center center} font=$this->regularfont fontsize=$this->fontsize} margin=$this->margin";
		$this->body_opts_right = "fittextline={position={right center} font=$this->regularfont fontsize=$this->fontsize} margin=$this->margin";
		$this->foot_opts_left = "fittextline={position={left center} font=$this->boldfont fontsize=$this->fontsize} margin=$this->margin";
		$this->foot_opts_right = "fittextline={position={right center} font=$this->boldfont fontsize=$this->fontsize} margin=$this->margin";
	}
	
	function __destruct() {
		gc_collect_cycles();	// PDF creates cycles...
	}
	
	protected function query(array $cols, array $where, array $group, array $order, $distinct = FALSE) {
		$table = $this->dbTable->get_table();
		
		$query = "SELECT ";
		$query .= $distinct? "DISTINCT ": "";
		$query .= implode(',', $cols)." FROM `$table`";
		if (count($where) > 0) {
			$query .= " WHERE " . implode(' AND ', $where);
		}
		if (count($group) > 0) {
			$query .= " GROUP BY ".implode(',', $group);
		}
		if (count($order) > 0) {
			$query .= " ORDER BY ".implode(',', $order);
		}		
		$result = $this->connection->query($query);

		if ($result) {
			error_log("$query\nRows: $result->num_rows\n");
		} else {
			error_log("$query\nNo Result!.\n");
		}
		
		return $result;
	}
	
	public function closeAndPrint($file = NULL) {
		$this->close();
		$this->pdf->printOut($file);
	}
	
	public function closeAndMail($to, $text, $subject) {
		$this->close();
		$this->pdf->sendMail($to, $text, $subject);
		?>
		<script type='text/javascript'>
		<!--
			alert('Mail gesendet an <?=$to?>.');
		-->
		</script>
		<?php
	}
	
	public function close() {
		$this->document->printAll($this->getHeader(), $this->getFooter());	
		$this->document->finish();
	}
	
	public function getDataWithFile() {
		return array('data' => $this->pdf->get_buffer(), 'file' => $this->pdf->getFile());
	}
	
	public function getDataEncodedWithFile() {
		return array('data' => chunk_split(base64_encode($this->pdf->get_buffer())), 'file' => $this->pdf->getFile());
	}

	protected function getHeader() {
		return NULL;
	}
	
	protected function getFooter() {
		return NULL;
	}
	
	public function test() {
		echo "classReport test.<br>";
	}
}


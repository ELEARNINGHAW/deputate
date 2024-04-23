<?php

include_once 'classReport.php';

/**
 * Description of classReportZeitkonto
 *
 * @author papa
 */
class ReportZeitkontoShort extends Report {

    public function __construct($connection, Table $dbTable, $file = 'Zeitkonto Übersicht.pdf') {

        parent::__construct($connection, $dbTable, $file);
    }

    public function add(array $DozKurz, $attribut, $deadline) {
        $cap = 36.0;

        // aggregierte Daten abfragen
        $index = array("DozKurz", "Status");
        $columns = array("Name", "Vorname", "Anrede", "Department");
        $anzahl = "Anzahl";

        $group1 = array_merge($index, $columns);
        $cols1 = array_merge($group1, array("count(*) as `$anzahl`"));
        $where1 = count($DozKurz) > 0 ?
                array("$index[0] = '" . implode("' OR $index[0] = '", $DozKurz) . "'") :
                array();
        // $where = count($DozKurz) > 0? array("$index[0] in ('".implode("','", $DozKurz)."')"): array();
        $order1 = array("Department", "Name");

        $where1[] = "Zeitkonto = true";

        $groupArray = $this->query($cols1, $where1, $group1, $order1);

        if ($groupArray == false || $groupArray->num_rows == 0) {
            echo 'nothing to do...<br>';
            return FALSE;
        }

// Detaillierte Daten abfragen

        $cols2 = explode(',', $this->dbTable->get_rows());
#		$where2 = array("Kurz = '$DozKurz'", "Status = '$Status'");
        $group2 = array();
        $order2 = $this->dbTable->get_order();

        foreach ($groupArray as $value) {
            $where2 = array();
            foreach ($index as $col) {
                $where2[] = "`$col` = '$value[$col]' ";
            }

            $rowArray = $this->query($cols2, $where2, $group2, $order2);

            $part = $this->document->createBlock();

            // Gruppenkopf erstellen
            $head = $this->document->createBlock();
//            $textflow = $this->document->createTextflow("({$value['Department']}) {$value['Name']}, ", $this->optionlist . " font=$this->boldfont");
//            $textflow->addTextflow("{$value['Vorname']}, {$value['Status']}", $this->optionlist);
            $textflow = $this->document->createTextflow(
                    "({$value['Department']}) <font={$this->boldfont}>{$value['Name']}, <font={$this->font}>{$value['Vorname']}, {$value['Status']}", 
                    $this->optionlist);
            $head->addLine($textflow);
            $part->addLine($head, 5);

            // Detail-Tabelle erstellen
            $table = $this->document->createTable();

            $sum1 = 0;
            $sum2 = 0;
            $tablerow = 1;
            $decimals = 2;

            $table->addCell(1, $tablerow, "Semester", $this->head_opts_center);
            $table->addCell(2, $tablerow, "Stunden", $this->head_opts_center);
            $table->addCell(3, $tablerow, "Pflicht", $this->head_opts_center);
            $table->addCell(4, $tablerow, "Bilanz", $this->head_opts_center);
            $table->addCell(5, $tablerow, "Summe voll", $this->head_opts_center);
            $table->addCell(6, $tablerow, "..begrenzt", $this->head_opts_center);
            $table->addCell(7, $tablerow, "Kommentar", $this->head_opts_center);
            ++$tablerow;

            foreach ($rowArray as $row) {
                $stunden = $row['Stunden'];
                $pflicht = $row['Pflicht'];
                $saldo = $row['Saldo'];
                $sum1 += $saldo;
                $sum2 += $saldo;
                if ($sum2 > $cap) {
                    $sum2 = $cap;
                }
                $table->addCell(1, $tablerow, array_key_exists('KurzText', $row) ? $row['KurzText'] : "", $this->body_opts_left);
                $table->addCell(2, $tablerow, number_format($stunden, $decimals), $this->body_opts_right);
                $table->addCell(3, $tablerow, number_format($pflicht, $decimals), $this->body_opts_right);
                $table->addCell(4, $tablerow, number_format($saldo, $decimals), $this->body_opts_right);
                $table->addCell(5, $tablerow, number_format($sum1, $decimals), $this->body_opts_right);
                $table->addCell(6, $tablerow, number_format($sum2, $decimals), $this->body_opts_right);
                $table->addCell(7, $tablerow, array_key_exists('Kommentar', $row) ? $row['Kommentar'] : "", $this->body_opts_left);
                ++$tablerow;
            }

            $part->addLine($table, 20);

            // Gruppenfuß erstellen


            $this->document->addLine($part);
        }
        return TRUE;
    }

    protected function getHeader() {
        //$header = new HeaderWithSemester($this->document, $aktuellesSemester['Text']);
        $header = new PDF_Header($this->document);
        $header->finish();
        return $header;
    }

    protected function getFooter() {
        $footer = new FooterWithCounter($this->document, $this->aktuellesSemester['Text']);
        $footer->finish();
        return $footer;
    }

}

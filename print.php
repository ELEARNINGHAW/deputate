<?php
/* $Id: hello.php,v 1.15 2006/10/01 20:33:35 rjs Exp $
 *
 * PDFlib client: hello example in PHP
 */
$pagewidth = 595;
$pageheight = 842;

//$p = $p->new();
$p = new pdflib();

# This means we must check return values of load_font() etc.
$p->set_option("errorpolicy = return");

/* This line is required to avoid problems on Japanese systems */
//$p->set_option("hypertextencoding = winansi");
$p->set_option("textformat = utf8");


/*  open new PDF file; insert a file name to create the PDF on disk */
if ($p->begin_document("", "") == 0) {
    die("Error: " . $p->get_errmsg($p));
}

$p->set_info("Creator", "hello.php");
$p->set_info("Author", "Rainer Schaaf");
$p->set_info("Title", "Hello world (PHP)!");

$font = $p->load_font("Helvetica-Bold", "winansi", "");
if ($font == 0) {
    die("Error: " . $p->get_errmsg($p));
}


$template = $p->begin_template_ext($pagewidth, $pageheight, "");
$p->fit_textline("x = 30, y = 10", 30, 10, "font=$font fontsize=8");
$p->fit_textline("x = 30, y = pageheight - 10 ", 30, $pageheight-10, "font=$font fontsize=8");
$p->end_template_ext(0, 0);

$p->begin_page_ext($pagewidth, $pageheight, "");

$p->setfont($font, 24.0);

$p->fit_image($template,  0.0,  0.0, "");

$p->set_text_pos(50, 700);
$p->show("Hello world!");
$p->continue_text("(says PHP)");
$p->continue_text("Umlaute: äöüÄÖÜ");
$p->end_page_ext("");

$p->end_document("");

$buf = $p->get_buffer();
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=hello.pdf");
print $buf;

$p->delete($p);

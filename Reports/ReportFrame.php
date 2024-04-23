<?php
	$report=filter_input(INPUT_GET,'report');
	$docTable=filter_input(INPUT_GET,'table');
	$relation=filter_input(INPUT_GET,'relation');
	$selection=filter_input(INPUT_GET,'selection');
	$detail=filter_input(INPUT_GET,'detail');
?>

<!DOCTYPE HTML>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Reports</title>
    </head>
	<frameset cols="25%,*">
		<frame src="ReportMenu.php?report=<?=$report?>&table=<?=$docTable?>&detail=<?=$detail?>" name="links" />
		<frame src="ReportLong.php?report=<?=$report?>&action=print&table=<?=$docTable?>&columns=Kurz&selection=<?=$detail?>" name="rechts" />
	</frameset>
</html>

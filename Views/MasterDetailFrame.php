<?php
$master = filter_input(INPUT_GET, 'master');
$detail = filter_input(INPUT_GET, 'detail');
$relation = filter_input(INPUT_GET, 'relation');
$function = filter_input(INPUT_GET, 'function');
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
	<frameset rows="50%,*">
		<frame src="MasterTableList.php?master=<?= $master ?>&detail=<?= $detail ?>&relation=<?= $relation ?>" name="oben" />
		<frame name="unten" />
	</frameset>
</html>

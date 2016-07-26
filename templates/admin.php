<?php
/** @var $l OC_L10N */
/**
 *
 * @var $_ array
 */

$syslogfile = '/var/log/syslog';
$allsyslogfileparsed = Array();

if (file_exists($syslogfile)) {
		$command = " zcat -f /var/log/syslog* | grep 'oobactivity\[' ";
 		$result = shell_exec($command);
  		$allsyslogfileparsed = explode("\n", $result );
}

/**
 * $allsyslogfileparsed - syslog array
 * $lastlines - to show the last elements in the $allsyslogfileparsed (most recent activities)
 * @param array 	$allsyslogfileparsed
 * @param integer 	$lastlines
 */
function showSyslog($allsyslogfileparsed, $lastlines) {	
	$end = 0;	
	if($lastlines==null) {
		$end = count($allsyslogfileparsed);
	} else{
		$end = $lastlines;
	}
	for ($i = 0;$i < $end; $i++) {		
		echo '	<tr>';
		echo '	<td>';
		echo $allsyslogfileparsed[$i];
		echo '	</td>';
		echo '	</tr>';
	}	
}
?>

<form id="ooba" class="section" action="logviewer.php">
	<h2>Out-Of-Band Activity</h2>
	
	<table cellspacing="0" cellpadding="0" border="0" width="325">
		<tr>
			<td>
				<table style="width: 1000px;" cellspacing="0" cellpadding="1" border="1">
					<tr style="color: white; background-color: grey">
						<th>
						All syslog
						</th>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<div style="width: 1000px; height: 200px; overflow: auto;">
					<table id="table1" cellspacing="0" cellpadding="1" border="1" width="300">						
						<?php showSyslog($allsyslogfileparsed,null);?>										
					</table>
				</div>
			</td>
		</tr>
	</table>
</form>






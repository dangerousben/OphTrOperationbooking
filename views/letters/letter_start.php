<p<?php if(@$accessible) { ?> class="accessible"<?php } ?>>Dear <?php echo $to; ?>,</p>

<p<?php if(@$accessible) { ?> class="accessible"<?php } ?>>
	<?php if(@$patient_ref) { 
		echo $patient->fullname . ', ';
	} ?>
	<strong>Hospital Reference Number: <?php echo $patient->hos_num; ?></strong>
	<?php if($patient->nhsnum) { ?><br/> NHS Number: <?php echo $patient->nhsnum; } ?>
	<?php if(@$patient_ref) { ?>
	<br /><?php echo $patient->correspondAddress->letterline ?>
	<br />DOB: <?php echo $patient->NHSDate('dob') ?>, <?php echo ($patient->gender == 'M') ? 'Male' : 'Female'; ?>
	<?php } ?>
</p>

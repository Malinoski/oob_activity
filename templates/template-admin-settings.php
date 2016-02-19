<?php
use Doctrine\Common\Collections\Expr\Value;
/** @var $l OC_L10N */
/** @var $_ array */
?>

<form id="activity" class="section">
	<h2><?php p($l->t('Activity')); ?></h2>
	
	Log File Activity:
	<br/>
	<input
		type='radio'
		name='logActivityEnable'
		value='1'
		<?php echo($_["logActivityEnable"] === '1' ? 'checked="checked"' : ''); ?> />
	<?php p($l->t("Enabled")); ?>
	<br/>
	<input
		type='radio'
		name='logActivityEnable'
		value='0'
		<?php echo($_["logActivityEnable"] === '0' ? 'checked="checked"' : ''); ?> />
	<?php p($l->t("Disabled")); ?>
	<br/>
	Log file path:
	<br/>
	<?php echo(realpath(".")) ?><input type="text" name="logFilePath" id="logFilePath" value="<?php echo($_["logFilePath"])?>"/>
</form>
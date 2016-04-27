$(document).ready(function() {
	function saveSettings() {
		OC.msg.startSaving('#ooba_notifications_msg');
		var post = $('#ooba_notifications').serialize();

		$.post(OC.generateUrl('/apps/ooba/settings'), post, function(response) {
			OC.msg.finishedSuccess('#ooba_notifications_msg', response.data.message);
		});
	}

	var $oobaNotifications = $('#ooba_notifications');
	$oobaNotifications.find('input[type=checkbox]').change(saveSettings);

	$oobaNotifications.find('select').change(saveSettings);

	$oobaNotifications.find('.ooba_select_group').click(function() {
		var $selectGroup = '#ooba_notifications .' + $(this).attr('data-select-group');
		var $filteredBoxes = $($selectGroup).not(':disabled');
		var $checkedBoxes = $filteredBoxes.filter(':checked').length;

		$filteredBoxes.attr('checked', true);
		if ($checkedBoxes === $filteredBoxes.filter(':checked').length) {
			// All values were already selected, so invert it
			$filteredBoxes.attr('checked', false);
		}

		saveSettings();
	});
});

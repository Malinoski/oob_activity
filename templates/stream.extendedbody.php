<?php

script('ooba', 'script');
style('ooba', 'style');
?>

<?php $_['appExtendedNavigation']->printPage(); ?>

<div id="app-content">
	<div id="emptycontent" class="hidden">
		<div class="icon-ooba"></div>
		<h2><?php p($l->t('No ooba yet')); ?></h2>
		<p><?php p($l->t('This stream will show events like additions, changes & shares')); ?></p>
	</div>

	<div id="container" data-ooba-filter="<?php p($_['filter']) ?>">
	</div>

	<div id="loading_oobas" class="icon-loading"></div>

	<div id="no_more_oobas" class="hidden">
		<?php p($l->t('No more events to load')) ?>
	</div>
</div>

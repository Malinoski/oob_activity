<?php

/**
 * ownCloud - Ooba App
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/** @var $l OC_L10N */
/** @var $theme OC_Defaults */
/** @var $_ array */
script('ooba', 'script');
style('ooba', 'style');
?>

<?php $_['appNavigation']->printPage(); ?>

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

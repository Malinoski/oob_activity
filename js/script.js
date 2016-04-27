/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
$(function(){
	var OCOoba={};

	OCOoba.Filter = {
		filter: undefined,
		currentPage: 0,
		navigation: $('#app-navigation'),


		_onPopState: function(params) {
			params = _.extend({
				filter: 'all'
			}, params);

			this.setFilter(params.filter);
		},

		setFilter: function (filter) {
			if (filter === this.filter) {
				return;
			}

			this.navigation.find('a[data-navigation=' + this.filter + ']').parent().removeClass('active');
			this.currentPage = 0;

			this.filter = filter;

			OCOoba.InfinitScrolling.container.animate({ scrollTop: 0 }, 'slow');
			OCOoba.InfinitScrolling.container.children().remove();
			$('#emptycontent').addClass('hidden');
			$('#no_more_oobas').addClass('hidden');
			$('#loading_oobas').removeClass('hidden');
			OCOoba.InfinitScrolling.ignoreScroll = false;

			this.navigation.find('a[data-navigation=' + filter + ']').parent().addClass('active');

			OCOoba.InfinitScrolling.prefill();
		}
	};

	OCOoba.InfinitScrolling = {
		ignoreScroll: false,
		container: $('#container'),
		lastDateGroup: null,
		content: $('#app-content'),

		prefill: function () {
			if (this.content.scrollTop() + this.content.height() > this.container.height() - 100) {
				OCOoba.Filter.currentPage++;

				$.get(
					OC.generateUrl('/apps/ooba/oobas/fetch'),
					'filter=' + OCOoba.Filter.filter + '&page=' + OCOoba.Filter.currentPage,
					function (data) {
						OCOoba.InfinitScrolling.handleOobasCallback(data);
					}
				);
			}
		},

		onScroll: function () {
			if (!OCOoba.InfinitScrolling.ignoreScroll && OCOoba.InfinitScrolling.content.scrollTop() +
			 OCOoba.InfinitScrolling.content.height() > OCOoba.InfinitScrolling.container.height() - 100) {
				OCOoba.Filter.currentPage++;

				OCOoba.InfinitScrolling.ignoreScroll = true;
				$.get(
					OC.generateUrl('/apps/ooba/oobas/fetch'),
					'filter=' + OCOoba.Filter.filter + '&page=' + OCOoba.Filter.currentPage,
					function (data) {
						if (OCOoba.InfinitScrolling.handleOobasCallback(data)) {
							OCOoba.InfinitScrolling.ignoreScroll = false;
						}
					}
				);
			}
		},

		handleOobasCallback: function (data) {
			var $numOobas = data.length;

			if ($numOobas > 0) {
				for (var i = 0; i < data.length; i++) {
					var $ooba = data[i];
					this.appendOobaToContainer($ooba);
				}

				// Continue prefill
				this.prefill();
				return true;

			} else if (OCOoba.Filter.currentPage == 1) {
				// First page is empty - No oobas :(
				var $emptyContent = $('#emptycontent');
				$emptyContent.removeClass('hidden');
				if (OCOoba.Filter.filter == 'all') {
					$emptyContent.find('p').text(t('ooba', 'This stream will show events like additions, changes & shares'));
				} else {
					$emptyContent.find('p').text(t('ooba', 'There are no events for this filter'));
				}
				$('#loading_oobas').addClass('hidden');

			} else {
				// Page is empty - No more oobas :(
				$('#no_more_oobas').removeClass('hidden');
				$('#loading_oobas').addClass('hidden');
			}
			return false;
		},

		appendOobaToContainer: function ($ooba) {
			this.makeSureDateGroupExists($ooba.relativeTimestamp, $ooba.readableTimestamp);
			this.addOoba($ooba);
		},

		makeSureDateGroupExists: function($relativeTimestamp, $readableTimestamp) {
			var $lastGroup = this.container.children().last();

			if ($lastGroup.data('date') !== $relativeTimestamp) {
				var $content = '<div class="section ooba-section group" data-date="' + escapeHTML($relativeTimestamp) + '">' + "\n"
					+'	<h2>'+"\n"
					+'		<span class="has-tooltip" title="' + escapeHTML($readableTimestamp) + '">' + escapeHTML($relativeTimestamp) + '</span>' + "\n"
					+'	</h2>' + "\n"
					+'	<div class="boxcontainer">' + "\n"
					+'	</div>' + "\n"
					+'</div>';
				$content = $($content);
				OCOoba.InfinitScrolling.processElements($content);
				this.container.append($content);
				this.lastDateGroup = $content;
			}
		},

		addOoba: function($ooba) {
			var $content = ''
				+ '<div class="box">' + "\n"
				+ '	<div class="messagecontainer">' + "\n"

				+ '		<div class="ooba-icon ' + (($ooba.typeicon) ? escapeHTML($ooba.typeicon) + ' svg' : '') + '"></div>' + "\n"

				+ '		<div class="oobasubject">' + "\n"
				+ (($ooba.link) ? '			<a href="' + $ooba.link + '">' + "\n" : '')
				+ '			' + $ooba.subjectformatted.markup.trimmed + "\n"
				+ (($ooba.link) ? '			</a>' + "\n" : '')
				+ '		</div>' + "\n"

				+'		<span class="oobatime has-tooltip" title="' + escapeHTML($ooba.readableDateTimestamp) + '">' + "\n"
				+ '			' + escapeHTML($ooba.relativeDateTimestamp) + "\n"
				+'		</span>' + "\n";

			if ($ooba.message) {
				$content += '<div class="oobamessage">' + "\n"
					+ $ooba.messageformatted.markup.trimmed + "\n"
					+'</div>' + "\n";
			}

			if ($ooba.previews && $ooba.previews.length) {
				$content += '<br />';
				for (var i = 0; i < $ooba.previews.length; i++) {
					var $preview = $ooba.previews[i];
					$content += (($preview.link) ? '<a href="' + $preview.link + '">' + "\n" : '')
						+ '<img class="preview' + (($preview.isMimeTypeIcon) ? ' preview-mimetype-icon' : '') + '" src="' + $preview.source + '" alt=""/>' + "\n"
						+ (($preview.link) ? '</a>' + "\n" : '')
				}
			}

			$content += '	</div>' + "\n"
				+'</div>';

			$content = $($content);
			OCOoba.InfinitScrolling.processElements($content);
			this.lastDateGroup.append($content);
		},

		processElements: function (parentElement) {
			$(parentElement).find('.avatar').each(function() {
				var element = $(this);
				element.avatar(element.data('user'), 28);
			});

			$(parentElement).find('.has-tooltip').tooltip({
				placement: 'bottom'
			})
		}
	};

	OC.Util.History.addOnPopStateHandler(_.bind(OCOoba.Filter._onPopState, OCOoba.Filter));
	OCOoba.Filter.setFilter(OCOoba.InfinitScrolling.container.attr('data-ooba-filter'));
	OCOoba.InfinitScrolling.content.on('scroll', OCOoba.InfinitScrolling.onScroll);

	OCOoba.Filter.navigation.find('a[data-navigation]').on('click', function (event) {
		var filter = $(this).attr('data-navigation');
		if (filter !== OCOoba.Filter.filter) {
			OC.Util.History.pushState({
				filter: filter
			});
		}
		OCOoba.Filter.setFilter(filter);
		event.preventDefault();
	});

	$('#enable_rss').change(function () {
		if (this.checked) {
			$('#rssurl').removeClass('hidden');
		} else {
			$('#rssurl').addClass('hidden');
		}
		$.post(OC.generateUrl('/apps/ooba/settings/feed'), 'enable=' + this.checked, function(response) {
			$('#rssurl').val(response.data.rsslink);
		});
	});

	$('#rssurl').on('click', function () {
		$('#rssurl').select();
	});
});


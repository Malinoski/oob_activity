/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function() {
	var TEMPLATE =
		'<div class="ooba-section">' +
		'{{#if loading}}' +
		'<div class="loading" style="height: 50px"></div>' +
		'{{end}}' +
		'{{else}}' +
		'<ul>' +
		'{{#each oobas}}' +
		'    <li class="ooba box">' +
		'        <div class="ooba-icon {{typeIconClass}}"></div>' +
		'        <div class="oobasubject">{{{subject}}}</div>' +
		'        <span class="oobatime has-tooltip" title="{{formattedDateTooltip}}">{{formattedDate}}</span>' +
		'        <div class="oobamessage">{{{message}}}</div>' +
		'        {{#if previews}}' +
		'        <div class="previews">' +
		'        {{#each previews}}' +
		'            <img class="preview {{previewClass}}" src="{{source}}" alt="" />' +
		'        {{/each}}' +
		'        </div>' +
		'        {{/if}}' +
		'    </li>' +
		'{{else}}' +
		'    <li class="empty">{{emptyMessage}}</li>' +
		'{{/each}}' +
		'</ul>' +
		'{{/if}}' +
		'</div>';

	/**
	 * Format an ooba model for display
	 *
	 * @param {OCA.Ooba.OobaModel} ooba
	 * @return {Object}
	 */
	function formatOoba(ooba) {
		var output = {
			subject: ooba.get('subjectformatted').markup.trimmed,
			formattedDate: ooba.get('relativeDateTimestamp'),
			formattedDateTooltip: ooba.get('readableDateTimestamp'),
			message: ooba.get('messageformatted').markup.trimmed
		};

		if (ooba.has('typeicon')) {
			output.typeIconClass = ooba.get('typeicon') + ' svg';
		}
		/**
		 * Disable previews in the rightside bar,
		 * it's always the same image anyway.
		if (ooba.has('previews')) {
			output.previews = _.map(ooba.get('previews'), function(data) {
				return {
					previewClass: data.isMimeTypeIcon ? 'preview-mimetype-icon': '',
					source: data.source
				};
			});
		}
		*/
		return output;
	}

	/**
	 * @class OCA.Ooba.OobaTabView
	 * @classdesc
	 *
	 * Displays ooba information for a given file
	 *
	 */
	var OobaTabView = OCA.Files.DetailTabView.extend(
		/** @lends OCA.Ooba.OobaTabView.prototype */ {
		id: 'oobaTabView',
		className: 'oobaTabView tab',

		_loading: false,

		initialize: function() {
			this.collection = new OCA.Ooba.OobaCollection();
			this.collection.setObjectType('files');
			this.collection.on('request', this._onRequest, this);
			this.collection.on('sync', this._onEndRequest, this);
			this.collection.on('update', this._onChange, this);
			this.collection.on('error', this._onError, this);
		},

		template: function(data) {
			if (!this._template) {
				this._template = Handlebars.compile(TEMPLATE);
			}
			return this._template(data);
		},

		get$: function() {
			return this.$el;
		},

		getLabel: function() {
			return t('ooba', 'Oobas');
		},

		setFileInfo: function(fileInfo) {
			this._fileInfo = fileInfo;
			if (this._fileInfo) {
				this.collection.setObjectId(this._fileInfo.get('id'));
				this.collection.fetch();
			} else {
				this.collection.reset();
			}
		},

		_onError: function() {
			OC.Notification.showTemporary(t('ooba', 'Error loading oobas'));
		},

		_onRequest: function() {
			this._loading = true;
			this.render();
		},

		_onEndRequest: function() {
			this._loading = false;
			// empty result ?
			if (!this.collection.length) {
				// render now as there will be no update event
				this.render();
			}
		},

		_onChange: function() {
			this._loading = false;
			this.render();
		},

		/**
		 * Renders this details view
		 */
		render: function() {
			if (this._fileInfo) {
				this.$el.html(this.template({
					loading: this._loading,
					oobas: this.collection.map(formatOoba),
					emptyMessage: t('ooba', 'No oobas')
				}));
				this.$el.find('.avatar').each(function() {
					var element = $(this);
					element.avatar(element.data('user'), 28);
				});
				this.$el.find('.has-tooltip').tooltip({
					placement: 'bottom'
				});
			} else {
				// TODO: render placeholder text?
			}
		}
	});

	OCA.Ooba = OCA.Ooba || {};
	OCA.Ooba.OobaTabView = OobaTabView;
})();


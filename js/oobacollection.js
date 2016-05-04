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

	OCA.Ooba = OCA.Ooba || {};

	/**
	 * @class OCA.Ooba.OobaCollection
	 * @classdesc
	 *
	 * Displays ooba information for a given file
	 *
	 */
	var OobaCollection = OC.Backbone.Collection.extend(
		/** @lends OCA.Ooba.OobaCollection.prototype */ {

		/**
		 * Id of the file for which to filter oobas by
		 *
		 * @var int
		 */
		_objectId: null,

		/**
		 * Type of the object to filter by
		 *
		 * @var string
		 */
		_objectType: null,

		model: OCA.Ooba.OobaModel,

		/**
		 * Sets the object id to filter by or null for all.
		 * 
		 * @param {int} objectId file id or null
		 */
		setObjectId: function(objectId) {
			this._objectId = objectId;
		},

		/**
		 * Sets the object type to filter by or null for all.
		 * 
		 * @param {int} objectType file id or null
		 */
		setObjectType: function(objectType) {
			this._objectType = objectType;
		},

		url: function() {
			var query = {
				page: 1,
				filter: 'all'
			};
			var url = OC.generateUrl('apps/ooba/oobas/fetch');
			if (this._objectId) {
				query.objectid = this._objectId;
				query.filter = 'filter';
			}
			if (this._objectType) {
				query.objecttype = this._objectType;
				query.filter = 'filter';
			}
			url += '?' + OC.buildQueryString(query);
			return url;
		}
	});

	OCA.Ooba.OobaCollection = OobaCollection;
})();


/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
describe('OobaCollection', function() {
	var OobaCollection = OCA.Ooba.OobaCollection;

	describe('query url', function() {
		it('does not filter by default', function() {
			var col = new OobaCollection();
			expect(col.url())
				.toEqual(
					OC.generateUrl('apps/ooba/oobas/fetch') +
					'?page=1&filter=all'
				);
		});
		it('filters by id and type when specified', function() {
			var col = new OobaCollection();
			col.setObjectType('files');
			col.setObjectId(512);
			expect(col.url())
				.toEqual(
					OC.generateUrl('apps/ooba/oobas/fetch') +
					'?page=1&filter=filter&objectid=512&objecttype=files'
				);
		});
	});
});


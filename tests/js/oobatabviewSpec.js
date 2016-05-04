/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
describe('OobaTabView', function() {
	var OobaCollection = OCA.Ooba.OobaCollection;
	var OobaTabView = OCA.Ooba.OobaTabView;

	describe('rendering', function() {
		var fetchStub, fileInfo, tabView;

		beforeEach(function() {
			fetchStub = sinon.stub(OobaCollection.prototype, 'fetch');
			fileInfo = new OCA.Files.FileInfoModel({
				id: 123,
				name: 'test.txt'
			});
			tabView = new OobaTabView();
		});
		afterEach(function() {
			fetchStub.restore();
			tabView.remove();
		});

		it('reloads matching oobas when setting file info model', function() {
			tabView.setFileInfo(fileInfo);
			expect(fetchStub.calledOnce).toEqual(true);
			var url = OC.parseQueryString(tabView.collection.url());
			expect(url.objectid).toEqual('123');
			expect(url.objecttype).toEqual('files');
		});

		it('renders loading icon while fetching oobas', function() {
			tabView.setFileInfo(fileInfo);
			tabView.collection.trigger('request');

			expect(tabView.$el.find('.loading').length).toEqual(1);
			expect(tabView.$el.find('.ooba').length).toEqual(0);
		});

		it('renders oobas', function() {
			var ooba1 = {
				subjectformatted: {markup: {trimmed: 'The <span class="markup">Subject</span>'}},
				relativeDateTimestamp: 'seconds ago',
				readableDateTimestamp: 'readable date',
				messageformatted: {markup: {trimmed: 'Some <span class="markup">message</span>!'}},
				typeicon: 'icon-add-color',
				previews: [{
					isMimeTypeIcon: true,
					source: OC.imagePath('core', 'filetypes/text.svg')
				}, {
					isMimeTypeIcon: false,
					source: OC.imagePath('core', 'filetypes/text.svg')
				}]
			};
			var ooba2 = {
				subjectformatted: {markup: {trimmed: 'The Subject Two'}},
				relativeDateTimestamp: 'years ago',
				readableDateTimestamp: 'once upon a time',
				messageformatted: {markup: {trimmed: 'Ooba Two'}}
			};
			tabView.setFileInfo(fileInfo);
			tabView.collection.set([ooba1, ooba2]);

			var $oobas = tabView.$el.find('.ooba');
			expect($oobas.length).toEqual(2);
			var $a1 = $oobas.eq(0);
			expect($a1.find('.oobasubject').text()).toEqual('The Subject');
			expect($a1.find('.oobasubject .markup').length).toEqual(1);
			expect($a1.find('.oobamessage').text()).toEqual('Some message!');
			expect($a1.find('.oobamessage .markup').length).toEqual(1);
			expect($a1.find('.ooba-icon').hasClass('icon-add-color')).toEqual(true);
			expect($a1.find('.oobatime').text()).toEqual('seconds ago');
			expect($a1.find('.oobatime').attr('data-original-title')).toEqual('readable date');

			/*
			expect($a1.find('.previews img').length).toEqual(2);
			expect($a1.find('.previews img').eq(0).hasClass('preview-mimetype-icon')).toEqual(true);
			expect($a1.find('.previews img').eq(1).hasClass('preview-mimetype-icon')).toEqual(false);
			expect($a1.find('.previews img').eq(0).attr('src')).toEqual(OC.imagePath('core', 'filetypes/text.svg'));
			*/
			expect($a1.find('.previews').length).toEqual(0);

			var $a2 = $oobas.eq(1);
			expect($a2.find('.previews').length).toEqual(0);
		});
	});
});


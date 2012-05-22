/***************************************************************
*  Copyright notice
*
*  (c) 2011 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

Ext.ns('TQSeo.overview');

Ext.onReady(function(){
	Ext.QuickTips.init();
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	new TQSeo.overview.grid.init();
});

TQSeo.overview.grid = {

	highlightFilter: false,

	init: function() {
		// Init
		var me = this;
		var cellEditMode		= false;
		var fullCellHighlight	= true;

		switch( TQSeo.overview.conf.listType ) {
			case 'metadata':
				cellEditMode = true;
				break;

			case 'pagetitle':
				cellEditMode = true;
				fullCellHighlight = false;
				break;

			case 'pagetitlesim':
				fullCellHighlight = false;
				break;
		}




		/****************************************************
		 * grid storage
		 ****************************************************/
		var gridDs = new Ext.data.Store({
	 		storeId: 'TQSeoOverviewRecordsStore',
			autoLoad: true,
			remoteSort: true,
			url: TQSeo.overview.conf.ajaxController + '&cmd=getList',
			reader: new Ext.data.JsonReader({
					totalProperty: 'results',
					root: 'rows'
				},[
					{name: 'uid', type: 'int'},
					{name: 'title', type: 'string'},
					// Meta
					{name: 'keywords', type: 'string'},
					{name: 'description', type: 'string'},
					{name: 'abstract', type: 'string'},
					{name: 'author', type: 'string'},
					{name: 'author_email', type: 'string'},

					// Pagetitle
					{name: 'tx_tqseo_pagetitle', type: 'string'},
					{name: 'tx_tqseo_pagetitle_prefix', type: 'string'},
					{name: 'tx_tqseo_pagetitle_suffix', type: 'string'},

					// Pagetitle sim
					{name: 'title_simulated', type: 'string'},

					{name: '_depth', type: 'int'}
				]
			),
			sortInfo: {
				field	 : 'uid',
				direction: 'DESC'
			},
			baseParams: {
				pid						: Ext.encode( TQSeo.overview.conf.pid ),
				pagerStart				: Ext.encode( 0 ),
				pagingSize				: Ext.encode( TQSeo.overview.conf.pagingSize ),
				sortField				: Ext.encode( TQSeo.overview.conf.sortField ),
				depth					: Ext.encode( TQSeo.overview.conf.depth ),
				listType				: Ext.encode( TQSeo.overview.conf.listType )
			},
			listeners: {
				beforeload: function() {
					this.baseParams.pagingSize	= Ext.encode( TQSeo.overview.conf.pagingSize );
					this.baseParams.depth		= Ext.encode( TQSeo.overview.conf.depth );
					this.removeAll();
				}
			}
		});

		var function_filter = function(ob) {
			TQSeo.overview.conf.criteriaFulltext = Ext.getCmp('searchFulltext').getValue();
			grid.getView().refresh();
		}

 		var function_reload = function(ob) {
			filterAction(ob, 'getItems');
		};

 		var filterAction = function(ob, cmd) {
 			TQSeo.overview.conf.depth = Ext.getCmp('listDepth').getValue();

 			gridDs.reload();
		};


		var fieldRenderer = function(value) {
			return fieldRendererCallback(value, value, 23);
		}

		var fieldRendererRaw = function(value) {
			return fieldRendererCallback(value, value, false);
		}

		var fieldRendererCallback = function(value, qtip, maxLength) {
			var classes = '';

			if( cellEditMode ) {
				classes += 'tqseo-cell-editable ';
			}

			if(fullCellHighlight && !Ext.isEmpty(TQSeo.overview.conf.criteriaFulltext)) {
				if( TQSeo.highlightTextExists(value, TQSeo.overview.conf.criteriaFulltext) ) {
					classes += 'tqseo-cell-highlight ';
				}
			}

			if( maxLength && value != '' && value.length >= maxLength ) {
				value = value.substring(0, (maxLength-3) )+'...';
			}
			value = String.escape(value);
			value = value.replace(/ /g, "&nbsp;");


			var qtip = String.escape(qtip);
			qtip = qtip.replace(/\n/g, "<br />");

			return '<div class="'+classes+'" ext:qtip="' + qtip +'">' + value + '&nbsp;<div class="icon"></div></div>';
		}

		var columnModel = [{
			id       : 'uid',
			header   : TQSeo.overview.conf.lang.page_uid,
			width    : 'auto',
			sortable : false,
			dataIndex: 'uid',
			hidden	 : true
		}, {
			id       : 'title',
			header   : TQSeo.overview.conf.lang.page_title,
			width    : 200,
			sortable : false,
			dataIndex: 'title',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				var qtip = value;

				if( record.data._depth ) {
					value = new Array(record.data._depth).join('    ') + value;
				}

				return fieldRendererCallback(value, qtip, false);
			},
			tqSeoEditor	: {
				fieldType: 'textfield',
				fieldMinLength: 3
			}
		}];

		switch( TQSeo.overview.conf.listType ) {
			case 'metadata':
				columnModel.push({
					id			: 'keywords',
					header		: TQSeo.overview.conf.lang.page_keywords,
					width		: 'auto',
					sortable	: false,
					dataIndex	: 'keywords',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textarea'
					}
				},{
					id			: 'description',
					header		: TQSeo.overview.conf.lang.page_description,
					width		: 'auto',
					sortable	: false,
					dataIndex	: 'description',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textarea'
					}
				},{
					id			: 'abstract',
					header		: TQSeo.overview.conf.lang.page_abstract,
					width		: 'auto',
					sortable	: false,
					dataIndex	: 'abstract',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textarea'
					}
				},{
					id			: 'author',
					header		: TQSeo.overview.conf.lang.page_author,
					width		: 'auto',
					sortable	: false,
					dataIndex	: 'author',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textfield'
					}
				},{
					id			: 'author_email',
					header		: TQSeo.overview.conf.lang.page_author_email,
					width		: 'auto',
					sortable	: false,
					dataIndex	: 'author_email',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textfield',
						fieldVType: 'email'
					}
				});

				break;

			case 'pagetitle':
				columnModel.push({
					id       : 'tx_tqseo_pagetitle',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle,
					width    : 'auto',
					sortable : false,
					dataIndex: 'tx_tqseo_pagetitle',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textfield'
					}
				},{
					id       : 'tx_tqseo_pagetitle_prefix',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle_prefix,
					width    : 'auto',
					sortable : false,
					dataIndex: 'tx_tqseo_pagetitle_prefix',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textfield'
					}
				},{
					id       : 'tx_tqseo_pagetitle_suffix',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle_suffix,
					width    : 'auto',
					sortable : false,
					dataIndex: 'tx_tqseo_pagetitle_suffix',
					renderer	: fieldRenderer,
					tqSeoEditor	: {
						fieldType: 'textfield'
					}
				});
				break;

			case 'pagetitlesim':
				columnModel.push({
					id       : 'title_simulated',
					header   : TQSeo.overview.conf.lang.page_title_simulated,
					width    : 400,
					sortable : false,
					dataIndex: 'title_simulated',
					renderer : fieldRendererRaw
				});


				break;
		}



		/****************************************************
		 * grid panel
		 ****************************************************/
		var grid = new Ext.grid.GridPanel({
			layout: 'fit',
			renderTo: TQSeo.overview.conf.renderTo,
			store: gridDs,
			loadMask: true,
			plugins: [new Ext.ux.plugin.FitToParent()],
			columns: columnModel,
			stripeRows: true,
			height: 350,
			width: '98%',
			frame: true,
			border: true,
			title: TQSeo.overview.conf.lang.title,
			viewConfig: {
				forceFit: true,
				listeners: {
					refresh: function(view) {
						if (!fullCellHighlight && !Ext.isEmpty(TQSeo.overview.conf.criteriaFulltext)) {
							view.el.select('.x-grid3-body .x-grid3-cell').each(function(el) {
								TQSeo.highlightText(el.dom, TQSeo.overview.conf.criteriaFulltext);
							});
						}
					}
				}
			},
			tbar: [
				TQSeo.overview.conf.lang.labelSearchFulltext,
				{
					xtype: 'textfield',
					id: 'searchFulltext',
					fieldLabel: TQSeo.overview.conf.lang.labelSearchFulltext,
					emptyText : TQSeo.overview.conf.lang.emptySearchFulltext,
					listeners: {
						specialkey: function(f,e){
							if (e.getKey() == e.ENTER) {
								function_filter(this);
							}
						}
					}
				},
				{xtype: 'tbspacer', width: 10}
			],
			bbar: [
				TQSeo.overview.conf.lang.labelDepth,
		    	{
		    		xtype: 'combo',
		    		id: 'listDepth',
					listeners: {
						select: function(f,e){
							function_reload(this);
						}
					},
					forceSelection: true,
					editable: false,
					mode: 'local',
					triggerAction: 'all',
					value : TQSeo.overview.conf.depth,
					store: new Ext.data.ArrayStore({
						id: 0,
						fields: [
							'id',
							'label'
						],
						data: [
							[1, 1],
							[2, 2],
							[3, 3]
						]
					}),
					valueField: 'id',
					displayField: 'label'
		    	},
			]
		});

		var editWindow = false;

		if( cellEditMode ) {
			grid.addClass('tqseo-grid-editable');

			grid.on('cellclick', function(grid, rowIndex, colIndex, e) {
				var record = grid.getStore().getAt(rowIndex);
				var fieldName = grid.getColumnModel().getDataIndex(colIndex);
				var col = grid.getColumnModel().getColumnById(fieldName);
				var data = record.get(fieldName);

				var title = record.get('title');


				if( col.tqSeoEditor ) {

					var fieldWidth		= 375;
					var fieldHeight		= null;
					var fieldVType		= null;
					var fieldMinLength	= 0;

					if(col.tqSeoEditor.fieldType == 'textarea') {
						fieldHeight = 150;
					}

					if(col.tqSeoEditor.fieldVType) {
						fieldVType = col.tqSeoEditor.fieldVType;
					}

					if(col.tqSeoEditor.fieldMinLength) {
						fieldMinLength = col.tqSeoEditor.fieldMinLength;
					}

					var editWindow = new Ext.Window({
						xtype: 'form',
						width: 400,
						height: 'auto',
						modal: true,
						title: title+': '+col.header,
						items: [
							{
								xtype : col.tqSeoEditor.fieldType,
								itemId: 'form-field',
								value:  data,
								width:  fieldWidth,
								height: fieldHeight,
								vtype:  fieldVType,
								minLength: fieldMinLength
							}
						],
						buttons: [
							{
								text: 'Save',
								handler: function(cmp, e) {
									grid.loadMask.show();

									var pid = record.get('uid');
									var fieldValue = editWindow.getComponent('form-field').getValue();

									var callbackFinish = function(response) {
										var response = Ext.decode(response.responseText);

										if( response && response.error ) {
											TYPO3.Flashmessage.display(TYPO3.Severity.error, 'TODO', response.error);
										}

										grid.getStore().load();
									};

									Ext.Ajax.request({
										url: TQSeo.overview.conf.ajaxController + '&cmd=updatePageField',
										params: {
											pid:   Ext.encode(pid),
											field: Ext.encode(fieldName),
											value: Ext.encode(fieldValue)
										},
										success: callbackFinish,
										failure: callbackFinish
									});

									editWindow.destroy();
								}
							},{
								text: 'Cancel',
								handler: function(cmp, e) {
									editWindow.destroy();
								}
							}
						]
					});
					editWindow.show();

				}


			});
		}

	}

};
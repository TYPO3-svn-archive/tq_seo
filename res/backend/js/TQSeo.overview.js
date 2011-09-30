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

	init: function() {
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
					{name: 'email', type: 'string'},

					// Pagetitle
					{name: 'tx_tqseo_pagetitle', type: 'string'},
					{name: 'tx_tqseo_pagetitle_prefix', type: 'string'},
					{name: 'tx_tqseo_pagetitle_suffix', type: 'string'},

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

 		var function_reload = function(ob) {
			filterAction(ob, 'getItems');
		};

 		var filterAction = function(ob, cmd) {
 			TQSeo.overview.conf.depth = Ext.getCmp('listDepth').getValue();

 			gridDs.reload();
		};


		var columnModel = [{
			id       : 'uid',
			header   : TQSeo.overview.conf.lang.page_uid,
			width    : 'auto',
			sortable : true,
			dataIndex: 'uid',
			hidden	 : true
		}, {
			id       : 'title',
			header   : TQSeo.overview.conf.lang.page_title,
			width    : 200,
			sortable : true,
			dataIndex: 'title',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				var ret = value;

				if( record.data._depth ) {
					ret = new Array(record.data._depth).join('&nbsp;&nbsp;&nbsp;&nbsp;') + ret;
				}

				return ret;
			}
		}];

		switch( TQSeo.overview.conf.listType ) {
			case 'metadata':
				columnModel.push({
					id       : 'keywords',
					header   : TQSeo.overview.conf.lang.page_keywords,
					width    : 'auto',
					sortable : true,
					dataIndex: 'keywords'
				},{
					id       : 'description',
					header   : TQSeo.overview.conf.lang.page_description,
					width    : 'auto',
					sortable : true,
					dataIndex: 'description'

				},{
					id       : 'abstract',
					header   : TQSeo.overview.conf.lang.page_abstract,
					width    : 'auto',
					sortable : true,
					dataIndex: 'abstract'

				},{
					id       : 'author',
					header   : TQSeo.overview.conf.lang.page_author,
					width    : 'auto',
					sortable : true,
					dataIndex: 'author'

				},{
					id       : 'author_email',
					header   : TQSeo.overview.conf.lang.page_author_email,
					width    : 'auto',
					sortable : true,
					dataIndex: 'author_email'
				});

				break;

			case 'pagetitle':
				columnModel.push({
					id       : 'tx_tqseo_pagetitle',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle,
					width    : 'auto',
					sortable : true,
					dataIndex: 'tx_tqseo_pagetitle'
				},{
					id       : 'tx_tqseo_pagetitle_prefix',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle_prefix,
					width    : 'auto',
					sortable : true,
					dataIndex: 'tx_tqseo_pagetitle_prefix'

				},{
					id       : 'tx_tqseo_pagetitle_suffix',
					header   : TQSeo.overview.conf.lang.page_tx_tqseo_pagetitle_suffix,
					width    : 'auto',
					sortable : true,
					dataIndex: 'tx_tqseo_pagetitle_suffix'
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
			tbar: [
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

	}

};
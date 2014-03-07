/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
Ext.ns('TQSeo');

TQSeo.metaeditor  = Ext.extend(Ext.Window, {
    xtype: 'form',
    width: '90%',
    height: '90%',
    modal: true,

    initComponent : function() {
        var window = this;

        this.items = [{
            xtype:'tabpanel',
            activeItem:0,
            //autoScroll: true,
            enableTabScroll : true,
            //autoHeight:true,
            height:340,
            //collapseMode: "mini",
            items:[
                window.initTabOpenGraph()
            ]
        }];

        this.buttons = [{
            text: 'Cancel',
            handler: function(cmp, e) {
                window.onClose(false);
                window.destroy();
            }
        },{
            text: 'Save',
            handler: function(cmp, e) {
                window.saveMeta(function() {
                    window.onClose(true);
                    window.destroy();
                });
            }
        }];


        // call parent
        TQSeo.metaeditor.superclass.initComponent.call(this);

        this.addListener("show", function() {
            var el = window.getEl();
            el.mask();

            window.loadMeta(function() {
                el.unmask();
            });
        });


    },

    onClose: function(reload) {
        // placeholder
    },

    loadMeta: function(callback) {
        var me = this;


        // Process data from database/ajax call
        var callbackSuccess = function(response) {
            var responseJson =  Ext.util.JSON.decode(response.responseText);

            for( var index in responseJson ) {
                var value = responseJson[index];

                // Inject data into form
                var formField = me.find("name", index);
                if( formField.length == 1 ) {
                    formField = formField[0];
                    formField.setValue(value);
                }
            }

            callback();
        }

        var callbackFailure = function() {
            // TODO
        }

        Ext.Ajax.request({
            url: TQSeo.overview.conf.ajaxController + '&cmd=loadAdvMetaTags',
            params: {
                pid             : Ext.encode(me.pid),
                sysLanguage     : Ext.encode( TQSeo.overview.conf.sysLanguage ),
                mode            : Ext.encode( TQSeo.overview.conf.listType ),
                sessionToken    : Ext.encode( TQSeo.overview.conf.sessionToken )
            },
            success: callbackSuccess,
            failure: callbackFailure
        });
    },

    saveMeta: function(callbackSuccess) {
        var me = this;

        var metaTagList = {};

        var formOpenGraph = this.find("name", "form-opengraph");
        if( formOpenGraph.length = 1 ) {
            formOpenGraph = formOpenGraph[0];

            formOpenGraph.items.each(function(formField) {
                var formFieldName  = formField.getName();
                var formFieldValue = formField.getValue();

                metaTagList[formFieldName] = formFieldValue;
            });
        }

        var callbackFailure = function() {
            // TODO: failure function
        }

        Ext.Ajax.request({
            url: TQSeo.overview.conf.ajaxController + '&cmd=updateAdvMetaTags',
            params: {
                pid             : Ext.encode(me.pid),
                metaTags        : Ext.encode(metaTagList),
                sysLanguage     : Ext.encode( TQSeo.overview.conf.sysLanguage ),
                mode            : Ext.encode( TQSeo.overview.conf.listType ),
                sessionToken    : Ext.encode( TQSeo.overview.conf.sessionToken )
            },
            success: callbackSuccess,
            failure: callbackFailure
        });
    },

    initTabOpenGraph: function() {

        return {
            xtype: "panel",
            name: "form-opengraph",
            title: 'OpenGraph',
            layout: {
                type: 'form'
            },
            padding: 10,
            items: [{
                xtype: "textfield",
                fieldLabel: 'og:title',
                name: 'og:title'
            },{
                xtype: 'combo',
                fieldLabel: 'og:type',
                name: 'og:type',
                listeners: {
                    select: function(f,e){
                        // TODO: add dynamic field handling
                    }
                },
                forceSelection: true,
                editable: false,
                mode: 'local',
                triggerAction: 'all',
                value : "",
                store: new Ext.data.ArrayStore({
                    id: 0,
                    fields: [
                        'id',
                        'label'
                    ],
                    data: [
                        ["", "---"],
                        ["article", "article"],
                        ["book", "book"],
                        ["profile", "profile"],
                        ["website", "website"],

                        ["music.song", "music.song"],
                        ["music.album", "music.album"],
                        ["music.playlist", "music.playlist"],
                        ["music.radio_station", "music.radio_station"],

                        ["video.movie", "video.movie"],
                        ["video.episode", "video.episode"],
                        ["video.tv_show", "video.tv_show"],
                        ["video.other", "video.other"],
                    ]
                }),
                valueField: 'id',
                displayField: 'label'
            }, {
                xtype: "textfield",
                fieldLabel: 'og:image',
                name: 'og:image'
            }, {
                xtype: "textfield",
                fieldLabel: 'og:description',
                name: 'og:description'
            }]
        };
    }

});

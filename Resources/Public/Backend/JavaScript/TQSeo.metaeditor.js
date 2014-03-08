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

        this.title = TQSeo.overview.conf.lang.metaeditor_title;

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
            text: TQSeo.overview.conf.lang.button_cancel,
            handler: function(cmp, e) {
                window.onClose(false);
                window.destroy();
            }
        },{
            text: TQSeo.overview.conf.lang.button_save,
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

            // auto enable fields
            me.onChangeOgType();

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
                    if( formField.isVisible() ) {
                    var formFieldName  = formField.getName();
                    var formFieldValue = formField.getValue();

                    metaTagList[formFieldName] = formFieldValue;
                }
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

    onChangeOgType: function() {
        var formOpenGraph = this.find("name", "form-opengraph")[0];
        var typeField = formOpenGraph.find("name", "og:type")[0];

        // Get current type
        var ogType           = typeField.getValue();

        // Default types
        var ogTypeDefault    = "og:general";
        var ogTypeMain       = "og:general";
        var ogTypeMainAndSub = "og:general";

        // Lookup current selected type
        var ogTypeMatch = ogType.match(/^([^:]+):?([^:]+)?/);
        if( ogTypeMatch ) {
            ogTypeMain = 'og:'+ogTypeMatch[1];

            if( ogTypeMatch[2] ) {
                ogTypeMainAndSub  = 'og:'+ogTypeMatch[1]+'-'+ogTypeMatch[2];
            }
        }

        // dynamic dis- and enable form elements        
        formOpenGraph.items.each(function(formField) {
            if( formField.tqSeoFieldCat ) {
                if( TQSeo.inList(formField.tqSeoFieldCat, ogTypeDefault)
                    || TQSeo.inList(formField.tqSeoFieldCat, ogTypeMain)
                    || TQSeo.inList(formField.tqSeoFieldCat, ogTypeMainAndSub) ) {
                    formField.show();
                } else {
                    formField.hide();
                }
            }
        });

    },

    initTabOpenGraph: function() {
        var me = this;

        return {
            xtype: "panel",
            name: "form-opengraph",
            title: TQSeo.overview.conf.lang.metaeditor_tab_opengraph,
            layout: {
                type: 'form'
            },
            padding: 10,
            items: [{
                xtype: "textfield",
                fieldLabel: 'og:title',
                name: 'og:title',
                width: 375,
                tqSeoFieldCat: 'og:general'
            },{
                xtype: 'combo',
                fieldLabel: 'og:type',
                name: 'og:type',
                listeners: {
                    select: function(f,e){
                        // dynamic field handling
                        me.onChangeOgType();
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
                displayField: 'label',
                width: 375,
                tqSeoFieldCat: 'og:general'
            }, {
                xtype: "textfield",
                fieldLabel: 'og:image',
                name: 'og:image',
                width: 375,
                tqSeoFieldCat: 'og:general'
            }, {
                xtype: "textfield",
                fieldLabel: 'og:description',
                name: 'og:description',
                width: 375,
                tqSeoFieldCat: 'og:general'
            },

            // ########################
            // OG: Music General
            // ########################

            // ########################
            // OG: Music Song
            // ########################
            {
                xtype: "textfield",
                fieldLabel: 'og:music:duration',
                name: 'og:music:duration',
                width: 375,
                tqSeoFieldCat: 'og:music:song'
            }, {
                xtype: "textfield",
                fieldLabel: 'og:music:album',
                name: 'og:music:duration',
                width: 375,
                tqSeoFieldCat: 'og:music:song'
            },


            // ########################
            // OG: Music Radio
            // ########################
            {
                xtype: "textfield",
                fieldLabel: 'og:music:creator',
                name: 'og:music:creator',
                width: 375,
                tqSeoFieldCat: 'og:music:radio_station'
            }

            ]
        };
    }

});

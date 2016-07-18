//
// This app will handle the listing, additions and deletions of workshops.  These are associated business.
//
function ciniki_workshops_main() {
    //
    // Panels
    //
    this.regFlags = {
        '1':{'name':'Track Registrations'},
        '2':{'name':'Online Registrations'},
        };
    this.init = function() {
        //
        // workshops panel
        //
        this.menu = new M.panel('Workshops',
            'ciniki_workshops_main', 'menu',
            'mc', 'medium', 'sectioned', 'ciniki.workshops.main.menu');
        this.menu.sections = {
            'upcoming':{'label':'Upcoming Workshops', 'type':'simplegrid', 'num_cols':2,
                'headerValues':null,
                'cellClasses':['multiline center nobreak', 'multiline'],
                'noData':'No workshops added',
                'addTxt':'Add Workshop',
                'addFn':'M.ciniki_workshops_main.showEdit(\'M.ciniki_workshops_main.showMenu();\',0);',
                },
            'past':{'label':'Past Workshops', 'type':'simplegrid', 'num_cols':2,
                'headerValues':null,
                'cellClasses':['multiline center nobreak', 'multiline'],
                'noData':'No workshops',
                },
            };
        this.menu.sectionData = function(s) { return this.data[s]; }
        this.menu.noData = function(s) { return this.sections[s].noData; }
        this.menu.cellValue = function(s, i, j, d) {
            if( j == 0 ) {
                if( d.workshop.end_date != '' && d.workshop.end_date != null ) {
                    return '<span class="maintext">' + d.workshop.start_date.replace(' ', '&nbsp;') + '</span>'
                        + '<span class="subtext">' + d.workshop.end_date + '</span>';
                }
                if( d.workshop.start_date == null || d.workshop.start_date == '' ) {
                    return '<span class="maintext">???</span><span class="subtext">&nbsp;</span>';
                }
                return '<span class="maintext">' + d.workshop.start_date.replace(' ', '&nbsp;') + '</span><span class="subtext">&nbsp;</span>';
            }
            if( j == 1 ) {
//              var reg = '';
//              if( d.workshop.tickets_sold != null && d.workshop.num_tickets != null ) {
//                  reg = ' [' + d.workshop.tickets_sold + '/' + d.workshop.num_tickets + ']';
//              }
                return '<span class="maintext">' + d.workshop.name + '</span>'
                    + '<span class="subtext singleline"> </span>';
            }
        };
        this.menu.rowFn = function(s, i, d) {
            return 'M.ciniki_workshops_main.showWorkshop(\'M.ciniki_workshops_main.showMenu();\',\'' + d.workshop.id + '\');';
        };
        this.menu.addButton('add', 'Add', 'M.ciniki_workshops_main.showEdit(\'M.ciniki_workshops_main.showMenu();\',0);');
        this.menu.addClose('Back');

        //
        // The workshop panel 
        //
        this.workshop = new M.panel('Workshop',
            'ciniki_workshops_main', 'workshop',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.workshops.main.workshop');
        this.workshop.data = {};
        this.workshop.workshop_id = 0;
        this.workshop.sections = {
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
                }},
            'info':{'label':'', 'aside':'yes', 'list':{
                'name':{'label':'Name'},
                'start_date':{'label':'Start'},
                'end_date':{'label':'End'},
                'times':{'label':'Hours'},
                'url':{'label':'Website'},
                'webcollections_text':{'label':'Web Collections'},
                }},
            '_registrations':{'label':'', 'aside':'yes', 'hidelabel':'yes', 'visible':'no', 'list':{
                'registrations':{'label':'Tickets'},
                }},
            'description':{'label':'Synopsis', 'type':'htmlcontent'},
            'long_description':{'label':'Description', 'type':'htmlcontent'},
            'files':{'label':'Files', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'cellClasses':['multiline'],
                'noData':'No workshop files',
                'addTxt':'Add File',
                'addFn':'M.startApp(\'ciniki.workshops.files\',null,\'M.ciniki_workshops_main.showWorkshop();\',\'mc\',{\'workshop_id\':M.ciniki_workshops_main.workshop.workshop_id,\'add\':\'yes\'});',
            },
            'images':{'label':'Gallery', 'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Additional Image',
                'addFn':'M.startApp(\'ciniki.workshops.images\',null,\'M.ciniki_workshops_main.showWorkshop();\',\'mc\',{\'workshop_id\':M.ciniki_workshops_main.workshop.workshop_id,\'add\':\'yes\'});',
                },
            '_buttons':{'label':'', 'buttons':{
                'edit':{'label':'Edit', 'fn':'M.ciniki_workshops_main.showEdit(\'M.ciniki_workshops_main.showWorkshop();\',M.ciniki_workshops_main.workshop.workshop_id);'},
                }},
        };
        this.workshop.addDropImage = function(iid) {
            var rsp = M.api.getJSON('ciniki.workshops.imageAdd',
                {'business_id':M.curBusinessID, 'image_id':iid, 'workshop_id':M.ciniki_workshops_main.workshop.workshop_id});
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            return true;
        };
        this.workshop.addDropImageRefresh = function() {
            if( M.ciniki_workshops_main.workshop.workshop_id > 0 ) {
                var rsp = M.api.getJSONCb('ciniki.workshops.workshopGet', {'business_id':M.curBusinessID, 
                    'workshop_id':M.ciniki_workshops_main.workshop.workshop_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_workshops_main.workshop.data.images = rsp.workshop.images;
                        M.ciniki_workshops_main.workshop.refreshSection('images');
                    });
            }
        };
        this.workshop.sectionData = function(s) {
            if( s == 'description' || s == 'long_description' ) { return this.data[s].replace(/\n/g, '<br/>'); }
            if( s == 'info' || s == '_registrations' ) { return this.sections[s].list; }
            return this.data[s];
        };
        this.workshop.listLabel = function(s, i, d) { return d.label; };
        this.workshop.listValue = function(s, i, d) {
            if( i == 'registrations' ) {
                return this.data['tickets_sold'] + ' of ' + this.data['num_tickets'] + ' sold';
            }
            if( i == 'url' && this.data[i] != '' ) {
                return '<a target="_blank" href="http://' + this.data[i] + '">' + this.data[i] + '</a>';
            }
            return this.data[i];
        };
        this.workshop.listFn = function(s, i, d) {
            if( i == 'registrations' ) {
                return 'M.startApp(\'ciniki.workshops.registrations\',null,\'M.ciniki_workshops_main.showWorkshop();\',\'mc\',{\'workshop_id\':\'' + M.ciniki_workshops_main.workshop.workshop_id + '\'});';
            }
            return null;
        };
        this.workshop.fieldValue = function(s, i, d) {
            return this.data[i];
        };
        this.workshop.cellValue = function(s, i, j, d) {
            if( s == 'files' && j == 0 ) { 
                return '<span class="maintext">' + d.file.name + '</span>';
            }
        };
        this.workshop.rowFn = function(s, i, d) {
            if( s == 'files' ) {
                return 'M.startApp(\'ciniki.workshops.files\',null,\'M.ciniki_workshops_main.showWorkshop();\',\'mc\',{\'file_id\':\'' + d.file.id + '\'});';
            }
        };
        this.workshop.thumbFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.workshops.images\',null,\'M.ciniki_workshops_main.showWorkshop();\',\'mc\',{\'workshop_image_id\':\'' + d.image.id + '\'});';
        };
        this.workshop.addButton('edit', 'Edit', 'M.ciniki_workshops_main.showEdit(\'M.ciniki_workshops_main.showWorkshop();\',M.ciniki_workshops_main.workshop.workshop_id);');
        this.workshop.addClose('Back');

        //
        // The panel for a site's menu
        //
        this.edit = new M.panel('Workshop',
            'ciniki_workshops_main', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.workshops.main.edit');
        this.edit.data = null;
        this.edit.workshop_id = 0;
        this.edit.sections = { 
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
            'general':{'label':'General', 'aside':'yes', 'fields':{
                'name':{'label':'Name', 'hint':'Workshops name', 'type':'text'},
                'url':{'label':'URL', 'hint':'Enter the http:// address for your workshops website', 'type':'text'},
                'start_date':{'label':'Start', 'type':'date'},
                'end_date':{'label':'End', 'type':'date'},
                'times':{'label':'Hours', 'type':'text'},
                }}, 
            '_webcollections':{'label':'Web Collections', 'aside':'yes', 'active':'no', 'fields':{
                'webcollections':{'label':'', 'hidelabel':'yes', 'type':'collection'},
                }},
            '_registrations':{'label':'Registrations', 'aside':'yes', 'visible':'no', 'fields':{
                'reg_flags':{'label':'Options', 'active':'no', 'type':'flags', 'joined':'no', 'flags':this.regFlags},
                'num_tickets':{'label':'Number of Tickets', 'active':'no', 'type':'text', 'size':'small'},
                }},
            '_description':{'label':'Synopsis', 'fields':{
                'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'small', 'type':'textarea'},
                }},
            '_long_description':{'label':'Description', 'fields':{
                'long_description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_workshops_main.saveWorkshop();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_workshops_main.removeWorkshop();'},
                }},
            };  
        this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.workshops.workshopHistory', 'args':{'business_id':M.curBusinessID, 
                'workshop_id':this.workshop_id, 'field':i}};
        }
        this.edit.addDropImage = function(iid) {
            M.ciniki_workshops_main.edit.setFieldValue('primary_image_id', iid, null, null);
            return true;
        };
        this.edit.deleteImage = function(fid) {
            this.setFieldValue(fid, 0, null, null);
            return true;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_workshops_main.saveWorkshop();');
        this.edit.addClose('Cancel');
    }

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }
        
        //
        // Check if web collections are enabled
        //
        if( M.curBusiness.modules['ciniki.web'] != null 
            && (M.curBusiness.modules['ciniki.web'].flags&0x08) ) {
            this.workshop.sections.info.list.webcollections_text.visible = 'yes';
            this.edit.sections._webcollections.active = 'yes';
        } else {
            this.workshop.sections.info.list.webcollections_text.visible = 'no';
            this.edit.sections._webcollections.active = 'no';
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_workshops_main', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        this.showMenu(cb);
    }

    this.showMenu = function(cb) {
        this.menu.data = {};
        var rsp = M.api.getJSONCb('ciniki.workshops.workshopList', 
            {'business_id':M.curBusinessID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_workshops_main.menu;
                p.data['upcoming'] = rsp.upcoming;
                p.data['past'] = rsp.past;
                p.refresh();
                p.show(cb);
            });
    };

    this.showWorkshop = function(cb, eid) {
        this.workshop.reset();
        if( eid != null ) {
            this.workshop.workshop_id = eid;
        }
        var rsp = M.api.getJSONCb('ciniki.workshops.workshopGet', {'business_id':M.curBusinessID, 
            'workshop_id':this.workshop.workshop_id, 'images':'yes', 
            'files':'yes', 'sponsors':'yes', 'webcollections':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_workshops_main.workshop;
                p.data = rsp.workshop;
                if( rsp.workshop.end_date != null && rsp.workshop.end_date != '' ) {
                    p.sections.info.list.end_date.visible = 'yes';
                } else {
                    p.sections.info.list.end_date.visible = 'no';
                }
                p.sections.info.list.times.visible=(rsp.workshop.times!=null&&rsp.workshop.times!='')?'yes':'no';
                if( rsp.workshop.url != null && rsp.workshop.url != '' ) {
                    p.sections.info.list.url.visible = 'yes';
                } else {
                    p.sections.info.list.url.visible = 'no';
                }
                if( (rsp.workshop.reg_flags&0x03) > 0 ) {
                    p.sections._registrations.visible = 'yes';
                } else {
                    p.sections._registrations.visible = 'no';
                }
                p.refresh();
                p.show(cb);
            });
    };

    this.showEdit = function(cb, eid) {
        this.edit.reset();
        if( eid != null ) {
            this.edit.workshop_id = eid;
        }

        if( (M.curBusiness.modules['ciniki.workshops'].flags&0x03) > 0 ) {
            this.edit.sections._registrations.visible = 'yes';
            this.edit.sections._registrations.fields.reg_flags.active = 'yes';
            this.edit.sections._registrations.fields.num_tickets.active = 'yes';
        } else {
            this.edit.sections._registrations.visible = 'no';
            this.edit.sections._registrations.fields.reg_flags.active = 'no';
            this.edit.sections._registrations.fields.num_tickets.active = 'no';
        }

        if( this.edit.workshop_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.workshops.workshopGet', {'business_id':M.curBusinessID, 
                'workshop_id':this.edit.workshop_id, 'webcollections':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_workshops_main.edit.data = rsp.workshop;
                    M.ciniki_workshops_main.edit.refresh();
                    M.ciniki_workshops_main.edit.show(cb);
                });
        } else if( this.edit.sections._webcollections.active == 'yes' ) {
            this.edit.reset();
            this.edit.data = {};
            // Get the list of collections
            M.api.getJSONCb('ciniki.web.collectionList', {'business_id':M.curBusinessID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_workshops_main.edit;
                p.data = {};
                if( rsp.collections != null ) {
                    p.data['_webcollections'] = rsp.collections;
                }
                p.refresh();
                p.show(cb);
            });
        } else {
            this.edit.data = {};
            this.edit.show(cb);
        }
    };

    this.saveWorkshop = function() {
        if( this.edit.workshop_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                var rsp = M.api.postJSONCb('ciniki.workshops.workshopUpdate', 
                    {'business_id':M.curBusinessID, 'workshop_id':M.ciniki_workshops_main.edit.workshop_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_workshops_main.edit.close();
                    });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeForm('yes');
            if( c != '' ) {
                var rsp = M.api.postJSONCb('ciniki.workshops.workshopAdd', 
                    {'business_id':M.curBusinessID}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        var wid = rsp.id;
                        if( rsp.id > 0 ) {
                            var cb = M.ciniki_workshops_main.edit.cb;
                            M.ciniki_workshops_main.edit.close();
                            M.ciniki_workshops_main.showWorkshop(cb,rsp.id);
                        } else {
                            M.ciniki_workshops_main.edit.close();
                        }
                    });
            } else {
                this.edit.close();
            }
        }
    };

    this.removeWorkshop = function() {
        if( confirm("Are you sure you want to remove '" + this.workshop.data.name + "' as an workshop ?") ) {
            var rsp = M.api.getJSONCb('ciniki.workshops.workshopDelete', 
                {'business_id':M.curBusinessID, 'workshop_id':M.ciniki_workshops_main.workshop.workshop_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_workshops_main.workshop.close();
                });
        }
    }
};

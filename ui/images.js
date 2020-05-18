//
// The app to add/edit workshops workshop images
//
function ciniki_workshops_images() {
    this.webFlags = {
        '1':{'name':'Hidden'},
        };
    this.init = function() {
        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Edit Image',
            'ciniki_workshops_images', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.workshops.images.edit');
        this.edit.default_data = {};
        this.edit.data = {};
        this.edit.workshop_id = 0;
        this.edit.sections = {
            '_image':{'label':'Photo', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
                'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
            }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_workshops_images.saveImage();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_workshops_images.deleteImage();'},
            }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.workshops.imageHistory', 'args':{'tnid':M.curTenantID, 
                'workshop_image_id':this.workshop_image_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_workshops_images.edit.setFieldValue('image_id', iid, null, null);
            return true;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_workshops_images.saveImage();');
        this.edit.addClose('Cancel');
    };

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_workshops_images', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.showEdit(cb, 0, args.workshop_id);
        } else if( args.workshop_image_id != null && args.workshop_image_id > 0 ) {
            this.showEdit(cb, args.workshop_image_id);
        }
        return false;
    }

    this.showEdit = function(cb, iid, eid) {
        if( iid != null ) {
            this.edit.workshop_image_id = iid;
        }
        if( eid != null ) {
            this.edit.workshop_id = eid;
        }
        if( this.edit.workshop_image_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.workshops.imageGet', 
                {'tnid':M.curTenantID, 'workshop_image_id':this.edit.workshop_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_workshops_images.edit.data = rsp.image;
                    M.ciniki_workshops_images.edit.refresh();
                    M.ciniki_workshops_images.edit.show(cb);
                });
        } else {
            this.edit.reset();
            this.edit.data = {};
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveImage = function() {
        if( this.edit.workshop_image_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.workshops.imageUpdate', 
                    {'tnid':M.curTenantID, 
                    'workshop_image_id':this.edit.workshop_image_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_workshops_images.edit.close();
                            }
                        });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeFormData('yes');
            var rsp = M.api.postJSONFormData('ciniki.workshops.imageAdd', 
                {'tnid':M.curTenantID, 'workshop_id':this.edit.workshop_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_workshops_images.edit.close();
                        }
                    });
        }
    };

    this.deleteImage = function() {
        M.confirm('Are you sure you want to delete this image?',null,function(,null,function() {
            var rsp = M.api.getJSONCb('ciniki.workshops.imageDelete', {'tnid':M.curTenantID, 
                'workshop_image_id':M.ciniki_workshops_images.edit.workshop_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_workshops_images.edit.close();
                });
        });
    };
}

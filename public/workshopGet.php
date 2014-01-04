<?php
//
// Description
// ===========
// This method will return all the information about an workshop.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business the workshop is attached to.
// workshop_id:		The ID of the workshop to get the details for.
// 
// Returns
// -------
// <workshop id="419" name="Workshop Name" url="http://myworkshop.com" 
//		description="Workshop description" start_date="July 18, 2012" end_date="July 19, 2012"
//		date_added="2012-07-19 03:08:05" last_updated="2012-07-19 03:08:05" />
//
function ciniki_workshops_workshopGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'workshop_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Workshop'), 
		'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
		'files'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Files'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
    $rc = ciniki_workshops_checkAccess($ciniki, $args['business_id'], 'ciniki.workshops.workshopGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	$strsql = "SELECT ciniki_workshops.id, "
		. "ciniki_workshops.name, "
		. "ciniki_workshops.permalink, "
		. "ciniki_workshops.url, "
		. "ciniki_workshops.description, "
		. "ciniki_workshops.num_tickets, "
		. "ciniki_workshops.reg_flags, "
		. "DATE_FORMAT(ciniki_workshops.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
		. "DATE_FORMAT(ciniki_workshops.end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS end_date, "
		. "ciniki_workshops.primary_image_id, "
		. "ciniki_workshops.long_description ";
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$strsql .= ", "
			. "ciniki_workshop_images.id AS img_id, "
			. "ciniki_workshop_images.name AS image_name, "
			. "ciniki_workshop_images.webflags AS image_webflags, "
			. "ciniki_workshop_images.image_id, "
			. "ciniki_workshop_images.description AS image_description, "
			. "ciniki_workshop_images.url AS image_url "
			. "";
	}
	$strsql .= "FROM ciniki_workshops ";
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$strsql .= "LEFT JOIN ciniki_workshop_images ON (ciniki_workshops.id = ciniki_workshop_images.workshop_id "
			. "AND ciniki_workshop_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	}
	$strsql .= "WHERE ciniki_workshops.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_workshops.id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.workshops', array(
			array('container'=>'workshops', 'fname'=>'id', 'name'=>'workshop',
				'fields'=>array('id', 'name', 'permalink', 'url', 'primary_image_id', 
					'start_date', 'end_date', 'description', 'num_tickets', 'reg_flags', 'long_description')),
			array('container'=>'images', 'fname'=>'img_id', 'name'=>'image',
				'fields'=>array('id'=>'img_id', 'name'=>'image_name', 'webflags'=>'image_webflags',
					'image_id', 'description'=>'image_description', 'url'=>'image_url')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['workshops']) || !isset($rc['workshops'][0]) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1460', 'msg'=>'Unable to find workshop'));
		}
		$workshop = $rc['workshops'][0]['workshop'];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
		if( isset($workshop['images']) ) {
			foreach($workshop['images'] as $img_id => $img) {
				if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
					$rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $img['image']['image_id'], 75);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$workshop['images'][$img_id]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
				}
			}
		}
	} else {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.workshops', array(
			array('container'=>'workshops', 'fname'=>'id', 'name'=>'workshop',
				'fields'=>array('id', 'name', 'permalink', 'url', 'primary_image_id', 
					'start_date', 'end_date', 'description', 'num_tickets', 'reg_flags', 'long_description')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['workshops']) || !isset($rc['workshops'][0]) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1461', 'msg'=>'Unable to find workshop'));
		}
		$workshop = $rc['workshops'][0]['workshop'];
	}
	
	//
	// Check how many registrations
	//
	if( ($workshop['reg_flags']&0x03) > 0 ) {
		$workshop['tickets_sold'] = 0;
		$strsql = "SELECT 'num_tickets', SUM(num_tickets) AS num_tickets "	
			. "FROM ciniki_workshop_registrations "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_workshop_registrations.workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
		$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.workshops', 'num');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['num']['num_tickets']) ) {
			$workshop['tickets_sold'] = $rc['num']['num_tickets'];
		}
	}

	//
	// Get any files if requested
	//
	if( isset($args['files']) && $args['files'] == 'yes' ) {
		$strsql = "SELECT id, name, extension, permalink "
			. "FROM ciniki_workshop_files "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_workshop_files.workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.workshops', array(
			array('container'=>'files', 'fname'=>'id', 'name'=>'file',
				'fields'=>array('id', 'name', 'extension', 'permalink')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['files']) ) {
			$workshop['files'] = $rc['files'];
		}
	}

	return array('stat'=>'ok', 'workshop'=>$workshop);
}
?>

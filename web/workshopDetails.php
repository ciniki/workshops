<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_workshops_web_workshopDetails($ciniki, $settings, $business_id, $permalink) {

	$strsql = "SELECT ciniki_workshops.id, "
		. "ciniki_workshops.name, "
		. "ciniki_workshops.permalink, "
		. "ciniki_workshops.url, "
		. "DATE_FORMAT(ciniki_workshops.start_date, '%a %b %c, %Y') AS start_date, "
		. "DATE_FORMAT(ciniki_workshops.end_date, '%a %b %c, %Y') AS end_date, "
		. "ciniki_workshops.description AS short_description, "
		. "ciniki_workshops.long_description, "
		. "ciniki_workshops.primary_image_id, "
		. "ciniki_workshop_images.image_id, "
		. "ciniki_workshop_images.name AS image_name, "
		. "ciniki_workshop_images.permalink AS image_permalink, "
		. "ciniki_workshop_images.description AS image_description, "
		. "ciniki_workshop_images.url AS image_url, "
		. "UNIX_TIMESTAMP(ciniki_workshop_images.last_updated) AS image_last_updated "
		. "FROM ciniki_workshops "
		. "LEFT JOIN ciniki_workshop_images ON ("
			. "ciniki_workshops.id = ciniki_workshop_images.workshop_id "
			. "AND (ciniki_workshop_images.webflags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_workshops.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_workshops.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'workshops', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'permalink', 'image_id'=>'primary_image_id', 
			'start_date', 'end_date', 
			'url', 'short_description', 'description'=>'long_description')),
		array('container'=>'images', 'fname'=>'image_id', 
			'fields'=>array('image_id', 'title'=>'image_name', 'permalink'=>'image_permalink',
				'description'=>'image_description', 'url'=>'image_url',
				'last_updated'=>'image_last_updated')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['workshops']) || count($rc['workshops']) < 1 ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1466', 'msg'=>"I'm sorry, but we can't find the workshop you requested."));
	}
	$workshop = array_pop($rc['workshops']);

	//
	// Check if any files are attached to the workshop
	//
	$strsql = "SELECT id, name, extension, permalink, description "
		. "FROM ciniki_workshop_files "
		. "WHERE ciniki_workshop_files.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_workshop_files.workshop_id = '" . ciniki_core_dbQuote($ciniki, $workshop['id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.workshops', array(
		array('container'=>'files', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'extension', 'permalink', 'description')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['files']) ) {
		$workshop['files'] = $rc['files'];
	}

	return array('stat'=>'ok', 'workshop'=>$workshop);
}
?>

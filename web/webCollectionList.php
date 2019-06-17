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
function ciniki_workshops_web_webCollectionList($ciniki, $settings, $tnid, $args) {

    $strsql = "SELECT ciniki_workshops.id, "
        . "ciniki_workshops.name, "
        . "ciniki_workshops.permalink, "
        . "ciniki_workshops.url, "
        . "IF(ciniki_workshops.long_description='', 'no', 'yes') AS isdetails, "
        . "DATE_FORMAT(ciniki_workshops.start_date, '%M') AS start_month, "
        . "DATE_FORMAT(ciniki_workshops.start_date, '%D') AS start_day, "
        . "DATE_FORMAT(ciniki_workshops.start_date, '%Y') AS start_year, "
        . "IF(ciniki_workshops.end_date = '0000-00-00', '', DATE_FORMAT(ciniki_workshops.end_date, '%M')) AS end_month, "
        . "IF(ciniki_workshops.end_date = '0000-00-00', '', DATE_FORMAT(ciniki_workshops.end_date, '%D')) AS end_day, "
        . "IF(ciniki_workshops.end_date = '0000-00-00', '', DATE_FORMAT(ciniki_workshops.end_date, '%Y')) AS end_year, "
        . "DATE_FORMAT(ciniki_workshops.start_date, '%a %b %e, %Y') AS start_date, "
        . "DATE_FORMAT(ciniki_workshops.end_date, '%a %b %e, %Y') AS end_date, "
        . "ciniki_workshops.times, "
        . "ciniki_workshops.description, "
        . "ciniki_workshops.primary_image_id, "
        . "COUNT(ciniki_workshop_images.id) AS num_images, "
        . "COUNT(ciniki_workshop_files.id) AS num_files "
        . "FROM ciniki_web_collection_objrefs "
        . "INNER JOIN ciniki_workshops ON ("
            . "ciniki_web_collection_objrefs.object_id = ciniki_workshops.id "
            . "AND ciniki_workshops.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
            if( isset($args['type']) && $args['type'] == 'past' ) {
                $strsql .= "AND ((ciniki_workshops.end_date > ciniki_workshops.start_date AND ciniki_workshops.end_date < DATE(NOW())) "
                        . "OR (ciniki_workshops.end_date <= ciniki_workshops.start_date AND ciniki_workshops.start_date < DATE(NOW())) "
                        . ") ";
            } else {
                $strsql .= "AND (ciniki_workshops.end_date >= DATE(NOW()) OR ciniki_workshops.start_date >= DATE(NOW())) ";
            }
    $strsql .= ") "
        . "LEFT JOIN ciniki_workshop_images ON (ciniki_workshops.id = ciniki_workshop_images.workshop_id "
            . "AND ciniki_workshop_images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_workshop_images.webflags&0x01) = 0 " // public images
            . ") "
        . "LEFT JOIN ciniki_workshop_files ON (ciniki_workshops.id = ciniki_workshop_files.workshop_id "
            . "AND ciniki_workshop_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_workshop_files.webflags&0x01) = 0 " // public files
            . ") "
        . "WHERE ciniki_web_collection_objrefs.collection_id = '" . ciniki_core_dbQuote($ciniki, $args['collection_id']) . "' "
        . "AND ciniki_web_collection_objrefs.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_web_collection_objrefs.object = 'ciniki.workshops.workshop' "
        . "";
    if( isset($args['type']) && $args['type'] == 'past' ) {
        $strsql .= "GROUP BY ciniki_workshops.id ";
        $strsql .= "ORDER BY ciniki_workshops.start_date DESC ";
    } else {
        $strsql .= "GROUP BY ciniki_workshops.id ";
        $strsql .= "ORDER BY ciniki_workshops.start_date ASC ";
    }
    if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 && is_int($args['limit']) ) {
        $strsql .= "LIMIT " . intval($args['limit']) . " ";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.workshops', array(
        array('container'=>'workshops', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'image_id'=>'primary_image_id', 'isdetails', 
                'start_month', 'start_day', 'start_year', 'end_month', 'end_day', 'end_year', 
                'start_date', 'end_date', 'times',
                'permalink', 'description', 'url', 'num_images', 'num_files')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['workshops']) ) {
        return array('stat'=>'ok', 'workshops'=>array());
    }
    return array('stat'=>'ok', 'workshops'=>$rc['workshops']);
}
?>

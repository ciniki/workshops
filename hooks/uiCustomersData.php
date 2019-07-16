<?php
//
// Description
// -----------
// This function will return the data for customer(s) to be displayed in the IFB display panel.
// The request might be for 1 individual, or multiple customer ids for a family.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get workshops for.
//
// Returns
// -------
//
function ciniki_workshops_hooks_uiCustomersData($ciniki, $tnid, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');

    //
    // Default response
    //
    $rsp = array('stat'=>'ok', 'tabs'=>array());

    //
    // Get the list of registrations for the with latest first
    //
    $sections['ciniki.workshops.registrations'] = array(
        'label' => 'Workshop Registrations',
        'type' => 'simplegrid', 
        'num_cols' => 3,
        'headerValues' => array('Name', 'Workshop', 'Date'),
        'cellClasses' => array('', '', ''),
        'noData' => 'No registrations',
//            'editApp' => array('app'=>'ciniki.workshops.sapos', 'args'=>array('registration_id'=>'d.id;', 'source'=>'\'\'')),
        'cellValues' => array(
            '0' => "d.display_name",
            '1' => "d.name",
            '2' => "d.start_date",
            ),
        'data' => array(),
        );
    $strsql = "SELECT regs.id, regs.customer_id, "
        . "IFNULL(customers.display_name, '') AS display_name, "
        . "workshops.name, "
        . "DATE_FORMAT(workshops.start_date, '%b %d, %Y') AS start_date "
        . "FROM ciniki_workshop_registrations AS regs "
        . "INNER JOIN ciniki_workshops AS workshops ON ("
            . "regs.workshop_id = workshops.id "
            . "AND workshops.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_customers AS customers ON ("
            . "regs.customer_id = customers.id "
            . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE regs.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($args['customer_id']) ) {
        $strsql .= "AND regs.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' ";
    } elseif( isset($args['customer_ids']) && count($args['customer_ids']) > 0 ) {
        $strsql .= "AND regs.customer_id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['customer_ids']) . ") ";
    } else {
        return array('stat'=>'ok');
    }
    $strsql .= "ORDER BY customers.display_name, workshops.start_date DESC, workshops.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.customers', array(
        array('container'=>'registrations', 'fname'=>'id', 
            'fields'=>array('id', 'customer_id', 'display_name', 'start_date', 'name'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $sections['ciniki.workshops.registrations']['data'] = isset($rc['registrations']) ? $rc['registrations'] : array();
    $rsp['tabs'][] = array(
        'id' => 'ciniki.workshops.registrations',
        'label' => 'Workshops',
        'sections' => $sections,
        );
    $sections = array();

    return $rsp;
}
?>

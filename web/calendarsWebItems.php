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
function ciniki_workshops_web_calendarsWebItems($ciniki, $settings, $tnid, $args) {

    if( !isset($args['ltz_start']) || !is_a($args['ltz_start'], 'DateTime') ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.workshops.65', 'msg'=>'Invalid start date'));
    }
    if( !isset($args['ltz_end']) || !is_a($args['ltz_end'], 'DateTime') ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.workshops.66', 'msg'=>'Invalid end date'));
    }

    $sdt = $args['ltz_start'];
    $edt = $args['ltz_end'];

    if( isset($ciniki['tenant']['module_pages']['ciniki.workshops']['base_url']) ) {
        $base_url = $ciniki['tenant']['module_pages']['ciniki.workshops']['base_url'];
    } elseif( isset($ciniki['tenant']['module_pages']['ciniki.workshops.upcoming']['base_url']) ) {
        $base_url = $ciniki['tenant']['module_pages']['ciniki.workshops.upcoming']['base_url'];
    } else {
        $base_url = '/workshops';
    }

    //
    // Check if this modules items are to be included in the calendar
    //
    if( isset($settings['ciniki-workshops-calendar-include']) && $settings['ciniki-workshops-calendar-include'] == 'no' ) {
        return array('stat'=>'ok');
    }

    //
    // Check if colours specified
    //
    $style = '';
    if( isset($settings['ciniki-workshops-colour-background']) && $settings['ciniki-workshops-colour-background'] != '' ) {
        $style .= ($style != '' ? ' ':'') . 'background: ' . $settings['ciniki-workshops-colour-background'] . ';';
    }
    if( isset($settings['ciniki-workshops-colour-border']) && $settings['ciniki-workshops-colour-border'] != '' ) {
        $style .= ($style != '' ? ' ':'') . ' border: 1px solid ' . $settings['ciniki-workshops-colour-border'] . ';';
    }
    if( isset($settings['ciniki-workshops-colour-font']) && $settings['ciniki-workshops-colour-font'] != '' ) {
        $style .= ($style != '' ? ' ':'') . ' color: ' . $settings['ciniki-workshops-colour-font'] . ';';
    }

    //
    // Setup the legend
    //
    if( isset($settings['ciniki-workshops-legend-title']) && $settings['ciniki-workshops-legend-title'] != '' ) {
        $legend = array(
            array('title'=>$settings['ciniki-workshops-legend-title'], 'style'=>$style)
            );
    } else {
        $legend = array();
    }

    //
    // FIXME: Add the ability to select the tags for an workshop and turn tags into classes
    //

    //
    // Get the list of workshops between the start and end date specified
    //
    $strsql = "SELECT ciniki_workshops.id, "
        . "ciniki_workshops.name, "
        . "ciniki_workshops.permalink, "
        . "ciniki_workshops.url, "
        . "IF(ciniki_workshops.long_description='', 'no', 'yes') AS isdetails, "
        . "DATE_FORMAT(ciniki_workshops.start_date, '%Y-%m-%d') AS start_date, "
        . "IF(ciniki_workshops.end_date < start_date, '', DATE_FORMAT(ciniki_workshops.end_date, '%Y-%m-%d')) AS end_date, "
        . "ciniki_workshops.times, "
        . "ciniki_workshops.description, "
        . "ciniki_workshops.primary_image_id "
        . "FROM ciniki_workshops "
        . "WHERE ciniki_workshops.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        // Workshop has to start or end between the dates for the calendar
        . "AND (("
            . "ciniki_workshops.start_date >= '" . ciniki_core_dbQuote($ciniki, $sdt->format('Y-m-d')) . "' "
            . "AND ciniki_workshops.start_date <= '" . ciniki_core_dbQuote($ciniki, $edt->format('Y-m-d')) . "' "
            . ") "
            . "OR ("
            . "ciniki_workshops.end_date >= '" . ciniki_core_dbQuote($ciniki, $sdt->format('Y-m-d')) . "' "
            . "AND ciniki_workshops.end_date <= '" . ciniki_core_dbQuote($ciniki, $edt->format('Y-m-d')) . "' "
            . ")) "
        . "ORDER BY ciniki_workshops.start_date DESC, ciniki_workshops.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.workshops', array(
        array('container'=>'workshops', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'title'=>'name', 'image_id'=>'primary_image_id', 'isdetails', 
                'start_date', 'end_date', 'permalink', 'times', 'description', 'url')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $prefix = '';
    if( isset($settings['ciniki-workshops-prefix']) ) {
        $prefix = $settings['ciniki-workshops-prefix'];
    }

    $items = array();
    if( isset($rc['workshops']) ) {
        foreach($rc['workshops'] as $workshop) {
            $item = array(
                'title'=>$prefix . $workshop['title'],
                'time_text'=>'',
                'style'=>$style,
                'url'=>$base_url . '/' . $workshop['permalink'],
                'classes'=>array('workshops'),
                );
            if( isset($settings['ciniki-workshops-display-times']) && $settings['ciniki-workshops-display-times'] == 'yes' ) {
                $item['time_text'] = $workshop['times'];
            }
            if( $workshop['end_date'] != '' && $workshop['start_date'] != $workshop['end_date'] ) {
                //
                // Add an item to the items list for each date of the workshop
                //
                $dt = new DateTime($workshop['start_date'], $sdt->getTimezone());
                $c = 0;
                do {
                    if( $c > 365 ) {
                        error_log("ERR: runaway workshop dates " . $workshop['id']);
                        break;
                    }
                    $cur_date = $dt->format('Y-m-d');
                    if( !isset($items[$cur_date]) ) {
                        $items[$cur_date]['items'] = array();
                    }
                    $items[$cur_date]['items'][] = $item;

                    $dt->add(new DateInterval('P1D'));
                    $c++;
                } while( $cur_date != $workshop['end_date']);
            } else {
                if( !isset($items[$workshop['start_date']]) ) {
                    $items[$workshop['start_date']]['items'] = array();
                }
                $items[$workshop['start_date']]['items'][] = $item;
            }
        }
    }

    return array('stat'=>'ok', 'items'=>$items, 'legend'=>$legend);
}
?>

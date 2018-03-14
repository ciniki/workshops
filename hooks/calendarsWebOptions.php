<?php
//
// Description
// -----------
// This function will return the calendar options for the this module.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get exhibitions for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_workshops_hooks_calendarsWebOptions(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.workshops']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.workshops.67', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    $settings = $args['settings'];

    $options = array();
    $options[] = array(
        'label'=>'Include Workshops',
        'setting'=>'ciniki-workshops-calendar-include',
        'type'=>'toggle',
        'value'=>(isset($settings['ciniki-workshops-calendar-include'])?$settings['ciniki-workshops-calendar-include']:'yes'),
        'toggles'=>array(
            array('value'=>'no', 'label'=>'No'),
            array('value'=>'yes', 'label'=>'Yes'),
            ),
        );
    $options[] = array(
        'label'=>'Workshop Title Prefix',
        'setting'=>'ciniki-workshops-prefix',
        'type'=>'text',
        'value'=>(isset($settings['ciniki-workshops-prefix'])?$settings['ciniki-workshops-prefix']:''),
        );
    $options[] = array(
        'label'=>'Workshop Legend Name',
        'setting'=>'ciniki-workshops-legend-title',
        'type'=>'text',
        'value'=>(isset($settings['ciniki-workshops-legend-title'])?$settings['ciniki-workshops-legend-title']:''),
        );
    $options[] = array(
        'label'=>'Workshops Display Times',
        'setting'=>'ciniki-workshops-display-times',
        'type'=>'toggle',
        'value'=>(isset($settings['ciniki-workshops-display-times'])?$settings['ciniki-workshops-display-times']:'no'),
        'toggles'=>array(
            array('value'=>'no', 'label'=>'No'),
            array('value'=>'yes', 'label'=>'Yes'),
            ),
        );
    $options[] = array(
        'label'=>'Workshops Background Colour',
        'setting'=>'ciniki-workshops-colour-background', 
        'type'=>'colour',
        'value'=>(isset($settings['ciniki-workshops-colour-background'])?$settings['ciniki-workshops-colour-background']:'no'),
        );
    $options[] = array(
        'label'=>'Workshops Border Colour',
        'setting'=>'ciniki-workshops-colour-border', 
        'type'=>'colour',
        'value'=>(isset($settings['ciniki-workshops-colour-border'])?$settings['ciniki-workshops-colour-border']:'no'),
        );
    $options[] = array(
        'label'=>'Workshops Font Colour',
        'setting'=>'ciniki-workshops-colour-font', 
        'type'=>'colour',
        'value'=>(isset($settings['ciniki-workshops-colour-font'])?$settings['ciniki-workshops-colour-font']:'no'),
        );

    return array('stat'=>'ok', 'options'=>$options);
}
?>

<?php

namespace EMST;

use EMST\Oauth;
use EMST\Lib;
use EMST\Views;

// Load the autoloader.
include_once( 'lib/autoloader.php' );

// Oauth to approve app.
$oauth = new Oauth\Auth();
$template = new Views\Template( 'authorize.html' );

$args = array(
    'output' => $oauth->init(),
);

$template->set( $args );
$template->renderHTML();

// App user data.
$users = new Lib\Users();
$users->init();
$users_data = $users->get_users_data();

// load templates.
foreach ( $users_data as $user ) {
    $template = new Views\Template( 'athlete.html' );
    $athlete = stapi()->get_athlete( $user->access_token );

    $args = array(
        'profile' => $athlete->getProfileMedium(),
        'firstname' => $athlete->getFirstName(),
        'lastname' => $athlete->getLastname(),
        'location' => stapi()->format_location( $athlete->getCity(), $athlete->getState(), $athlete->getCountry() ),
        'gender' => $athlete->getSex(),
    );

    $template->set( $args );
    $template->renderHTML();
}

foreach ( $users_data as $user ) { // this should only be once (not based on user)
    $template = new Views\Template( 'segment.html' );
    $segment = stapi()->get_segment( $user->access_token );

    $args = array(
        'name' => $segment->getName(),
        'distance' => stapi()->format_distance( $segment->getDistance() ),
        'avggrade' => stapi()->format_grade( $segment->getAverageGrade() ),
        'maxgrade' => stapi()->format_grade( $segment->getMaximumGrade() ),
        'elevgain' => stapi()->format_distance( $segment->getTotalElevationGain(), 'meters', 'feet' ),
        'category' => stapi()->format_climb_cat( $segment->getClimbCategory() ),
        'location' => stapi()->format_location( $segment->getCity(), $segment->getState(), $segment->getCountry() ),
    );

    $template->set( $args );
    $template->renderHTML();
}

foreach ( $users_data as $user ) {
    $efforts = stapi()->get_segment_efforts( $user->access_token ); // this needs to be a loop

    foreach ( $efforts as $effort ) :
        $template = new Views\Template( 'segment_efforts.html' );

        $args = array(
            'time' => stapi()->format_time( $effort->getElapsedTime() ),
            'iskom' => stapi()->is_kom( $effort->getIsKom() ),
            'date' => stapi()->format_date( $effort->getStartDate() ),
            'activityurl' => stapi()->get_activity_url_by_id( $effort->getActivity() ),
            'komrank' => stapi()->kom_rank( $effort->getKomRank() ),
            'prrank' => stapi()->pr_rank( $effort->getPrRank() ),
        );

        $template->set( $args );
        $template->renderHTML();
    endforeach;
}

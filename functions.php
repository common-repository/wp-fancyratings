<?php

######### Add Rating Custom Fields #########
add_action('publish_post', 'fr_add_ratings_fields');
add_action('publish_page', 'fr_add_ratings_fields');
function fr_add_ratings_fields($post_ID) {
    global $wpdb;
    if(!wp_is_post_revision($post_ID)) {
        add_post_meta($post_ID, '_rating_raters', 0, true);
        add_post_meta($post_ID, '_rating_average', 0, true);
    }
}


######### Delete Rating Custom Fields #########
add_action('delete_post', 'fr_delete_ratings_fields');
add_action('delete_page', 'fr_delete_ratings_fields');
function fr_delete_ratings_fields($post_ID) {
    global $wpdb;
    if(!wp_is_post_revision($post_ID)) {
        delete_post_meta($post_ID, '_rating_raters');
        delete_post_meta($post_ID, '_rating_average');
    }
}

######### Default rating #########
function fr_rating( $post_id = null) {
    global $post;
    $output = '';
    if (is_null($post_id) || $post_id == 0) { $post_id = get_the_ID(); }
    $output .= fancyratings_rating_custom($post_id);
    return $output;
}

function add_ratings_display($post_id = 0,  $prefix = null){
    $prefix = $prefix ? $prefix : '投票';
    return '<div class="rating-combo" data-post-id="'.$post_id.'"><span class="rating-toggle">' . $prefix . '</span><ul class="rating-star"><li><i class="star-5-0"></i></li><li><i class="star-4-0"></i></li><li><i class="star-3-0"></i></li><li><i class="star-2-0"></i></li><li><i class="star-1-0"></i></li></ul></div><meta content="5" itemprop="bestRating"><meta content="1" itemprop="worstRating">';

}

function fr_rating_custom($post_id= null,$prefix = null){
    global $wpdb;
    $output = '';
    $rating_info = fr_get_rating_info($post_id);
    if(is_singular()){
        $output .= '<div class="rate-holder clearfix" itemtype="http://schema.org/AggregateRating" itemscope="" itemprop="aggregateRating"><div class="post-rate"><div class="rating-stars" title="评分 '.$rating_info['average'].', 满分 5 星" style="width:'.$rating_info['percent'].'%">评分 <span class="average" itemprop="ratingValue">'.$rating_info['average'].'</span>, 满分 <span>5 星</span></div></div><div class="piao"><span itemprop="ratingCount">'.$rating_info['raters'].'</span> 票</div>';}
    else{
        $output .= '<div class="rate-holder clearfix"><div class="post-rate"><div class="rating-stars" title="评分 '.$rating_info['average'].', 满分 5 星" style="width:'.$rating_info['percent'].'%">评分 '.$rating_info['average'].', 满分 5 星</div></div><div class="piao">'.$rating_info['raters'].' 票</div>';
    }

    if(!isset($_COOKIE['fancyratings_'.$post_id]) && is_singular())
    {
        $output .= add_ratings_display($post_id,$prefix);
    }


    $output .= '</div>';
    return $output;

}

function fancyratings($post_id= null,$prefix = null){
	 if (is_null($post_id) || $post_id == 0) { $post_id = get_the_ID(); }
    echo fr_rating_custom($post_id,$prefix);

}


function fr_get_rating_info($post_id = null) {
    if (is_null($post_id) || $post_id == 0) { $post_id = get_the_ID(); }
    global $wpdb,$post;
    $_rating_raters = get_post_meta($post_id,'_rating_raters',true);
    $_rating_average = get_post_meta($post_id,'_rating_average',true);
    $output = array();
    if (!$_rating_raters || $_rating_raters == '' || $_rating_raters == 0 || !is_numeric($_rating_raters) || !$_rating_average || $_rating_average == '' || !is_numeric($_rating_average) ) {
        $output['raters'] = 0;
        $output['average'] = 0;
        $output['percent'] = 0;
    } else {
        $output['raters'] = $_rating_raters;
        $output['average'] = number_format_i18n(round($_rating_average, 2),2);
        $rating_per = $output['average'] * 20;
        $output['percent'] = round($rating_per, 2);
    }
    $output['max_rates'] = 5;
    return $output;
}

add_action('wp_ajax_nopriv_add_post_star', 'fr_ajax_callback');
add_action('wp_ajax_add_post_star', 'fr_ajax_callback');
function fr_ajax_callback(){
    global $post;
    $id = $_POST["id"];
    $scores = $_POST["score"];
    $expire = time() + 99999999;
    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
    setcookie('fancyratings_'.$id,$id,$expire,'/',$domain,false);
    $_rating_raters = get_post_meta($id,'_rating_raters',true);
    $_rating_average = get_post_meta($id,'_rating_average',true);
    if (!$_rating_raters || $_rating_raters == '' || !is_numeric($_rating_raters)) {
        update_post_meta($id, '_rating_raters', 1);
        update_post_meta($id, '_rating_average', $scores);
    } else {
        if (!$_rating_average || $_rating_average == '' || !is_numeric($_rating_average)) {
            update_post_meta($id, '_rating_raters', 1);
            update_post_meta($id, '_rating_average', $scores);
        } else {
            update_post_meta($id, '_rating_raters', ($_rating_raters + 1));
            $new_average = round((($_rating_raters * $_rating_average + $scores)/($_rating_raters + 1)),3);
            update_post_meta($id, '_rating_average', $new_average);
        }

    }
    $rating_info = fr_get_rating_info($id);
    $average = $rating_info['average'];
    $percent = $rating_info['percent'];
    $raters = $rating_info['raters'];
    $data = array("status"=>200,"data"=>array("average"=>$average,"percent"=>$percent,"raters"=>$raters));
    echo json_encode($data);
    die;
}


function fr_scripts(){
    wp_enqueue_style( 'fancyratings', FR_URL . '/static/css/style.css', array(), FR_VERSION );
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'fancyratings',  FR_URL . '/static/js/index.js' , array(), FR_VERSION );
    wp_localize_script( 'fancyratings', 'fancyratings_ajax_url', FR_ADMIN_URL . "admin-ajax.php");
}
add_action('wp_enqueue_scripts', 'fr_scripts', 20, 1);

?>
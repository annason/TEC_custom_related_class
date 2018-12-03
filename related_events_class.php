
<?php 

class Gdzieciak_related_events {

    public $price;
    public $tags;
    public $category;
    public $id;

    public $percent_for_range;
    public $percent_for_price;
    public $howmany;


    const FORRANGE = 5;  // percent for comparison price range when cost is a range ($5-10)
    const FORNUMBER = 30; // percent for comparison price range when cost is a number ($5)
    const HOWMANY = 3; // number of related events to show
 

    ///////////////////////////////////////// CONSTRUCT
    function __construct($eventID = null, $howmany_related = self::HOWMANY,  $range_percents = array(self::FORRANGE, self::FORNUMBER) ) {

        $this->id = $eventID;
 
        if(empty($range_percents)) {$range_percents = array(self::FORRANGE, self::FORNUMBER);}

        $this->percent_for_range = $range_percents[0];
        $this->percent_for_price = $range_percents[1];
        $this->howmany = $howmany_related;

        $this->price = get_post_meta($eventID,'_EventCost')[0];
        $this->tags = get_the_tags($eventID);
        $this->category = get_the_terms($eventID,'tribe_events_cat');

    }


    private function array_of_tags_ids($id_of_related = false) { /// create array of tag ids

        $tags_ids = array(); 

        if($id_of_related  !== false ) { // create array of tags ids for campared events

            $tags_in_related = get_the_tags($id_of_related);

            if(!is_array($tags_in_related)) {return $tags_ids;}

            foreach ($tags_in_related as $tag) {
                $id = $tag->term_id;
                array_push($tags_ids, $id);
            }
        }

        else {

            if(!is_array($this->tags)) {return $tags_ids;}

            foreach ($this->tags as $tag) { // create array of tags ids for our main event
                $id = $tag->term_id;
                array_push($tags_ids, $id);
            }
        }


        return $tags_ids;
    }

    private function array_of_cats_ids($id_of_related = false) { // create array of category ids

        $cats_ids = array();

        if($id_of_related  !== false ) { // create array of cats ids for campared events

            $cats_in_related = get_the_terms($id_of_related,'tribe_events_cat');

            if(!is_array($cats_in_related)) {return $cats_ids;}
        
            foreach ($cats_in_related as $cat) {
                $id = $cat->term_id;
                array_push($cats_ids, $id);
            }

        } else {

            if(!is_array($this->category)) {return $cats_ids;}

            foreach ($this->category as $cat) {
                $id = $cat->term_id;
                array_push($cats_ids, $id);
            }
        }

        return $cats_ids;
    }

    private function if_shares_any_tags($id_of_related) {

        $event_tags = $this->array_of_tags_ids();
        $comparison_tags = $this->array_of_tags_ids($id_of_related);

        $intersection = array_intersect($event_tags, $comparison_tags);

        if (!empty($intersection)) { // if there are any shared tags

            return true;

        } else  { return false; }

    }

    private function if_shares_all_tags($id_of_related) {

        $event_tags = $this->array_of_tags_ids();
        $comparison_tags = $this->array_of_tags_ids($id_of_related);

        $intersection = array_intersect($event_tags, $comparison_tags);

        if ( !empty($intersection) && count($intersection) == count($event_tags) ) { // if there are any shared tags

            return true;

        } else  { return false; }

    }   

    private function if_shares_any_cats($id_of_related) {

        $event_cats = $this->array_of_cats_ids();
        $comparison_cats = $this->array_of_cats_ids($id_of_related);

        $intersection = array_intersect($event_cats, $comparison_cats);

        if (!empty($intersection)) { // if there are any shared tags

            return true ;

        } else  { return false; }
    }

    private function if_shares_all_cats($id_of_related) {

        $event_cats = $this->array_of_cats_ids();
        $comparison_cats = $this->array_of_cats_ids($id_of_related);

        $intersection = array_intersect($event_cats, $comparison_cats);
  

        if ( !empty($intersection) && count($intersection) == count($event_cats) ) { // if there are any shared tags

            return true ;

        } else  { return false; }
    }

    private function is_recurring_related($id_of_related) {

        if(tribe_is_recurring_event($this->id)) {

            $event_parent_id = wp_get_post_parent_id($this->id);
            $related_parent_id = wp_get_post_parent_id($id_of_related);

            if($event_parent_id > 0 ) {  // main event is children

                if($event_parent_id == $id_of_related || $event_parent_id == $related_parent_id ) { // related is parent or sibling of main event
                    return true;
                } else {
                    return false;
                }

            } elseif ($event_parent_id == 0)  { //main event is parent

                if($related_parent_id == $this->id) { // related is child of main event

                } else { return false; }
            }
        }

        return false;
    }



    static function cost_int($cost) { /// casts aside non-numeric values and generate array of integers or single integer

        // returns integer or array

        if($cost !='') {

            if(is_numeric($cost)) { // is numeric
                    return (int) $cost;

            } elseif (is_array($cost)) {  //is array
                return $cost;
       
            } elseif ( strpos($cost, '-') !== false ) { // is range

                $from_to = explode("-", $cost);
                $from_to['from'] = $from_to[0]; unset($from_to[0]); $from_to['from'] = (int) $from_to['from'];
                $from_to['to'] = $from_to[1]; unset($from_to[1]); $from_to['to'] = (int) $from_to['to'];
                    return $from_to;
            }
        }
        return false;
    }


    public function cost_range($cost, $percent_range = null, $percent_price = null) { // generate array with cost range
        // returns an array or 0

        $amount = self::cost_int($cost);

        if( !isset($percent_range)) {
            $percent_range = $this->percent_for_range;
        }
        if( !isset($percent_price)) {
            $percent_price = $this->percent_for_price;
        }


        $percent_range = $percent_range / 100;
        $percent_price = $percent_price / 100;

        if( is_array($amount) ) {

           if (!array_key_exists ( 'from', $amount ) ) { return false;}

            $amount['from'] = $amount['from'] - ($amount['from'] * $percent_range); $amount['range_from'] = floor($amount['from']); unset($amount['from']);
            $amount['to'] = $amount['to'] + ($amount['to'] * $percent_range); $amount['range_to'] = ceil($amount['to']); unset($amount['to']);

            return  $amount;

        } elseif( is_numeric($amount) ) {

            if ($amount === 0) { return $amount; }

            $range_array = array();

            $range_array['range_from'] = floor($amount - ($amount * $percent_price));
            $range_array['range_to'] = ceil($amount + ($amount * $percent_price));

            return $range_array;

        }
        return false;
    }


    static function generate_cost_range($cost, $range_percents = array(self::FORRANGE, self::FORNUMBER) ){

        if(empty($range_percents)) {$range_percents = array(self::FORRANGE, self::FORNUMBER);}

        $percent_range = $range_percents[0];
        $percent_price = $range_percents[1];

        return self::cost_range($cost, $percent_range, $percent_price);
    }

    public function is_in_price_range($mainevent_price, $price_to_compare) {



        $mainevent_price = self::cost_int($mainevent_price); //array or int
        $price_to_compare = self::cost_int($price_to_compare); //array or int
        $range = self::cost_range($mainevent_price); //array or 0


        if ($range === 0) { // if range return 0 ( that means $mainevent_price is also 0 )

            if ($price_to_compare === 0) { return true; } else { return false; } 

        } elseif (is_array($price_to_compare)) { // if price to compare is array

            if (!array_key_exists ( 'from', $price_to_compare ) ) { return false;}

            if ( $price_to_compare['from']  >= $range['range_from'] && $price_to_compare['to']  <= $range['range_to']) {

                return true;
            }

        } elseif ( is_int($price_to_compare)) { // if price to compare is int

             if ($price_to_compare >= $range['range_from'] && $price_to_compare <= $range['range_to']) {
                return true;
             }
        }
        return false;
    }

    private function get_ids_of_pre_related($devmode = false) {

        $posts = new WP_Query( array(
            'posts_per_page' => -1,
            'post_type' => 'tribe_events',
            'eventDisplay' =>	'upcoming',
            
            'post__not_in' => [$this->id],

            'tax_query' => array(
                'relation' => 'OR',
                array(
                  'taxonomy' => 'post_tag',
                  'field' => 'term_id',
                  'terms' => $this->array_of_tags_ids(),
                ),
               array(
                  'taxonomy' => 'tribe_events_cat',
                  'field' => 'term_id',
                  'terms' => $this->array_of_cats_ids(),
                ),
              ),
            
        ));


        $dev = $ids = array();

        if ( $posts->have_posts() ) {
            while ( $posts->have_posts() ) {
                            $posts->the_post();
                $id = get_the_ID();            
                $start = get_post_meta($id,'_EventStartDate');
                $end = get_post_meta($id,'_EventEndDate');

                $ids[] = $id;
                $dev[] = get_the_title().' ---> '.$start[0].'-'.$end[0];
            } 
        }  wp_reset_query(); 

        if($devmode === "howmany") { return $posts->found_posts; }  // how many events in WP Query 
        elseif($devmode === "devmode") { return $dev; } // returns an array with events titles and dates


        return  $ids;
    }

    public function any_pre_related() {
        return $this->get_ids_of_pre_related("howmany");
    }


    public function show_related($howmany = null) {

        if(!isset($howmany)) { 
            $howmany = $this->howmany;
        }

        $prerelated = $this->get_ids_of_pre_related();

        $related = $price_tag_cat__any = $price_tag__any = $price_cat__any = $tag_cat__any = $tagsonly__any = $catsonly__any = $price_tag_cat__all = $price_tag__all =  $price_cat__all = $tag_cat__all = $tagsonly__all =  $catsonly__all = array(); 
        
        $output;
    

    foreach ($prerelated as $postid) {

        if($this->is_recurring_related($postid)) { // posts are in the same series
            $related[] = $postid.'_same_recurring';
        }

        elseif ( $this->if_shares_all_tags($postid) && $this->if_shares_all_cats($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0]) ) { /// 1. price & all tags & all cats

            $price_tag_cat__all[] = $postid.'_01';
        } 
        elseif ( $this->if_shares_any_tags($postid) && $this->if_shares_any_cats($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0]) ) { /// 2.  price & any tag & any event cat

            $price_tag_cat__any[] = $postid.'_02';
        } 



        elseif ($this->if_shares_all_tags($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0])) {  /// 3.  price & all tags

            $price_tag__all[] = $postid.'_03';

        }
        elseif ($this->if_shares_any_tags($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0])) {  /// 4. price & any tag

            $price_tag__any[] = $postid.'_04';
        }



        elseif ($this->if_shares_all_cats($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0])) { /// 5. price & all cats

            $price_cat__all[] = $postid.'_05';

        }
        elseif ($this->if_shares_any_cats($postid) && $this->is_in_price_range($this->price, get_post_meta($postid,'_EventCost')[0])) { /// 6. price & any event cat

            $price_cat__any[] = $postid.'_06';
        }



        elseif ($this->if_shares_all_tags($postid) && $this->if_shares_all_cats($postid)) { /// 7. all tags & all cats

            $tag_cat__all[] = $postid.'_07';

        }
        elseif ($this->if_shares_any_tags($postid) && $this->if_shares_any_cats($postid)) { /// 8. any tag & any event cat

            $tag_cat__any[] = $postid.'_08';
        }



        elseif ($this->if_shares_all_tags($postid)) { /// 9. all tags

            $tagsonly__all[] = $postid.'_09';

        }
        elseif ($this->if_shares_any_tags($postid)) { /// 10. any tag

            $tagsonly__any[] = $postid.'_10';
        }



        elseif ($this->if_shares_all_cats($postid)) { /// 11. all cats

            $catsonly__all[] = $postid.'_11';
        }
        elseif ($this->if_shares_any_cats($postid)) { /// 12. any event cat

            $catsonly__any[] = $postid.'_12';
        }

    }

    $all_related = array_merge(
        $price_tag_cat__all, $price_tag_cat__any,
        $price_tag__any, $price_tag__all,
        $price_cat__all, $price_cat__any,
        $tag_cat__all, $tag_cat__any,
        $related,
        $tagsonly__all, $tagsonly__any,
        $catsonly__all, $catsonly__any
    );


        if ($howmany > sizeof($all_related ) ) {
            $howmany = sizeof($all_related );
        }

    for ($i = 0; $i <=($howmany - 1); $i++) {

        $id = explode("_", $all_related[$i])[0];
        $case = explode("_", $all_related[$i])[1];
        if($this->cost_range($this->price) != 0) {$data_range = implode('-', $this->cost_range($this->price));} else {$data_range = $this->cost_range($this->price);}

        $html = '
            <div class="realted-item data-case-related="'.$case.'" data-price-range="'.$data_range.'">

                <div class="post-image">
                    <img alt=" '.get_the_title($id).'" src="
                    '.get_the_post_thumbnail_url($id).' ">
                </div>

                <div class="post-title">
                    <a href="'.get_permalink($id).'">'.get_the_title($id).'</a>
                </div>
                
            </div>';

        
        $output .= $html;

        
    }


    return $output;

    }


}





// case 1: price & all tags & all cats
/// case 2: price & any tag & any event cat
//// case 3: price & all tags
///// case 4: price & any tag
////// case 5: price & all cats
/////// case 6: price & any event cat
//////// case 7: all tags & all cats
///////// case 8: any tag & any event cat
////////// posts are in the same series of recurring
/////////// case 9: all tags
//////////// case 10: any tag
///////////// case 11: all cats
////////////// case 12: any event cat

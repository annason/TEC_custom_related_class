# Class for related events in Tribe Events Calendar

My custom class for generating list of related events for tribe_events custom post type.

## How to use

First of all you need to create an instance of Gdzieciak_related_events class, providing at least the id of tribe_event for which you want to create a list of related events.

```php
$related = new Gdzieciak_related_events($event_id);
```

### Class instance parameters

```php
new Gdzieciak_related_events($event_id, $number_of_related, $price_range_percents)
```

**$event_id** (int) - required / ID of main event (tribe_events post type)

**$number_of_related** (int) - optional / default: 3 / Number of related posts to show

**$price_range_percents** (array) - optional / default: [5,30]  / Array with number that would be used as a percent for creating price range for comparison.
First one is percent for cost ranges (i.e. $10-25), thus by default comparison range would be created based on small +/- 5% differance. The second one is a percent for creating comparison range prices that are numbers. Any other type of price will be ignored.


### Method for related

Use _show_related()_ method in place where you want to display your related events.

```php
echo $related->show_related();
```

### Static methods

You can use static _cost_int($cost)_ method to discard non-numerical values and return an array or an int,
_generate_cost_range($cost, $range_percents_array)_ enables you to generate price range withouth creating an object from class, if you would need it for other purposes.


## HTML structure

You can find structure ourput in _show_related()_ method, as $html variable:

```php

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
```

You can change it as you like.

**$case** is a number of relation level case, 

**$data_range** is a final price range for comparison, saved as data value for debugging purposes (it would be blank if event doesn't have any price)

**$id** is a post id of related event; you can use it with standard wordpress functions.


### Relation level cases

```
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
```


## Example class usage

```php

<?php $related = new Gdzieciak_related_events(get_the_ID()); ?>

<?php if($related->any_pre_related() > 2):?>

    <h1>Related</h1>

    <div id="related_events"> 

        <?php echo $related->show_related(); ?>

    </div> 

<?php endif; ?>


```





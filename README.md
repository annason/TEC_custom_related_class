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

**$event_id** (int) - reguired / ID of main event (tribe_events post type)

**$number_of_related** (int) - optional / default: 3 / Number of related posts to show

**$price_range_percents** (array) - optional / default: [5,30] ] / Array with number that would be used as a percent for creating price range for comparison.
First one is percent for cost ranges (i.e. $10-25), thus by default comparison range would be created based on small +/- 3% differance. The second one is a percent for prices that are numbers. Any other type of price will be ignored.


### Method for related

Use show_related() methen in place where you want diplasy related events.

```php
echo $related->show_related();
```

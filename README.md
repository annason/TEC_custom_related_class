# Class for related events in Tribe Events Calendar

My custom class for generating list of related events for tribe_events custom post type.

## How to use

First of all you need to create an instance of Gdzieciak_related_events class, providing at least the id of tribe_event for which you want to create a list of related events.

```
$related = new Gdzieciak_related_events($event_id);
```

### Class instance Parameters

```
new Gdzieciak_related_events($event_id, $number_of_related, $price_range_percents)
```

*$event_id* (int) - ID of main event (tribe_events post type)

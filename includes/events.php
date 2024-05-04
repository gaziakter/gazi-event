
<div class="event-list">
    <?php if (empty($events)) : ?>
        <p>No events found for this month.</p>
    <?php else : ?>
        <ul>
            <?php foreach ($events as $event) : ?>
                <li>
                    <strong><?php echo get_the_title($event->ID); ?>:</strong>
                    <?php echo get_post_meta($event->ID, 'event_date', true); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

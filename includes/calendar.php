<?php
// Get current month and year
$current_month = date('m');
$current_year = date('Y');

// Get first day and total days in the current month
$first_day = date('N', strtotime("$current_year-$current_month-01"));
$total_days = date('t', strtotime("$current_year-$current_month-01"));

// Array to map numeric representation of days to their names
$days_map = array(
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
);
?>

<div class="event-calendar">
    <h2><?php echo date('F Y', strtotime("$current_year-$current_month-01")); ?></h2>
    <div class="calendar-navigation">
        <a href="#" class="prev-month">&lt;</a>
        <a href="#" class="next-month">&gt;</a>
    </div>

    <table>
        <thead>
            <tr>
                <?php foreach ($days_map as $day_number => $day_name) : ?>
                <th><?php echo $day_name; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                $day_counter = 1;
                // Print empty cells for days before the first day of the month
                for ($i = 1; $i < $first_day; $i++) : ?>
                <td></td>
                <?php endfor; ?>

                <?php
                // Print days of the month
                for ($day = 1; $day <= $total_days; $day++) :
                    // Check if the current day has an event
                    $has_event = false;
                    foreach ($events as $event) {
                        $event_date = get_post_meta($event->ID, 'event_date', true);
                        if (date('Y-m-d', strtotime($event_date)) == date('Y-m-d', strtotime("$current_year-$current_month-$day"))) {
                            $has_event = true;
                            break;
                        }
                    }
                ?>
                <td class="<?php echo $has_event ? 'has-event' : ''; ?>">
                    <?php echo $day; ?>
                </td>
                <?php
                    // Start new row for each Sunday
                    if (($first_day + $day_counter) % 7 == 0 && $day != $total_days) : ?>
            </tr>
            <tr>
                <?php endif;
                    $day_counter++;
                    ?>
                <?php endfor; ?>
            </tr>
        </tbody>
    </table>


</div>
jQuery(document).ready(function($) {

    // Function to load events for a specific month
    function loadEvents(year, month) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_events',
                year: year,
                month: month
            },
            success: function(response) {
                $('.event-list').html(response);
            }
        });
    }

    // Initial load of events for current month
    loadEvents(<?php echo $current_year; ?>, <?php echo $current_month; ?>);

    // Event delegation for next and previous month buttons
    $('.event-calendar').on('click', '.prev-month', function(e) {
        e.preventDefault();
        var prevMonth = <?php echo ($current_month == 1) ? 12 : $current_month - 1; ?>;
        var prevYear = <?php echo ($current_month == 1) ? $current_year - 1 : $current_year; ?>;
        loadEvents(prevYear, prevMonth);
    });

    $('.event-calendar').on('click', '.next-month', function(e) {
        e.preventDefault();
        var nextMonth = <?php echo ($current_month == 12) ? 1 : $current_month + 1; ?>;
        var nextYear = <?php echo ($current_month == 12) ? $current_year + 1 : $current_year; ?>;
        loadEvents(nextYear, nextMonth);
    });

    // Highlight event dates
    $('.event-calendar table td.has-event').hover(
        function() {
            $(this).addClass('highlight');
        },
        function() {
            $(this).removeClass('highlight');
        }
    );

    // Event delegation for showing event details when clicking on a date
    $('.event-calendar table').on('click', 'td.has-event', function() {
        var eventDate = $(this).text();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_event_details',
                date: eventDate
            },
            success: function(response) {
                alert(response); // Replace this with your desired action to display event details
            }
        });
    });
});

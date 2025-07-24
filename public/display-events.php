<?php
function wec_display_events() {
    global $wpdb;
    $table = $wpdb->prefix . 'wec_events';
    $results = $wpdb->get_results("SELECT * FROM $table WHERE event_date >= CURDATE() ORDER BY event_date ASC");

    ob_start();
    echo '<div class="wec-events">';
    foreach ($results as $event) {
        echo "<div class='wec-event'>
            <h3>$event->title</h3>
            <p><strong>Date:</strong> $event->event_date</p>
            <p>$event->description</p>
        </div>";
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('events_calendar', 'wec_display_events');

<?php
function wec_add_menu_page() {
    add_menu_page(
        'Events Calendar',
        'Events',
        'manage_options',
        'wec-events',
        'wec_render_admin_page',
        'dashicons-calendar-alt'
    );
}
add_action('admin_menu', 'wec_add_menu_page');

function wec_render_admin_page() {
    $message = '';
    if (isset($_POST['submit_event'])) {
        if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'add_event')) {
            $title = sanitize_text_field($_POST['event_title']);
            $date = sanitize_text_field($_POST['event_date']);
            $desc = sanitize_textarea_field($_POST['event_description']);
            
            if (strtotime($date)) {
                global $wpdb;
                $table = $wpdb->prefix . 'wec_events';
                $result = $wpdb->insert($table, [
                    'title' => $title,
                    'event_date' => $date,
                    'description' => $desc,
                ]);
                
                if ($result) {
                    $message = '<div class="notice notice-success"><p>Event added successfully!</p></div>';
                } else {
                    $message = '<div class="notice notice-error"><p>Error adding event.</p></div>';
                }
            } else {
                $message = '<div class="notice notice-error"><p>Invalid date format.</p></div>';
            }
        } else {
            $message = '<div class="notice notice-error"><p>Security check failed.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Add New Event</h1>
        <?php echo $message; ?>
        <div class="card">
            <div class="wec-message"></div>
            <form method="post" action="" id="wec-event-form" class="wec-admin-form" style="padding: 20px;">
                <?php wp_nonce_field('add_event'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="event_title">Event Title</label></th>
                        <td>
                            <input type="text" id="event_title" name="event_title" class="regular-text" 
                                placeholder="Enter event title" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="event_date">Event Date</label></th>
                        <td>
                            <input type="text" id="event_date" name="event_date" class="regular-text wec-date-picker" 
                                placeholder="Select event date" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="event_description">Description</label></th>
                        <td>
                            <textarea id="event_description" name="event_description" class="large-text" 
                                rows="5" placeholder="Enter event description"></textarea>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_event" class="button button-primary" value="Add Event">
                </p>
            </form>
        </div>
    </div>
    <?php
}

// Save event
if (isset($_POST['submit_event'])) {
    $title = sanitize_text_field($_POST['event_title']);
    $date = sanitize_text_field($_POST['event_date']);
    $desc = sanitize_textarea_field($_POST['event_description']);
    global $wpdb;
    $table = $wpdb->prefix . 'wec_events';
    $wpdb->insert($table, [
        'title' => $title,
        'event_date' => $date,
        'description' => $desc,
    ]);
}

jQuery(document).ready(function($) {
    // Initialize date picker for event date field
    if ($.fn.datepicker) {
        $('.wec-date-picker').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0, // Prevent selecting past dates
            changeMonth: true,
            changeYear: true
        });
    }

    // AJAX form submission
    $('#wec-event-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('input[type="submit"]');
        var formData = form.serialize();

        // Disable submit button during submission
        submitBtn.prop('disabled', true);

        $.ajax({
            url: wec_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wec_add_event',
                nonce: wec_ajax.nonce,
                formData: formData
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('.wec-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    // Reset form
                    form[0].reset();
                    // Refresh events list if it exists
                    if ($('.wec-events').length) {
                        refreshEventsList();
                    }
                } else {
                    // Show error message
                    $('.wec-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('.wec-message').html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Function to refresh events list via AJAX
    function refreshEventsList() {
        $.ajax({
            url: wec_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wec_get_events',
                nonce: wec_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.wec-events').html(response.data.html);
                }
            }
        });
    }

    // Form validation
    function validateEventForm() {
        var isValid = true;
        var title = $('#event_title').val();
        var date = $('#event_date').val();
        
        // Clear previous error messages
        $('.wec-error').remove();

        // Validate title
        if (!title.trim()) {
            $('#event_title').after('<span class="wec-error">Please enter an event title</span>');
            isValid = false;
        }

        // Validate date
        if (!date) {
            $('#event_date').after('<span class="wec-error">Please select a date</span>');
            isValid = false;
        } else {
            var selectedDate = new Date(date);
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                $('#event_date').after('<span class="wec-error">Please select a future date</span>');
                isValid = false;
            }
        }

        return isValid;
    }

    // Add validation before form submission
    $('#wec-event-form').on('submit', function(e) {
        if (!validateEventForm()) {
            e.preventDefault();
        }
    });

    // Add error styling
    $('<style>\
        .wec-error {\
            color: #dc3232;\
            display: block;\
            margin-top: 5px;\
            font-size: 0.9em;\
        }\
        .wec-events .loading {\
            text-align: center;\
            padding: 20px;\
        }\
    </style>').appendTo('head');
});

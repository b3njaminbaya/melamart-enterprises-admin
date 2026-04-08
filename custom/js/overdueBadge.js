/**
 * Load Overdue Orders Count Badge
 * This script loads the count of overdue orders and displays it in the navigation
 */
$(document).ready(function() {
    loadOverdueCount();
    
    // Refresh every 5 minutes
    setInterval(function() {
        loadOverdueCount();
    }, 300000);
});

function loadOverdueCount() {
    $.ajax({
        url: 'php_action/getOverdueOrders.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success && response.count > 0) {
                $('#overdue-badge').text(response.count).show();
                $('#overdue-badge').addClass('badge-danger');
            } else {
                $('#overdue-badge').text('').hide();
            }
        },
        error: function() {
            // Silently fail - don't show error to user
            $('#overdue-badge').text('').hide();
        }
    });
}

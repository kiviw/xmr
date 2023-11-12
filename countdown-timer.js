// File: countdown-timer.js
jQuery(document).ready(function ($) {
    // Set the date we're counting down to
    var countDownDate = new Date(countdown_timer_data.expiration_time * 1000).getTime();

    // Update the countdown every 1 second
    var x = setInterval(function () {
        // Get the current date and time
        var now = new Date().getTime();

        // Calculate the remaining time
        var distance = countDownDate - now;

        // Calculate minutes and seconds
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the countdown timer
        document.getElementById("countdown-timer").innerHTML = minutes + "m " + seconds + "s ";

        // If the countdown is over, redirect to the shop page
        if (distance < 0) {
            clearInterval(x);
            window.location.href = '<?php echo wc_get_page_permalink("shop"); ?>';
        }
    }, 1000);
});

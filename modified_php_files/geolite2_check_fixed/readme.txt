since xtream-ui.com is down, it won't get version info with json, it will wait timeout before open the settings page.

i just edited links and reduced timeout value in settings.php and functions.php, 
also it won't check geolite2 version less than a week to reduce requests to json file.
and last, removed version info bar due same reason, timeout.
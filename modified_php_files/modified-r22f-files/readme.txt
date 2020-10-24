edited these php files to,
- allow mag events for resellers
- edit user shows date with time
- removed version and geolite2 version info from settings page, site is offline, json requests have been dropped after timeout.
- let resellers to convert mag to normal user, it is 1 way process, will delete mag device entry from db.

- 24-10-2020, forked an user_reseller.php from woi topic, it will allow users to select bouquets, if you want to hide a bouquet, use "admin" word in bouquet name, or edit functions.php getbouquets() function with any other filter thing, it is up to you.

copy and paste onto r22f installed server.
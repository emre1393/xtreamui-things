NOTE: things below are belong to xpcom.common_oldone.js file. basically, add new mag types to js file and into "allowed_stb_types" column.

- find "MAG352" and replace with "MAG352, MAG420" if you wanna add MAG420 or other models, basically put new ones into same list with MAG352.



-edit admin/settings.php and add new mag devices into array. then add new mags into allowed stb list in panel settings.


find,

$rMAGs = Array("AuraHD","AuraHD2","AuraHD3","AuraHD4","AuraHD5","AuraHD6","AuraHD7","AuraHD8","AuraHD9","MAG200","MAG245","MAG245D","MAG250","MAG254","MAG255","MAG256","MAG257","MAG260","MAG270","MAG275","MAG322","MAG323","MAG324","MAG325","MAG349","MAG350","MAG351","MAG352","MAG420","WR320");


replace with,

$rMAGs = Array("AuraHD","AuraHD2","AuraHD3","AuraHD4","AuraHD5","AuraHD6","AuraHD7","AuraHD8","AuraHD9","MAG200","MAG245","MAG245D","MAG250","MAG254","MAG255","MAG256","MAG257","MAG260","MAG270","MAG275","MAG322","MAG323","MAG324","MAG325","MAG349","MAG350","MAG351","MAG352","MAG420","MAG420w1","MAG420w3","WR320");



NOTE 2:

the other "xpcom.common.js" does not check fw or stb type support. 
if you leave your allowed stb type list empty, it will allow every stb type.
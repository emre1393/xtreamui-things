edit admin/settings.php and add new mag devices into array. then add new mags into allowed stb list in panel settings.


find,

$rMAGs = Array("AuraHD","AuraHD2","AuraHD3","AuraHD4","AuraHD5","AuraHD6","AuraHD7","AuraHD8","AuraHD9","MAG200","MAG245","MAG245D","MAG250","MAG254","MAG255","MAG256","MAG257","MAG260","MAG270","MAG275","MAG322","MAG323","MAG324","MAG325","MAG349","MAG350","MAG351","MAG352","MAG420","WR320");


replace with,

$rMAGs = Array("AuraHD","AuraHD2","AuraHD3","AuraHD4","AuraHD5","AuraHD6","AuraHD7","AuraHD8","AuraHD9","MAG200","MAG245","MAG245D","MAG250","MAG254","MAG255","MAG256","MAG257","MAG260","MAG270","MAG275","MAG322","MAG323","MAG324","MAG325","MAG349","MAG350","MAG351","MAG352","MAG420","MAG420w1","MAG420w3","WR320");

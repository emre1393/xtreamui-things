someone asked "edit stb mag to show mac address on app"

Show MAC address on "your stb is blocked" screen
Go to wwwdir/c/blocking.js file,
find,

this.text_msg.innerHTML = msg || get_word('cut_off_msg');

replace,

this.text_msg.innerHTML = msg || '<span class="label">'+get_word('cut_off_msg')+ '<br>' +get_word('MAC') + ':</span> ' + stb.mac + '<br>';

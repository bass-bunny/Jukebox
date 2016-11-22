var essidArray = [];
var networks = [], xhr, networkz = [];

function wifi_networks() {
    xhr = $.ajax({
        url: "assets/modals/network_settings/wifi/wifi_scan.php",
        type: 'POST',
        data: 'data',
        dataType: 'json',
        cache: false,
        success: function (json) {
         networks = [];

         var r = [],
         j = 0;

         networkz = json;

         $.each (json, function (key, val) {
            var essid = val['ESSID'];
            var nlength = networks.length;
            var encryption = val['encryption'];
            var connected = val['connected'];
            var type = val['encryption_type'];
            var saved = val['saved'];

            var signal = val['signal'];

            var classes = "networkContainer";

            if (connected) {
                classes += " playing";
            }

            r[++j] = '<tr class="' + classes + '" data-essid="' + essid + '" data-encryption="' + encryption + '">';

            r[++j] = '<td class="wifiID">';
            r[++j] = nlength + 1;
            r[++j] = '</td>';
            r[++j] = '<td class="wifiESSID">' + essid + '</td>';

            var icon = "";

            if (encryption == "on") {
                icon = '<i class="fa fa-lock" aria-hidden="true" title="'+type+'"></i>';
            } else if (encryption == "saved") {

            } else {
                icon = '<i class="fa fa-unlock" aria-hidden="true" title="open"></i>';
            }

            if(saved){
                icon += ' <i class="fa fa-floppy-o" aria-hidden="true" title="saved"></i>';
            }

            if(connected){
                icon += ' <i class="fa fa-link" aria-hidden="true" title="connected"></i>'
            }

            r[++j] = '<td class="wifiEncryption">' + icon + '</i></td>';

            r[++j] = '<td class="wifiQuality">';
            r[++j] = "<div class='progressBar' style='width: 98%; margin-bottom: 0;'>";
            r[++j] = "<div class='progress' title='" + signal + "%' style='width: " + signal + "%;' ></div></div>";
            r[++j] = '</td></tr>';
            j = 0;

                // if (essidArray.indexOf(essid) == '-1' && encryption != 'null' && encryption != null) {
                //     essidArray.push(essid);
                //     var network = r.join('');
                //     networks.push(network);
                // } else {
                //     console.log("cose brutte qui");
                // }


                essidArray.push(essid);
                var network = r.join('');
                networks.push(network);
            });

         if (networks.length) {
            $('#wifiTable').find('tbody').html(networks);
        } else {
            $('#wifiTable').find('tbody').html("<tr><td colspan='4' class='text-center'>I found no WiFi network. :(</td></tr>");
        }

        bindWifiScannerClicks();
    },
    error: function () {
        $('#wifiTable').find('tbody').html("An error has occured");
    }
});
}

function stopScan() {
    try {
        xhr.abort();
        clearInterval(handleScanner);
    } catch (e) {

    }
}

function startScan() {
    $(document).ready(function () {
        wifi_networks();

        // This will run the scanner every 1000 seconds. Doesn't look like it's getting more networks anyways
        handleScanner = setInterval(wifi_networks, 5000);
    });
}


//if($('#wifiTable').is(":hidden"))

$('#wifiTable').on('remove', function () {
    stopScan();
});

$('.connectbtn').click(function () {
    stopScan();
    closeModal();
});


function bindWifiScannerClicks() {
    $('.networkContainer').click(function () {
        var essid = $(this).attr('data-essid');

        //setNetwork(essid);

        openNetworkDetails(networkz[essid]);
    });
}

function setSelectedNetwork(callback){
    setNetwork(selected_network['ESSID'], callback);
}

function setNetwork(essid, callback){
    var network = networkz[essid];

    $('#ssid').val(essid);

    if(!network.saved){
        var password = $('#network_password').val();

        network['password'] = password;

        $.ajax({
            type: "POST",
            url: "assets/modals/network_settings/wifi/wifi_update.php",
            data: JSON.stringify(network)
        }).always(function () {
            
            $('#network_settings_form').submit();
            
            closeNetworkDetails();

            if(typeof callback !== 'undefined')
            callback();
        });
    } else {
        $('#network_settings_form').submit();
        closeNetworkDetails();
    }
    
    
}

function forgetNetwork(essid, callback){
    var network = networkz[essid];

    console.log(network);

    if(network.saved){
        $.ajax({
            url: "assets/modals/network_settings/wifi/wifi_forget.php?essid="+essid,
            //contentType: "application/json; charset=utf-8",
            //dataType: "json",
        }).done(function (){
            if(typeof callback !== 'undefined')
                callback();
        });
    }
}


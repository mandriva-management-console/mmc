function toggleCustom() {
    if ($F('service') == "custom")
        $('custom').show();
    else
        $('custom').hide();
}

function restartFirewall(urlRestart, urlCheck, urlLogs) {
    checkService = function() {
        new Ajax.Request(urlCheck, {
            onSuccess: function(r) {
                if (r.responseJSON[1]) {
                    var status = r.responseJSON[1];
                    if (status != "active" && status != "failed")
                        setTimeout(checkService, 200);
                    else {
                        if (status == "failed") {
                            $('restartBtn').update("Restart failed. Check logs");
                            $('restartBtn').disabled = false;
                        }
                        else {
                            $('restartBtn').update("Firewall restarted");
                        }
                    }
                }
                else {
                    $('restartBtn').update("Failed to restart the firewall");
                }
            }
        });
    };
    new Ajax.Request(urlRestart, {
        onSuccess: function() {
            $('restartBtn').update("Restarting the firewall...");
            $('restartBtn').disabled = true;
            setTimeout(checkService, 200);
        }
    });
}

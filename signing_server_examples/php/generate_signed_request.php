<?php

require('OpenEd/SignedServerRequest.php');

define('OPENED_CLIENT_ID', '<< YOUR OPENED CLIENT ID HERE >>');
define('OPENED_APP_SECRET', '<< YOUR OPENED APP SECRET HERE >>');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_redirect('index.html');
}

if (!isset($_POST['username'])) {
    die('Username is required');
}

$request = new OpenEd\SignedServerRequest(OPENED_CLIENT_ID, OPENED_APP_SECRET);
$signed_request = $request->generateSignedRequest($_POST['username']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login and Query</title>
</head>
<body>
<script src="../../opened-api.js"></script>
<script>

    OpenEd.api.init({
        client_id: '<?= OPENED_CLIENT_ID ?>'
    });

    OpenEd.api.silentLogin('<?= $signed_request ?>', function () {
        var search_params = {
            standard: 'A.APR.1'
        };

        OpenEd.api.request('/resources', search_params, function (data) {
            var resourcesList = document.getElementById('resources_list');

            (data.resources || []).forEach(function (resource) {
                var li         = document.createElement('li'),
                    span       = document.createElement('span');
                span.innerText = resource.title;
                li.appendChild(span);
                resourcesList.appendChild(li);
            });
        });

    }, function (data) {
        document.getElementById('error').innerText = data.error;
    });
</script>

<ul id="resources_list">
</ul>
<pre id="error">
</pre>
</body>
</html>

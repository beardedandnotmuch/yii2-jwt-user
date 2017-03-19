<!DOCTYPE html>
<html>
<head>
    <script>
        var usingExternalWindow = function() {
            var nav     = navigator.userAgent.toLowerCase(),
            ieLTE10 = (nav && nav.indexOf('msie') != -1),
            ie11    = !!navigator.userAgent.match(/Trident.*rv\:11\./);
            return !(ieLTE10 || ie11);
        }
        if (usingExternalWindow()) {
            window.addEventListener("message", function(ev) {
                if (ev.data === "requestCredentials") {
                    ev.source.postMessage({
                            <?= $content ?>
                    }, '*');
                    window.close();
                }
            });
        } else {
            window.location.href = "<?= $_GET['auth_origin_url'] ?>";
        }
    </script>
</head>
<body>
    <pre>
        Redirecting...
    </pre>
</body>
</html>

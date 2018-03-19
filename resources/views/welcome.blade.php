<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>

    <div id="moein"></div>
    
    <script>
        var conn = new WebSocket('ws://localhost:8080/auth');
        conn.onmessage = function(e) { document.querySelector("#moein").innerHTML = new Date + ' - ' + JSON.stringify(e.data); };
        conn.onopen = function(e) { console.log("connected!"); conn.send('Hello Me!'); };
    </script>
</body>
</html>
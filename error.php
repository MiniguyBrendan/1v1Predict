<html>

<head>
  <script src="js/jquery-1.12.3.min.js"></script>
  <link rel="stylesheet" type="text/css" href="css/error.css">
  <title>
    Error
  </title>
</head>
<body>
  <script type="text/javascript">
  function preload(arrayOfImages) {
    $(arrayOfImages).each(function() {
      $('<img/>')[0].src = this;
    });
  }

  preload([
    'img/amumu.jpg',
  ]);
  
  <?php 
    session_start();
    echo 'var msg = ' . json_encode($_SESSION['error']).';';
  ?>

  var timer = 0;

  timer = setInterval("updateElement()", 55);

  function updateElement() {
    var oldValue = $('p').text();

    if (msg == oldValue) {
      clearInterval(timer);
    } else {
        $('p').text($('p').text() + msg.substring(oldValue.length, oldValue.length+1));
    }
  }
  </script>
  <center><p></p></center>
</body>

</html>
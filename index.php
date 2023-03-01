<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/svg+xml" href="resources/static/images/favicon.svg">

    <title>AlbumApp | Aplicación de Album de Figus</title>
    <!-- Bootstrap core CSS -->
    <link href="resources/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="resources/static/bootstrap/css/jumbotron.css" rel="stylesheet">
    <link href="resources/static/bootstrap/css/sticky-footer-navbar.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet">
    <!-- AngularJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.8.2/angular.min.js" integrity="sha512-7oYXeK0OxTFxndh0erL8FsjGvrl2VMDor6fVqzlLGfwOQQqTbYsGPv4ZZ15QHfSk80doyaM0ZJdvkyDcVO7KFA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Router -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-route/1.8.2/angular-route.min.js" integrity="sha512-5zOAub3cIpqklnKmM05spv4xttemFDlbBrmRexWiP0aWV8dlayEGciapAjBQWA7lgQsxPY6ay0oIUVtY/pivXA==" crossorigin="anonymous"></script>
    <!-- Satellizer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/satellizer/0.14.1/satellizer.min.js" integrity="sha512-ZLAGfaREnf5hq51URaG84dBY6DhCVOxxwvhMEsPPRj5qTbN9NU2cp4hyaWzc9a0k1UrsLYWU5vbVPvme0d/n6A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-sanitize.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/tinymce.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/angular-ui-tinymce/0.0.19/tinymce.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ngStorage/0.3.10/ngStorage.min.js"></script>
  </head>

  <body data-ng-app="albumApp"> 
    
    <menu></menu>
    
    
      <ng-view></ng-view>
    
      <footer class="footer">
        <mi-footer></mi-footer>
      </footer>
    
    <script src="js/app.js"></script>
    <script src="js/partes/menu.js"></script>
    <script src="js/partes/footer.js"></script>
    <script src="js/rutas/figusList.js"></script>
    <script src="js/rutas/equipos.js"></script>
    <script src="js/rutas/login.js"></script>
    <script src="js/rutas/registro.js"></script>
    <script src="js/rutas/detalle.js"></script>
    <script src="js/rutas/formCanje.js"></script>
    <script src="js/rutas/listUsuarios.js"></script>
  </body>
</html>
angular.module('albumApp')
.component('menu', {
	templateUrl	: 'resources/templates/fragments/menu.html',
	controller	: function ($auth, $location, $http, $localStorage) {
		var ctrl = this;

		ctrl.salir = function () {
			if (confirm ('¿Está seguro de querer salir del sistema?')) {
				$http.post('api/logout')
					.then(function (response) {
						$auth.logout();
						$localStorage.$reset();
						ctrl.perfil = {id: null};
						ctrl.usuario = {id: null, nombre: null};
						$localStorage.perfil = ctrl.perfil;
						$localStorage.usuario = ctrl.usuario;
						$location.path('/login');
					})
					.catch(function (response) {
						alert('Imposible salir del sistema');
					})
			}
		}

		ctrl.registro = function () {
			$location.path('/registro');
		}

		ctrl.login = function () {
			$location.path('/login');
		}

		ctrl.actual = function (ruta) {
			return $location.path() == ruta;
		}

		ctrl.perfil = function(){
			return pasaId.get();
		}

		ctrl.administrador = function(){
			return $localStorage.perfil.id == 2;
		}

		ctrl.mostrarNombre = function(){
			ctrl.usuario = $localStorage.usuario;
			return ctrl.usuario.nombre == $localStorage.usuario.nombre;
		}			
		

		this.$onInit = function () {
			ctrl.usuario = $localStorage.usuario;
			ctrl.autenticado = $auth.isAuthenticated;
		}
	}
})
;
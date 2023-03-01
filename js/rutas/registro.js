angular.module('albumApp')
.component('registro', {
	templateUrl	: 'resources/templates/formRegistro.html',
	controller	: function ($http, $location) {
		var ctrl = this;

		ctrl.cancelar = function () {
			$location.path('/');
		}

		ctrl.registrar = function () {
			$http.post('api/usuarios', ctrl.nuevoUsuario)
				.then(function (response) {
					alert('Usuario registrado exitosamente');
					$location.path('/');
				})
				.catch(function (response) {
					alert(response.data.error);
				})
		}

		this.$onInit = function () {
			ctrl.nuevoUsuario=null;
		}
	}
})
;
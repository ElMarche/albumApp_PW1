angular.module('albumApp')
.component('login', {
	templateUrl	: 'resources/templates/login.html',
	controller	: function ($auth, $location, $localStorage) {
		var ctrl = this;	

		ctrl.ingresar = function () {
			$auth.login({"email": ctrl.login.email, "clave": ctrl.login.clave }) 
				.then(function(response) {
					$auth.setToken(response.data.jwt);
					ctrl.perfil = {id: response.data.idperfil};
					ctrl.usuario = {id: response.data.idusuario, nombre: response.data.nombre};
					$localStorage.perfil = ctrl.perfil;
					$localStorage.usuario = ctrl.usuario;
					$location.path('/figusList');
				})
				.catch(function(response) {
					alert("Login incorrecto");
					ctrl.login = {};
				});
		}

		this.$onInit = function () {
			ctrl.login = {};
			
		}
	}
})
;
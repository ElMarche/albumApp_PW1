angular.module('albumApp')
.component('listUsuarios', {
	templateUrl	: 'resources/templates/usuarios/listUsuarios.html',
	controller	: function($http, $auth, $location, $localStorage) {
		var ctrl = this;

		var initUsuarios = function(){
			$http.get('api/usuarios')
				.then(function(response){
					ctrl.usuarios = response.data;
				})
				.catch(function(response){
					console.log(response);
				});
		}

		ctrl.bloquear = function(usuario){
			if(confirm('¿Quiere bloquear el acceso al usuario?')){
				usuario.estatus = 0;
				$http.patch('api/usuarios', usuario)
					.then(function(response){
						alert('Se ha bloqueado al usuario!');
						initUsuarios();
					})
					.catch(function(response){
						console.log(response.data);
					});
			}
		}

		ctrl.desbloquear = function(usuario){
			if(confirm('¿Desea desbloquear el usuario?')){
				usuario.estatus = 1;
				$http.patch('api/usuarios', usuario)
					.then(function(response){
						alert('Usuario desbloqueado!');
						initUsuarios();
					})
					.catch(function(response){
						console.log(response.data);
					});
			}
		}

		ctrl.borrar = function(id){
			if(confirm('ATENCIÓN: ¿Quiere borrar al usuario?')){
				$http.delete('api/usuarios/' + id)
					.then(function(response){
						alert('USUARIO BORRADO!');
						initUsuarios();
					})
					.catch(function(response){
						console.log(response.data);
					});
			}
		}

		ctrl.volver = function(){
			$location.path('/');
		}

		ctrl.chkIdUsuario = function(id){
			return ctrl.miUsuario.id == id;
		}

		ctrl.chkEstatus = function(estatus){
			return estatus == 1;
		}


		this.$onInit = function(){
			ctrl.miUsuario = $localStorage.usuario;
			initUsuarios();
		}
	} 
})
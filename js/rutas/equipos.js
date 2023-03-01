angular.module('albumApp')
.component('listEquipos', {
	templateUrl	: 'resources/templates/equipos/listEquipos.html',
	controller 	: function($http, $auth, $localStorage, $location) {
		var ctrl = this;

		var initEquipos = function(){
			$http.get('api/equipos')
					.then(function(response){
						ctrl.equipos = response.data;

					})
					.catch(function(){
						console.log(response);
					});
		}

		ctrl.editar = function(id) {
			$http.get('api/equipos/'+id)
				.then(function (response) {
					ctrl.nuevoEquipo = response.data;
				})
				.catch(function (response) {
					if (response.status==404) {
						alert('No se encontró el equipo con id='+id);
					} else {
						console.error(response);
					}
				});
		}

		ctrl.guardar = function () {
			if (ctrl.nuevoEquipo!=null) { // Hay un nuevo equipo para guardar / editar
				if (ctrl.nuevoEquipo.id!=null) { // Tiene id? -> Edición (patch)
					$http.patch('api/equipos/' + ctrl.nuevoEquipo.id, ctrl.nuevoEquipo)
						.then(function (response) {
							initEquipos();
							ctrl.nuevoEquipo = null;
						})
						.catch(function (response) {
							if (response.status==404) {
								alert('No se encontró el equipo con id=' + ctrl.nuevoEquipo.id);
							} else {
								console.error(response);
							}
						});
				} else { // No tiene id -> nuevo equipo (post)
					$http.post('api/equipos', ctrl.nuevoEquipo)
						.then(function (response) {
							initEquipos();
							ctrl.nuevoEquipo = null;
						})
						.catch(function (response) {
							console.error(response);
						});
				}
			}
		}

		ctrl.borrar = function(id){
			if(confirm('¿Estas seguro que quiere borrrar el equipo?')){
				$http.delete('api/equipos/'+id)
					.then(function(response){
						initEquipos();
						ctrl.nuevoEquipo = null;
					})
					.catch(function(response){
						if(response.status==404){
							alert('No se encontró equipo con id=' + id + ' o ya fué borrado');
						} else {
							console.error(response);
						}
					});
			}
		}

		ctrl.volver = function(){
			$location.path('/');
		}

		ctrl.administrador = function(){
			return $localStorage.perfil.id == 2;
		}

		this.$onInit = function(){
			initEquipos();
			ctrl.nuevoEquipo = null;
		}

	}
});
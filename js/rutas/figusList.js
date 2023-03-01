angular.module('albumApp')
.component('figusList', {
	templateUrl	: 'resources/templates/figusList.html',
	controller 	: function($http, $auth, pasaFigurita, $location, $localStorage) {
		var ctrl = this;

		var initFiguritas = function(){
			$http.get('api/figuritas')
					.then(function(response){
						ctrl.figuritas = response.data;
						if(ctrl.figuritas.length==0){
							alert('Aún no ha obtenido figuritas');
						}
					})
					.catch(function(){
						console.log(response);
					});
		}

		var initEquipos = function(){
			$http.get('api/equipos')
					.then(function(response){
						ctrl.equipos = response.data;

					})
					.catch(function(response){
						console.log(response);
					});
		}
		// function obtiene una figurita random ( cantidad = 1 ) - funcionando
		var initRandomfigurita = function(){
			$http.get('api/figuritasrandom')
				.then(function(response){
					ctrl.randomFigu = response.data;
					ctrl.randomFigu.cantidad = 1;
					guardarFiguritaUsuario();
				})
				.catch(function(response){
					console.log(response);
				});
		}
		// function que obtiene una figu random y repetida ( cantidad = 2) - funcionando
		var initRandomFiguritaRepetida = function(){
			$http.get('api/figuritasrandom')
				.then(function(response){
					ctrl.randomFigu = response.data;
					ctrl.randomFigu.cantidad = 2;
					guardarFiguritaUsuario();
				})
				.catch(function(response){
					console.log(response);
				});
		}
		// function que persiste figurita obtenida - funcionando
		var guardarFiguritaUsuario = function(){
			$http.post('api/figuritasusuarios', ctrl.randomFigu)
				.then(function(response){
					alert('Mostrar la Figurita Agregada');
					ctrl.detalle(ctrl.randomFigu);
				})
				.catch(function(response){
					if(response.status==404){
						alert('Error inesperado guardando Figurita');
					} else {
						console.log(response);
					}
				});
		}
			// función que obtiene conjunto de figuritas según el usuario - funcionando
		var initFigusPorUsuario = function(){
			$http.get('api/figuritasporusuarios')
				.then(function(response){
					ctrl.figuritas = response.data;
				})
				.catch(function(response){
					console.log(response);
				});
		}

		ctrl.crearFigurita = function(){
			ctrl.figuModel = {
				id: null,
  				nombre: null,
  				puesto: null,
				descripcion: null,
				fechanacimiento: null,
				altura: null,
				imagen: null,
				detalle: null,
				idEquipo: null
			};

			ctrl.detalle(ctrl.figuModel);
		}
		
				
		ctrl.detalle = function(f){
			//pasaFigurita.set(f);
			$localStorage.figurita = f;
			$location.path('/detalle');

		}

		ctrl.administrador = function(){
			return $localStorage.perfil.id == 2;
		}

		ctrl.obtenerFigurita = function(){
			initRandomfigurita();
		}

		ctrl.obtenerFiguritaRepetida = function(){
			initRandomFiguritaRepetida();
		}
		

		this.$onInit = function(){
			this.unEquipo = {};
			ctrl.randomFigu = {};
			ctrl.perfil = $localStorage.perfil;

			initEquipos();
			if(ctrl.administrador()){ // llama si es administrador
				initFiguritas();
			} else {
				initFigusPorUsuario();	// si no es administrador
			}

						
		}

	}
});
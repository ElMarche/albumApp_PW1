angular.module('albumApp')
.component('formCanje', {
	templateUrl	: 'resources/templates/formCanje.html',
	controller	: function($http, $auth, pasaFigurita, $location, $localStorage) {
		var ctrl = this;

		var initFigusAjenas = function(){
			$http.get('api/figuritasajenas')
				.then(function(response){
					ctrl.figuritasAjenas = response.data;
					if(ctrl.figuritasAjenas.length==0){
						alert('No hay figuritas disponibles de otros usuarios');
					}
				})
				.catch(function(response){
					console.log(response);
				});
		}

		var initFigusPropias = function(){
			$http.get('api/figuritaspropias/'+ ctrl.unaFiguAjena.idUsuario)
			.then(function(response){
				ctrl.figuritasPropias = response.data;
				if(ctrl.figuritasPropias.length==0){
					alert('No hay coincidencia de figuritas');
				}
			})
			.catch(function(response){
				console.log(response);
			});

		}

		ctrl.eligeAjena = function(fAjena){
			ctrl.unaFiguAjena = fAjena;
			ctrl.unaFiguPropia = ctrl.figuIndefinida;
			initFigusPropias();
		}

		ctrl.eligePropia = function(fPropia){
			ctrl.unaFiguPropia = fPropia;
		}

		ctrl.canjear = function(){
			if(confirm("¿Quiere canjear estas figuritas?")){
				ctrl.canje.figupropia = ctrl.unaFiguPropia.id;
				ctrl.canje.figuajena = ctrl.unaFiguAjena.id;
				ctrl.canje.idajeno = ctrl.unaFiguAjena.idUsuario;

				$http.patch('api/canjefiguritas', ctrl.canje)
					.then(function(response){
						alert('Canje realizado con éxito!');
						$location.path('/');
					})
					.catch(function(response){
						console.log(response);
					});
			}
		}

		ctrl.volver = function(){
			ctrl.unaFiguPropia = ctrl.figuIndefinida;
			ctrl.unaFiguAjena = ctrl.figuIndefinida;
			$location.path('/');
		}

		ctrl.borrar = function(){
			ctrl.unaFiguPropia = ctrl.figuIndefinida;
			ctrl.unaFiguAjena = ctrl.figuIndefinida;
			ctrl.figuritasPropias = [];
			initFigusAjenas();
		}

		ctrl.figuIndefinida = {
				id: null,
				imagen: null,
				nombre: 'ELEGIR DE LA LISTA',
				puesto: 'SIN DATOS',
				fechanacimiento: null,
				descripcion: 'SIN DATOS'
		};

		ctrl.canje = {
				figupropia: null,
				figuajena: null,
				idajeno: null,
			};

		this.$onInit = function() {
			ctrl.perfil = $localStorage.perfil;
			ctrl.unaFiguPropia = ctrl.figuIndefinida;
			ctrl.unaFiguAjena = ctrl.figuIndefinida;
			initFigusAjenas();
		}
	}
})
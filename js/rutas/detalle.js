angular.module('albumApp')
.component('detalle',{
	templateUrl	: 'resources/templates/figuritas/detalle.html',
	/*
	bindings	: {
		unaFigu : '<',
	},
	*/
	controller	: function($http, $auth, pasaFigurita, $location, $localStorage) {
		var ctrl = this;
		
		var initEquipo = function(id){
			$http.get('api/equipos/' + id)
					.then(function(response){
						ctrl.equipo = response.data;

					})
					.catch(function(response){
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

		var initFigurita = function(id){
			$http.get('api/figuritas/' + id)
				.then(function(response){
					ctrl.figurita = response.data;
				})
				.catch(function(response){
					console.log(response);
				});
		}

		ctrl.editar = function(id, elEquipo){
			$http.get('api/figuritas/'+id)
				.then(function (response) {

					response.data.fechanacimiento = response.data.fechanacimiento ? new Date(response.data.fechanacimiento+'T12:00:00Z') : null;
					ctrl.nuevaFigurita = response.data;
					ctrl.unEquipo = angular.copy(elEquipo);
				})
				.catch(function (response) {
					if (response.status==404) {
						alert('No se encontró el equipo con id='+id);
					} else {
						console.error(response);
					}
				});
		}

		ctrl.descartar = function(){
			ctrl.nuevaFigurita = null;
			ctrl.unEquipo = null;
		}
		

		ctrl.volver = function() {
			$location.path('/');
			ctrl.nuevaFigurita = null;
			ctrl.unEquipo = null;
		}



		ctrl.guardar = function(){
			if(ctrl.nuevaFigurita.id){
				id = ctrl.nuevaFigurita.id;
				ctrl.nuevaFigurita.idEquipo = ctrl.unEquipo.id;
			
				$http.patch('api/figuritas/'+id, ctrl.nuevaFigurita)
				.then(function(response){
					alert('Figurita guardada con éxito');
					$location.path('/');
				})
				.catch(function(response){
					if(response.status==404){
						alert('No se encontró Figurita con id='+id);
					} else {
						console.error(response);
					}
				});	
			} else {
				ctrl.nuevaFigurita.idEquipo = ctrl.unEquipo.id;

				$http.post('api/figuritas', ctrl.nuevaFigurita)
				.then(function(response){
					
					
					alert('Nueva Figurita agregada');
					$location.path('/')
					
				})
				.catch(function(response){
					if(response.status==404){
						alert('Error inesperado guardando figurita');
					} else {
						console.error(response);
					}
				});
			}


			
			ctrl.figurita = ctrl.nuevaFigurita;
			ctrl.nuevaFigurita = null;
			ctrl.unEquipo = null;

		}

		ctrl.borrar = function(id){
			if(confirm("¿Quiere borrar esta figurita?")){
				$http.delete('api/figuritas/'+id)
					.then(function(response){
						alert('Figurita borrada');
						$location.path('/');
					})
					.catch(function(response){
						if(response.status==404){
							alert('Error borrando Figurita con id= '+id)
						} else {
							console.error(response);
						}
					});
			}
		}

		ctrl.administrador = function(){
			return $localStorage.perfil.id == 2;
		}

		ctrl.tinymceOptions = {
        selector: "detalle",
        plugins: "textcolor, table lists code",
          toolbar: " undo redo | bold italic | alignleft aligncenter alignright alignjustify \n\
                    | bullist numlist outdent indent | forecolor backcolor table code"
    	};


		this.$onInit = function(){
			ctrl.nuevaFigurita = null;
			ctrl.unEquipo = null;
			initEquipos();
			ctrl.id = $localStorage.figurita.id;
			if(ctrl.id!=null){
				initFigurita(ctrl.id);
			}
			if($localStorage.figurita.idEquipo!=null){
				initEquipo($localStorage.figurita.idEquipo);	
			}			

		}
		
	}
})
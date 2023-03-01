angular.module('albumApp', ['ngRoute', 'satellizer', 'ngSanitize', 'ui.tinymce', 'ngStorage'])
/* factory services funcionan pero no estan en uso y fueron reemplazados por ngStorage - $localStorage */
.factory('pasaFigurita', function(){
	
	var figuGuardada = {}

	figuGuardada.set = function(figu){
		figuGuardada = figu;
	}
	figuGuardada.get = function(){
		return figuGuardada;
	}

	return figuGuardada;
})
.factory('pasaId', function(){

	var idPerfil = {}

	idPerfil.set = function(objeto){
		idPerfil = objeto;
	}
	idPerfil.get = function(){
		return idPerfil;
	}

	return idPerfil;
})
/* fin de factory services - actualmente no se utilizan - reemplazados por ngStorage */
.config(function($authProvider, $httpProvider){
	var rutaRelativa = window.location.pathname.substr(0,window.location.pathname.lastIndexOf('/'))+'/'; 
	$authProvider.baseUrl = rutaRelativa;
	$authProvider.loginUrl = 'api/login';
	$authProvider.tokenName = 'jwt';
	$httpProvider.interceptors.push(function($q, $window, $rootScope) {
		return {
			'responseError': function (rejection) {
				if(rejection.status === 401) {
					$rootScope.logut();
					$window.location.href = rutaRelativa;
				}
				return $q.reject(rejection);
			}
		};
	});
})
.config(function($routeProvider) { 
	$routeProvider
		.when("/", {
			template: '<login></login>',
			resolve: {
				necesitaLogin: saltarSiLogueado
			},
		})
		.when("/login", {
			template: '<login></login>',
			resolve: {
				necesitaLogin: saltarSiLogueado
			},
		})
		.when("/figusList", {
			template: '<figus-list></figus-list>',
			resolve: {
				necesitaLogin: loginRequerido
			},
		})
		.when("/equipos", {
			template: '<list-equipos></list-equipos>',
			resolve: {
				necesitaLogin: loginRequerido
			},
		})
		.when("/registro", {
			template: '<registro></registro>',
			resolve: {
				necesitaLogin: saltarSiLogueado
			},
		})
		.when("/detalle", {
			template: '<detalle></detalle>',
			resolve: {
				necesitaLogin: loginRequerido
			},
		})
		.when("/canje", {
			template: '<form-canje></form-canje>',
			resolve: {
				necesitaLogin: loginRequerido
			},
		})
		.when("/usuarios", {
			template: '<list-usuarios></list-usuarios>',
			resolve: {
				necesitaLogin: loginRequerido
			},
		})
		.when("/404", {
			templateUrl	: 'resources/templates/fragments/404.html',
		})
		.otherwise('/404');
		;

		function saltarSiLogueado($q, $auth, $location) {
			var diferido = $q.defer();
			if ($auth.isAuthenticated()) {
				$location.path('/figusList');
				diferido.reject(); /* Rechazo - Detener */
			} else {
				diferido.resolve();  /* Resolver - Continuar */
			}
			return diferido.promise; /* Promesa */
	    };

	    function loginRequerido($q, $auth, $location) {
			var diferido = $q.defer();
			if ($auth.isAuthenticated()) {
				diferido.resolve();
			} else {
				$location.path('/login');
				diferido.reject();
			}
			return diferido.promise;
		}
})
;
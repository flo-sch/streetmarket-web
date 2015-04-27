function StreetMarketClient (baseUrl) {
	if (!window.Request) {
		throw new Error('Missing `Request` dependency');
	}

	var client = function () {
		var _baseUrl = baseUrl,
				_format = '.json',
				_routes = {
					list: _baseUrl + '/api/v1/furnitures/' + _format,
					create: _baseUrl + '/api/v1/furnitures/create' + _format,
					find: _baseUrl + '/api/v1/furnitures/{id}' + _format,
					upload: _baseUrl + '/api/v1/furnitures/{id}/upload' + _format,
					update: _baseUrl + '/api/v1/furnitures/{id}/update' + _format,
					delete: _baseUrl + '/api/v1/furnitures/{id}/delete' + _format
				},
				_list,
				_create,
				_find,
				_upload,
				_update,
				_delete;

		_list = function (callback, scope) {
			callback = callback || function () {};
			scope = scope || window;

			var request = new Request(_routes.list, 'GET', {
				successCallback: function (response, status) {
					callback.apply(scope, [true, JSON.parse(response), status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		_create = function (data, callback, scope) {
			if (!data) {
				throw new Error('Impossible to create the furniture: undefined data.');
			}

			var requirements = ['title', 'latitude', 'longitude'];

			for (var i = 0; i < requirements.length; i++) {
				var requirement = requirements[i];

				if (!(requirement in data)) {
					throw new Error('Impossible to create the furniture: undefined ' + requirement + '.');
				}
			}

			callback = callback || function () {};
			scope = scope || window;

			var now = new moment();

			var request = new Request(_routes.create, 'POST', {
				data: JSON.stringify({
					title: data.title,
					created_at: now.format('YYYY-MM-DDTHH:mm:ssZ'),
					took_at: now.format('YYYY-MM-DDTHH:mm:ssZ'),
					longitude: data.longitude,
					latitude: data.latitude
				}),
				type: 'application/json',
				successCallback: function (response, status) {
					callback.apply(scope, [true, JSON.parse(response), status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		_find = function (id, callback, scope) {
			callback = callback || function () {};
			scope = scope || window;

			var request = new Request(_routes.find.replace('{id}', id), 'GET', {
				successCallback: function (response, status) {
					callback.apply(scope, [true, JSON.parse(response), status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		_upload = function (id, data, callback, scope) {
			if (!id) {
				throw new Error('Trying to upload a picture for an undefined `id`.');
			}
			if (!data) {
				throw new Error('Impossible to upload the furniture\'s picture: undefined data.');
			}

			var requirements = ['picture', 'filename'];

			for (var i = 0; i < requirements.length; i++) {
				var requirement = requirements[i];

				if (!(requirement in data)) {
					throw new Error('Impossible to upload the furniture\'s picture: undefined ' + requirement + '.');
				}
			}

			var formData = new FormData();
			formData.append('picture', data.picture, data.filename);

			var request = new Request(_routes.upload.replace('{id}', id), 'POST', {
				data: formData,
				successCallback: function (response, status) {
					callback.apply(scope, [true, JSON.parse(response), status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		_update = function (id, data, callback, scope) {
			if (!id) {
				throw new Error('Trying to upload a picture for an undefined `id`.');
			}
			if (!data) {
				throw new Error('Impossible to update the furniture: undefined data.');
			}

			callback = callback || function () {};
			scope = scope || window;

			var now = new moment();
			var formattedData = {
				updated_at: now.format('YYYY-MM-DDTHH:mm:ssZ')
			};

			var fields = ['title', 'updated_at', 'took_at', 'latitude', 'longitude'];

			for (var i = 0; i < fields.length; i++) {
				var field = fields[i];

				if (field in data) {
					formattedData[field] = data[field];
				}
			}

			var request = new Request(_routes.update.replace('{id}', id), 'PUT', {
				data: formattedData,
				type: 'application/json',
				successCallback: function (response, status) {
					callback.apply(scope, [true, JSON.parse(response), status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		_delete = function (id, callback, scope) {
			if (!id) {
				throw new Error('Trying to delete a picture for an undefined `id`.');
			}

			callback = callback || function () {};
			scope = scope || window;

			var request = new Request(_routes.delete.replace('{id}', id), 'DELETE', {
				successCallback: function (response, status) {
					// Response should be 204 -- without content
					callback.apply(scope, [true, null, status]);
				},
				errorCallback: function (response, status) {
					callback.apply(scope, [false, JSON.parse(response), status]);
				}
			});

			request.send();
		}

		return {
			'list': _list,
			'create': _create,
			'find': _find,
			'upload': _upload,
			'update': _update,
			'delete': _delete,
		}
	};

	return new client();
}
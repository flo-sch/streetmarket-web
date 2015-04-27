var Request = function (url, method, settings) {
	var methods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'PATCH'];

	if (!url || url.length === 0) {
		throw new Error('Request: undefined URL', url);
	}
	if (!method || methods.indexOf(method) < 0) {
		throw new Error('Request: undefined or unsupported method - ' + method);
	}

	this.url = url;
	this.method = method;
	this.type = 'text/plain;charset=UTF-8';

	this.data = {};
	this.successCallback = function () {};
	this.errorCallback = function () {};

	if (settings && typeof settings === 'object' && 'data' in settings) {
		this.data = settings.data;
	}
	if (settings && typeof settings === 'object' && 'successCallback' in settings) {
		this.successCallback = settings.successCallback;
	}
	if (settings && typeof settings === 'object' && 'errorCallback' in settings) {
		this.errorCallback = settings.errorCallback;
	}
	if (settings && typeof settings === 'object' && 'type' in settings) {
		this.type = settings.type;
	}
}

Request.prototype.send = function (username, password) {
	username = username || '';
	password = password || '';

	var Request = this;

	var xhr = new XMLHttpRequest();
	xhr.open(this.method, this.url, true, username, password);

	if (this.type) {
		xhr.setRequestHeader('Content-Type', this.type);
	}

	xhr.onreadystatechange = function (event) {
	  if (this.readyState === XMLHttpRequest.DONE) {

			if (this.status < 200) {
				Request.successCallback.apply(Request, [this.responseText, this.status]);
			} else if (this.status < 300) {
				Request.successCallback.apply(Request, [this.responseText, this.status]);
			} else if (this.status < 400) {
				Request.errorCallback.apply(Request, [this.responseText, this.status]);
			} else if (this.status < 500) {
				Request.errorCallback.apply(Request, [this.responseText, this.status]);
			}
	  }
	};

	xhr.onerror = function (error) {
		Request.errorCallback.apply(Request, [this.responseText, this.status]);
	}

	console.log('xhr send', this.data);

	xhr.send(this.data);
}
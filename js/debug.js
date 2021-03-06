'use strict';

(function ($) {
	const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

	class Ajax {
		static Call(url, method, params) {
			let xhr = new XMLHttpRequest();

			return new Promise((resolve, reject) => {
				xhr.addEventListener('load', (e) => {
					let ret = xhr.response ?? null
					if( ret instanceof String || xhr.responseType == '' || xhr.responseType == 'text' ) {
						try {
							let t = JSON.parse(xhr.responseText);
							ret = t;
						} catch(e) {
							// do nothing
						}
					}

					resolve({
						'error': false,
						'data': ret,
						'responseCode': xhr.status
					});
				});
				xhr.addEventListener('abort', (e) => {
					reject({
						'error': true,
						'msg': 'Request was cancelled',
						'responseCode': xhr.status
					});
				});
				xhr.addEventListener('error', (e) => {
					reject({
						'error': true,
						'msg': 'An error has occured',
						'responseCode': xhr.status,
						'responseStatus': xhr.statusText
					});
				});

				// TODO: handle params for querystring / postdata
				// See: https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest#submitting_forms_and_uploading_files

				xhr.open(method || 'GET', url, true);
				xhr.send();
			});
		}

		static async Get(url) {
			return await Ajax.Call(url, 'GET');
		}

		static async Post(url, data) {
			return await Ajax.Call(url, 'POST', data);
		}
	}

	class Nav {
		buildMenu() {
			Ajax.Get('/api/Debug/Nav/Get').then((resp) => {
				console.log(resp);
			}).catch((resp) => {
				console.log('error', resp);
			});
		}
	}

	let nav = null;
	$(function () {
		nav = new Nav();
		nav.buildMenu();

		Ajax.Get('/api/Debug/NonExistant/Get').then((resp) => {
			console.log('success', resp);
		}).catch((resp) => {
			console.log('error', resp);
		});
	});

})(jQuery);
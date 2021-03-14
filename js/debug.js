'use strict';

(function () {
	// Promise based setTimeout
	//const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

	function $(query) {
		return document.querySelectorAll(query);
	}

	function ajax() {
		return atomic.apply(null, arguments).then((resp) => {
			let ret = resp;

			if( resp && resp.data )
				ret = resp.data;

			if( !ret || ret.error )
				return Promise.reject(ret);

			return Promise.resolve(ret);
		}).catch((resp) => {
			let ret = resp;
			if( resp && resp.responseText ) {
				try {
					ret = JSON.parse(resp.responseText);
				} catch(e) {
					// don't replace the response text
				}
			}

			if( !ret || ret.error )
				return Promise.reject(ret);

			return Promise.resolve(ret);
		});
	}

	class PageView {
		loadingCnt = 0;

		startLoading() {
			this.loadingCnt++;

			if( this.loadingCnt >= 1 )
				$('.page-holdder > .loading')[0].classList.add('active');
		}

		endLoading() {
			this.loadingCnt--;

			if( this.loadingCnt <= 0 )
				$('.page-holdder > .loading')[0].classList.remove('active');
		}
	}

	class Nav {
		buildMenu() {
			ajax('/api/Debug/Nav/Get').then((resp) => {
				console.log('nav-success', resp);
			}).catch((resp) => {
				console.log('nav-error', resp);
			});
		}
	}

	let nav = null;
	$(function () {
		nav = new Nav();
		nav.buildMenu();
	});

})();
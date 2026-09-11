/**
 * Fanaloka Self-Help Assistant — chat UI.
 *
 * Messages live only in this page's DOM for the current view; nothing is
 * written to localStorage/sessionStorage/a database. Reload or close the
 * tab and the conversation is gone, by design.
 */
( function () {
	'use strict';

	var form     = document.getElementById( 'fsh-form' );
	var input    = document.getElementById( 'fsh-input' );
	var messages = document.getElementById( 'fsh-messages' );

	if ( ! form || ! input || ! messages ) {
		return;
	}

	messages.setAttribute( 'data-empty', fshData.i18n.placeholder );

	function appendMessage( text, role ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-msg fsh-msg-' + role;
		el.textContent = text;
		messages.appendChild( el );
		messages.scrollTop = messages.scrollHeight;
		return el;
	}

	function appendAnswer( data ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-msg fsh-msg-bot';

		if ( data.matched ) {
			var title = document.createElement( 'span' );
			title.className = 'fsh-msg-title';
			title.textContent = data.title;
			el.appendChild( title );

			var ol = document.createElement( 'ol' );
			( data.steps || [] ).forEach( function ( step ) {
				var li = document.createElement( 'li' );
				li.textContent = step;
				ol.appendChild( li );
			} );
			el.appendChild( ol );
		} else {
			el.appendChild( document.createTextNode( data.answer ) );
		}

		if ( data.alternatives && data.alternatives.length ) {
			var alt = document.createElement( 'div' );
			alt.className = 'fsh-suggestions';
			alt.textContent = fshData.i18n.alternatives + ' ' + data.alternatives.join( ', ' );
			el.appendChild( alt );
		} else if ( data.suggestions && data.suggestions.length ) {
			var sug = document.createElement( 'div' );
			sug.className = 'fsh-suggestions';
			sug.textContent = fshData.i18n.alternatives + ' ' + data.suggestions.join( ', ' );
			el.appendChild( sug );
		}

		messages.appendChild( el );
		messages.scrollTop = messages.scrollHeight;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var question = input.value.trim();
		if ( '' === question ) {
			return;
		}

		appendMessage( question, 'user' );
		input.value = '';
		input.disabled = true;

		var thinking = appendMessage( fshData.i18n.thinking, 'bot' );

		fetch( fshData.restUrl + '/ask', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': fshData.nonce
			},
			body: JSON.stringify( { question: question } )
		} )
			.then( function ( res ) {
				return res.json();
			} )
			.then( function ( data ) {
				thinking.remove();
				appendAnswer( data );
			} )
			.catch( function () {
				thinking.remove();
				appendMessage( 'Terjadi kesalahan, coba lagi.', 'bot' );
			} )
			.finally( function () {
				input.disabled = false;
				input.focus();
			} );
	} );
} )();

/**
 * Fanaloka Self-Help Assistant — floating chat widget.
 *
 * Available on every wp-admin screen. Messages live only in this page's
 * DOM for as long as the panel stays open; nothing is written to
 * localStorage/sessionStorage/a database, and the panel resets empty
 * every time it's reopened. No history is ever persisted, by design.
 */
( function () {
	'use strict';

	if ( typeof fshData === 'undefined' ) {
		return;
	}

	var widget   = document.getElementById( 'fsh-widget' );
	var bubble   = document.getElementById( 'fsh-widget-bubble' );
	var panel    = document.getElementById( 'fsh-widget-panel' );
	var closeBtn = document.getElementById( 'fsh-widget-close' );
	var form     = document.getElementById( 'fsh-widget-form' );
	var input    = document.getElementById( 'fsh-widget-input' );
	var messages = document.getElementById( 'fsh-widget-messages' );

	if ( ! widget || ! bubble || ! panel || ! form || ! input || ! messages ) {
		return;
	}

	input.placeholder = fshData.i18n.placeholder;

	function appendMessage( text, role ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-widget-msg fsh-widget-msg-' + role;
		el.textContent = text;
		messages.appendChild( el );
		messages.scrollTop = messages.scrollHeight;
		return el;
	}

	function appendAnswer( data ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-widget-msg fsh-widget-msg-bot';

		if ( data.matched ) {
			var title = document.createElement( 'span' );
			title.className = 'fsh-widget-msg-title';
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

		var extra = data.alternatives && data.alternatives.length ? data.alternatives : data.suggestions;
		if ( extra && extra.length ) {
			var sug = document.createElement( 'div' );
			sug.className = 'fsh-widget-suggestions';
			sug.textContent = fshData.i18n.alternatives + ' ' + extra.join( ', ' );
			el.appendChild( sug );
		}

		messages.appendChild( el );
		messages.scrollTop = messages.scrollHeight;
	}

	function openPanel() {
		widget.classList.add( 'is-open' );
		panel.hidden = false;

		if ( ! messages.childElementCount ) {
			appendMessage( fshData.i18n.greeting, 'bot' );
		}

		input.focus();
	}

	function closePanel() {
		widget.classList.remove( 'is-open' );
		panel.hidden = true;
	}

	bubble.addEventListener( 'click', openPanel );
	closeBtn.addEventListener( 'click', closePanel );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && ! panel.hidden ) {
			closePanel();
		}
	} );

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

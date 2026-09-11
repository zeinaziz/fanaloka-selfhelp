/**
 * Fanaloka Self-Help Assistant — floating chat widget.
 *
 * Available on every wp-admin screen. The conversation survives normal
 * wp-admin page navigation (kept in sessionStorage, scoped to this browser
 * tab) so switching screens doesn't lose it — but nothing is written to a
 * database, and clicking the close (×) button wipes it for good. Closing
 * the browser tab also clears it, since sessionStorage doesn't outlive
 * the tab. No server-side chat history exists in this plugin, by design.
 */
( function () {
	'use strict';

	if ( typeof fshData === 'undefined' ) {
		return;
	}

	var STORAGE_KEY = 'fshChatState';

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

	function loadState() {
		try {
			var raw = sessionStorage.getItem( STORAGE_KEY );
			return raw ? JSON.parse( raw ) : { open: false, log: [] };
		} catch ( err ) {
			return { open: false, log: [] };
		}
	}

	function saveState( state ) {
		try {
			sessionStorage.setItem( STORAGE_KEY, JSON.stringify( state ) );
		} catch ( err ) {
			// sessionStorage unavailable (private mode/blocked) — chat still
			// works for the current page, it just won't survive navigation.
		}
	}

	function clearState() {
		try {
			sessionStorage.removeItem( STORAGE_KEY );
		} catch ( err ) {
			// Nothing to clean up if it never saved in the first place.
		}
	}

	var state = loadState();

	function renderUserMessage( text ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-widget-msg fsh-widget-msg-user';
		el.textContent = text;
		messages.appendChild( el );
	}

	function renderBotText( text ) {
		var el = document.createElement( 'div' );
		el.className = 'fsh-widget-msg fsh-widget-msg-bot';
		el.textContent = text;
		messages.appendChild( el );
	}

	function renderBotAnswer( data ) {
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
	}

	function renderEntry( entry ) {
		if ( 'user' === entry.role ) {
			renderUserMessage( entry.text );
		} else if ( 'bot-answer' === entry.role ) {
			renderBotAnswer( entry.data );
		} else {
			renderBotText( entry.text );
		}
	}

	function pushEntry( entry ) {
		state.log.push( entry );
		saveState( state );
		renderEntry( entry );
		messages.scrollTop = messages.scrollHeight;
	}

	function openPanel() {
		widget.classList.add( 'is-open' );
		panel.hidden = false;
		state.open = true;

		if ( ! state.log.length ) {
			pushEntry( { role: 'bot', text: fshData.i18n.greeting } );
		} else {
			saveState( state );
		}

		input.focus();
	}

	function closePanel() {
		widget.classList.remove( 'is-open' );
		panel.hidden = true;
		messages.innerHTML = '';
		state = { open: false, log: [] };
		clearState();
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

		pushEntry( { role: 'user', text: question } );
		input.value = '';
		input.disabled = true;

		var thinking = document.createElement( 'div' );
		thinking.className = 'fsh-widget-msg fsh-widget-msg-bot';
		thinking.textContent = fshData.i18n.thinking;
		messages.appendChild( thinking );
		messages.scrollTop = messages.scrollHeight;

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
				pushEntry( { role: 'bot-answer', data: data } );
			} )
			.catch( function () {
				thinking.remove();
				pushEntry( { role: 'bot', text: 'Terjadi kesalahan, coba lagi.' } );
			} )
			.finally( function () {
				input.disabled = false;
				input.focus();
			} );
	} );

	// Restore an in-progress conversation after normal wp-admin page navigation.
	if ( state.open && state.log.length ) {
		widget.classList.add( 'is-open' );
		panel.hidden = false;
		state.log.forEach( renderEntry );
		messages.scrollTop = messages.scrollHeight;
	}
} )();

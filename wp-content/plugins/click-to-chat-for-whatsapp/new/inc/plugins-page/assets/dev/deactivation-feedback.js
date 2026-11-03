/**
 * Deactivation feedback modal for the Plugins screen.
 *
 * Takes over this plugin's Deactivate link. Whatever happens to the
 * feedback, the plugin still deactivates.
 */

( function htCtcDeactivationFeedbackModal () {
	'use strict';

	var cfg = window.ht_ctc_df_var;

	// How long Skip waits for this site to record the snooze before it
	// deactivates anyway. A same-site request, so normally far less.
	var SKIP_WAIT_MS = 1500;

	if ( ! cfg || ! cfg.basename || ! cfg.ajax_url || ! cfg.nonce ) {
		return;
	}

	var modal = null;
	var closeBtn = null;
	var comment = null;
	var contactOptIn = null;
	var contactLabel = null;
	var contactEmail = null;
	var emailText = null;
	var emailEdit = null;
	var committedEmail = '';
	var submitBtn = null;
	var skipBtn = null;
	var more = null;
	var snoozeSelect = null;
	var submitLabel = '';
	var defaultPlaceholder = '';

	var deactivateUrl = '';
	var lastFocused = null;
	var submitting = false;
	var navigating = false;
	var pressedOnBackdrop = false;
	var pressedWhileEditing = false;
	var pressingSubmit = false;
	var failsafeTimer = 0;
	var attempt = 0;
	var snoozeTouched = false;

	ready( init );

	/**
	 * Run fn once the DOM is parsed.
	 *
	 * @param {Function} fn Callback.
	 */
	function ready ( fn ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	/**
	 * Find the pieces, and only then take over the Deactivate link.
	 */
	function init () {
		try {
			modal = document.getElementById( 'ht-ctc-df-modal' );
			if ( ! modal ) {
				return;
			}

			closeBtn = document.getElementById( 'ht-ctc-df-close' );
			comment = document.getElementById( 'ht-ctc-df-comment' );
			submitBtn = document.getElementById( 'ht-ctc-df-submit' );
			skipBtn = document.getElementById( 'ht-ctc-df-skip' );

			// The contact opt-in: always shown, unticked on every open.
			contactOptIn = document.getElementById( 'ht-ctc-df-contact-opt-in' );
			contactLabel = document.getElementById( 'ht-ctc-df-contact-label' );

			// The email to contact them at. With an account email it shows as
			// text plus a pencil; without one, the field shows straight away.
			contactEmail = document.getElementById( 'ht-ctc-df-contact-email' );
			emailText = document.getElementById( 'ht-ctc-df-email-text' );
			emailEdit = document.getElementById( 'ht-ctc-df-email-edit' );

			// The "What is sent" panel and its snooze choice.
			more = document.getElementById( 'ht-ctc-df-more' );
			snoozeSelect = document.getElementById( 'ht-ctc-df-snooze' );

			if ( ! comment || ! submitBtn || ! skipBtn ) {
				return;
			}

			defaultPlaceholder = comment.getAttribute( 'placeholder' ) || '';

			submitLabel = submitBtn.textContent;

			bindModal();

			// Delegated: the plugin search re-renders the list rows over AJAX.
			document.addEventListener( 'click', onDeactivateClick );

			// Back navigation can restore this page from the bfcache after deactivate() started.
			window.addEventListener( 'pageshow', function onPageShow ( event ) {
				if ( event.persisted ) {
					reset();
				}
			} );
		} catch {
			// Leave the Deactivate link to core.
		}
	}

	/**
	 * The deactivation URL of this plugin's row, if the click landed on its
	 * Deactivate link.
	 *
	 * @param {EventTarget} target Click target.
	 * @return {string} The URL, or '' when the click is not ours to take.
	 */
	function deactivateUrlFrom ( target ) {
		var link = target && target.closest ? target.closest( 'a[href]' ) : null;
		if ( ! link ) {
			return '';
		}

		// Exact basename: several plugins can share a name or slug prefix.
		var row = link.closest( 'tr[data-plugin]' );
		if ( ! row || cfg.basename !== row.getAttribute( 'data-plugin' ) ) {
			return '';
		}

		// The URL itself decides, not the markup around it: core's own
		// plugins.php deactivate request for this plugin, with its nonce.
		// Anything else is left to the browser to follow as it would.
		var url;
		try {
			url = new URL( link.href, window.location.href );
		} catch {
			return '';
		}

		var params = url.searchParams;
		if (
			url.origin !== window.location.origin ||
			url.pathname !== window.location.pathname ||
			'deactivate' !== params.get( 'action' ) ||
			cfg.basename !== params.get( 'plugin' ) ||
			! params.get( '_wpnonce' )
		) {
			return '';
		}

		return url.href;
	}

	/**
	 * Intercept a plain left click on Deactivate.
	 *
	 * @param {MouseEvent} event Click event.
	 */
	function onDeactivateClick ( event ) {
		if ( event.defaultPrevented || 0 !== event.button ) {
			return;
		}

		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}

		// Not this plugin's deactivate request: the browser follows the link as usual.
		var url = deactivateUrlFrom( event.target );
		if ( ! url ) {
			return;
		}

		// Another script can move the dialog out of the document after init().
		if ( ! modal.isConnected ) {
			return;
		}

		deactivateUrl = url;

		// Take the click over only once the dialog is up. Short of that the
		// browser follows the link, and the plugin deactivates as core intended.
		try {
			openModal();
		} catch {
			return;
		}

		event.preventDefault();
	}

	/**
	 * Wire the dialog controls.
	 */
	function bindModal () {
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', cancel );
		}

		skipBtn.addEventListener( 'click', skip );
		submitBtn.addEventListener( 'click', submit );

		// Pressing Send blurs the email field first. Flag the press (Safari
		// does not focus buttons, so relatedTarget alone is not enough) for
		// the length of this event only.
		submitBtn.addEventListener( 'pointerdown', function onSubmitPress () {
			pressingSubmit = true;
			window.setTimeout( function clearSubmitPress () {
				pressingSubmit = false;
			}, 0 );
		} );

		// Backdrop click outside dialog. The press must start there too, so a
		// text selection dragged out of the textarea does not close it.
		modal.addEventListener( 'pointerdown', function onBackdropPress ( event ) {
			pressedOnBackdrop = event.target === modal;
		} );
		modal.addEventListener( 'click', function onBackdropClick ( event ) {
			if ( event.target === modal && pressedOnBackdrop ) {
				cancel();
			}
			pressedOnBackdrop = false;
		} );

		// On the document: a click on the dialog's plain text moves focus to
		// <body>, and Escape and Tab must still work from there.
		document.addEventListener( 'keydown', onKeydown );

		// The dialog body scrolls: bring the opened panel into view.
		if ( more && more.scrollIntoView ) {
			more.addEventListener( 'toggle', function onMoreToggle () {
				if ( more.open ) {
					more.scrollIntoView( { block: 'nearest' } );
				}
			} );
		}

		if ( emailEdit ) {
			emailEdit.addEventListener( 'click', onEmailEdit );
		}

		// While the field is open, a click on the label's words only ends the
		// edit: it must not also untick the box the pencil just ticked. A click
		// on the box itself still toggles it.
		if ( contactLabel ) {
			contactLabel.addEventListener( 'pointerdown', function onContactPress () {
				pressedWhileEditing = isEditingEmail();
			} );
			contactLabel.addEventListener( 'click', function onContactClick ( event ) {
				if ( pressedWhileEditing ) {
					event.preventDefault();
				}
				pressedWhileEditing = false;
			} );
		}

		if ( contactEmail ) {
			// Typing an address means wanting a reply, as the pencil does: tick
			// the box, so a filled-in address is never left out by an unticked one.
			contactEmail.addEventListener( 'input', function onEmailInput () {
				contactEmail.setCustomValidity( '' );
				if ( contactOptIn && contactEmail.value.trim() ) {
					contactOptIn.checked = true;
				}
			} );
			contactEmail.addEventListener( 'blur', onEmailBlur );
			contactEmail.addEventListener( 'keydown', onEmailKeydown );
		}

		if ( snoozeSelect ) {
			snoozeSelect.addEventListener( 'change', function onSnoozeChange () {
				snoozeTouched = true;
			} );
		}

		toArray( modal.querySelectorAll( 'input[name="ht_ctc_df_reason"]' ) )
			.forEach( function bindRadio ( radio ) {
				radio.addEventListener( 'change', onReasonChange );
			} );
	}

	/**
	 * Pencil: swap the shown address for the field. Wanting another address
	 * means wanting to be contacted, so it also ticks the box.
	 *
	 * @param {MouseEvent} event Click event.
	 */
	function onEmailEdit ( event ) {
		event.preventDefault();
		showEmailField( true );

		if ( contactOptIn ) {
			contactOptIn.checked = true;
		}

		contactEmail.focus();
		contactEmail.select();
	}

	/**
	 * Show the email field, or the account email as text with its pencil.
	 * Without an account email there is no text: the field always shows.
	 *
	 * @param {boolean} editing Whether to show the field.
	 */
	function showEmailField ( editing ) {
		if ( ! contactEmail || ! emailText || ! emailEdit ) {
			return;
		}

		contactEmail.hidden = ! editing;
		emailEdit.hidden = editing;
	}

	/**
	 * Whether the field is open for editing (it can be closed only when
	 * there is an account email to show instead).
	 *
	 * @return {boolean} True while editing.
	 */
	function isEditingEmail () {
		return !! ( emailEdit && contactEmail && ! contactEmail.hidden );
	}

	/**
	 * Leave the field (click away): a valid address becomes the shown one;
	 * an empty or invalid one falls back to the last good address, so the
	 * field never stays stuck on a warning. Not when Send took the focus:
	 * submit() checks the typed address, so the one being replaced is never
	 * sent in its place.
	 *
	 * @param {FocusEvent} event Blur event.
	 */
	function onEmailBlur ( event ) {
		if ( pressingSubmit || event.relatedTarget === submitBtn ) {
			return;
		}

		if ( isEditingEmail() ) {
			closeEmailField( true );
		}
	}

	/**
	 * Keep the typed address and show it as text again. Empty, or invalid
	 * with fallBack, restores the last good address.
	 *
	 * @param {boolean} fallBack Restore the last address instead of warning.
	 * @return {boolean} False when the address is invalid and the field stays open.
	 */
	function closeEmailField ( fallBack ) {
		var value = contactEmail.value.trim();

		if ( value && ! contactEmail.checkValidity() ) {
			if ( ! fallBack ) {
				if ( contactEmail.reportValidity ) {
					contactEmail.reportValidity();
				}
				return false;
			}
			value = '';
		}

		if ( value ) {
			committedEmail = value;
			emailText.textContent = value;
		}

		contactEmail.value = committedEmail;
		contactEmail.setCustomValidity( '' );
		showEmailField( false );
		return true;
	}

	/**
	 * Enter keeps the address, Escape undoes the edit. Escape stops here so
	 * the document's handler does not also close the dialog.
	 *
	 * @param {KeyboardEvent} event Key event.
	 */
	function onEmailKeydown ( event ) {
		if ( ! isEditingEmail() ) {
			return;
		}

		if ( 'Enter' === event.key ) {
			event.preventDefault();

			// Enter is a deliberate "use this", so an invalid address gets the
			// browser's message instead of being swapped back.
			if ( closeEmailField( false ) ) {
				emailEdit.focus();
			}
			return;
		}

		if ( 'Escape' === event.key ) {
			event.preventDefault();
			event.stopPropagation();
			contactEmail.value = committedEmail;
			showEmailField( false );
			emailEdit.focus();
		}
	}

	/**
	 * Whether the comment holds any text.
	 *
	 * @return {boolean} True when non-empty.
	 */
	function hasComment () {
		return !! comment && comment.value.trim().length > 0;
	}

	/**
	 * Handle reason change. The comment box stays put, so the dialog does not
	 * jump; only its prompt follows the reason.
	 *
	 * @param {Event} event Change event from radio.
	 */
	function onReasonChange ( event ) {
		suggestSnooze( event.target.value );
		comment.setAttribute( 'placeholder', event.target.getAttribute( 'data-placeholder' ) || defaultPlaceholder );
	}

	/**
	 * Default the snooze from the reason: longer while testing. A length the
	 * user picked themselves is left alone.
	 *
	 * @param {string} reason Reason key, or '' for none.
	 */
	function suggestSnooze ( reason ) {
		if ( ! snoozeSelect || snoozeTouched || ! cfg.snooze ) {
			return;
		}

		snoozeSelect.value = String( 'temporary' === reason ? cfg.snooze.temporary : cfg.snooze.default );
	}

	/**
	 * Snooze length to send, in days ('' lets the server use its default).
	 *
	 * @return {string} Days.
	 */
	function snoozeDays () {
		return snoozeSelect ? snoozeSelect.value : '';
	}

	/**
	 * Open dialog and move focus inside.
	 */
	function openModal () {
		lastFocused = document.activeElement;
		modal.hidden = false;
		document.body.classList.add( 'ht-ctc-df-open' );

		clearForm();

		var first = modal.querySelector( 'input[name="ht_ctc_df_reason"]' );
		if ( first ) {
			first.focus();
		}
	}

	/**
	 * Hide the dialog and hand focus back.
	 */
	function closeModal () {
		modal.hidden = true;
		document.body.classList.remove( 'ht-ctc-df-open' );

		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}
	}

	/**
	 * Put the form back to how it opens: no reason, no text, no contact opt-in.
	 * Called on the way in, so the dialog starts blank however the last one
	 * ended — including an exit that never ran closeModal().
	 */
	function clearForm () {
		var checked = checkedReason();
		if ( checked ) {
			checked.checked = false;
		}
		if ( comment ) {
			comment.value = '';
			comment.setAttribute( 'placeholder', defaultPlaceholder );
		}

		if ( contactOptIn ) {
			contactOptIn.checked = false;
		}

		if ( contactEmail ) {
			committedEmail = contactEmail.getAttribute( 'data-default' ) || '';
			contactEmail.value = committedEmail;
			contactEmail.setCustomValidity( '' );
		}
		if ( emailText ) {
			emailText.textContent = committedEmail;
		}
		showEmailField( false );

		if ( more ) {
			more.open = false;
		}
		snoozeTouched = false;
		suggestSnooze( '' );
	}

	/**
	 * The chosen reason radio, if any.
	 *
	 * @return {?HTMLInputElement} Checked radio or null.
	 */
	function checkedReason () {
		return modal.querySelector( 'input[name="ht_ctc_df_reason"]:checked' );
	}

	/**
	 * Close without deactivating.
	 */
	function cancel () {
		// While feedback is sending, the dialog stays up until the reply or the
		// failsafe deactivates. A navigation does not lock it: Escape is the
		// browser's Stop button and kills it without telling us.
		if ( submitting ) {
			return;
		}

		// Dismissing ends the attempt. reset() also drops the pending failsafe
		// and makes a late reply a no-op, so neither deactivates after a close.
		reset();
	}

	/**
	 * End the current attempt: cancel the failsafe, ignore any late reply,
	 * re-enable the buttons and close.
	 */
	function reset () {
		attempt++;
		window.clearTimeout( failsafeTimer );
		submitting = false;
		navigating = false;
		setButtonsDisabled( false );
		submitBtn.textContent = submitLabel;

		if ( ! modal.hidden ) {
			closeModal();
		}
	}

	/**
	 * Enable or disable every dialog button.
	 *
	 * @param {boolean} disabled Whether to disable.
	 */
	function setButtonsDisabled ( disabled ) {
		submitBtn.disabled = disabled;
		skipBtn.disabled = disabled;

		if ( closeBtn ) {
			closeBtn.disabled = disabled;
		}
	}

	/**
	 * While the dialog is open: Escape cancels, Tab stays inside.
	 *
	 * @param {KeyboardEvent} event Key event.
	 */
	function onKeydown ( event ) {
		if ( modal.hidden ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			event.preventDefault();
			cancel();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var items = focusable();
		if ( ! items.length ) {
			return;
		}

		var first = items[ 0 ];
		var last = items[ items.length - 1 ];

		if ( ! modal.contains( document.activeElement ) ) {
			event.preventDefault();
			( event.shiftKey ? last : first ).focus();
		} else if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	/**
	 * Visible, enabled controls inside the dialog.
	 *
	 * @return {Array} Focusable elements.
	 */
	function focusable () {
		return toArray( modal.querySelectorAll( 'button, summary, a[href], input, textarea, select' ) )
			.filter( function isReachable ( node ) {
				return ! node.disabled && null !== node.offsetParent;
			} );
	}

	/**
	 * Array from NodeList helper.
	 *
	 * @param {NodeList} list Nodes.
	 * @return {Array} Array of nodes.
	 */
	function toArray ( list ) {
		return Array.prototype.slice.call( list );
	}

	/**
	 * Submit feedback and deactivate.
	 */
	function submit () {
		if ( submitting ) {
			return;
		}

		// Already answered and on the way out (the browser may have stopped
		// that navigation): go again, without sending a second time.
		if ( navigating ) {
			deactivate();
			return;
		}

		// No reason but a comment: send the comment on its own (the server
		// files it as "comment_only"). Neither a reason nor a comment: a skip.
		var checked = checkedReason();
		var reason = checked ? checked.value : '';
		if ( ! reason && ! hasComment() ) {
			skip();
			return;
		}

		var wantsContact = !! ( contactOptIn && contactOptIn.checked );

		// Opted in to be contacted, but the address is empty or not an email:
		// say so at the field, and send nothing until it is fixed or unticked.
		var badEmail = !! contactEmail &&
			( ! contactEmail.value.trim() || ! contactEmail.checkValidity() );

		if ( wantsContact && badEmail ) {
			showEmailField( true );
			contactEmail.focus();
			if ( ! contactEmail.value.trim() && cfg.email_required ) {
				contactEmail.setCustomValidity( cfg.email_required );
			}
			if ( contactEmail.reportValidity ) {
				contactEmail.reportValidity();
			}
			return;
		}

		// Armed before anything else can fail, so the worst case is a wait and
		// then deactivation, never a dialog left disabled with nothing pending.
		var mine = ++attempt;
		failsafeTimer = window.setTimeout( deactivate, cfg.failsafe_ms || 4500 );

		submitting = true;
		setButtonsDisabled( true );

		if ( cfg.sending_label ) {
			submitBtn.textContent = cfg.sending_label;
		}

		var body = new FormData();
		body.append( 'action', cfg.action );
		body.append( 'nonce', cfg.nonce );
		body.append( 'reason', reason );
		body.append( 'comment', comment.value );
		body.append( 'is_contact_opt_in', wantsContact ? '1' : '0' );
		body.append( 'contact_email', wantsContact && contactEmail ? contactEmail.value.trim() : '' );
		body.append( 'snooze_days', snoozeDays() );

		postFeedback( body, function onPosted () {
			// A reset() since this request started owns the dialog now.
			if ( mine !== attempt ) {
				return;
			}

			deactivate();
		} );
	}

	/**
	 * Send the feedback to admin-ajax. keepalive lets the request finish
	 * after the page navigates away.
	 *
	 * @param {FormData} body Request payload.
	 * @param {Function} done Called on success and failure alike.
	 */
	function postFeedback ( body, done ) {
		try {
			if ( window.fetch ) {
				window.fetch( cfg.ajax_url, {
					method: 'POST',
					body: body,
					credentials: 'same-origin',
					keepalive: true,
				} )
					.then( done, done );
				return;
			}

			var req = new XMLHttpRequest();
			req.open( 'POST', cfg.ajax_url, true );
			req.withCredentials = true;
			req.onloadend = done;
			req.send( body );
		} catch {
			done();
		}
	}

	/**
	 * Skip: deactivate without feedback, after this site records the snooze
	 * the dialog promises. It waits for that reply (at most SKIP_WAIT_MS):
	 * leaving at once let the deactivation finish first, and then this
	 * plugin was no longer loaded to handle the snooze request.
	 */
	function skip () {
		if ( submitting ) {
			return;
		}

		// Already on the way out: go again, without asking twice.
		if ( navigating ) {
			deactivate();
			return;
		}

		var mine = ++attempt;
		failsafeTimer = window.setTimeout( deactivate, SKIP_WAIT_MS );

		submitting = true;
		setButtonsDisabled( true );

		var body = new FormData();
		body.append( 'action', cfg.action );
		body.append( 'nonce', cfg.nonce );
		body.append( 'is_skip', '1' );
		body.append( 'snooze_days', snoozeDays() );

		postFeedback( body, function onSnoozed () {
			// A reset() since this request started owns the dialog now.
			if ( mine !== attempt ) {
				return;
			}

			deactivate();
		} );
	}

	/**
	 * Navigate to deactivation URL.
	 */
	function deactivate () {
		if ( ! deactivateUrl ) {
			reset();
			return;
		}

		// End the attempt: the failsafe and a late reply must not navigate again.
		attempt++;
		window.clearTimeout( failsafeTimer );

		// Navigate first: no line below may keep this from running.
		navigating = true;
		submitting = false;
		window.location.href = deactivateUrl;

		// The browser can stop that navigation and tell us nothing — Escape is
		// its Stop button — so leave the dialog usable rather than sealed:
		// Skip or Send then navigate again.
		setButtonsDisabled( false );
		submitBtn.textContent = submitLabel;
	}
} )();

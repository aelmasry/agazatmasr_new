/**
 * Animations and entry effects for the live preview — played the way the live site
 * plays them, so the two can't drift:
 *  - CSS: the text HT_CTC_Hooks::animation_styles() adds via HT_CTC_Animations
 *    (new/inc/commons/class-ht-ctc-animations.php) — base rule + the selected effect.
 *  - Classes and timing: app.dev.js — on the element holding greetings, badge and style
 *    (the live .ht-ctc-chat; here the preview root): entry classes on show, then
 *    ht_ctc_animation + ht_ctc_an_<type> 1200ms later (120ms without an entry effect).
 *    A replay is remove → reflow → add and the classes come off when the animation ends,
 *    both as its playAnimation() does — so only a load, a field change or a demo click plays.
 */
import { getSafeProperty } from '../core/Utils.js';

// an_type → keyframes + class rule (HT_CTC_Animations::<type>()).
const ANIMATIONS = {
	bounce: '@keyframes bounce{from,20%,53%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1);transform:translate3d(0,0,0)}40%,43%{animation-timing-function:cubic-bezier(0.755,0.05,0.855,0.06);transform:translate3d(0,-30px,0) scaleY(1.1)}70%{animation-timing-function:cubic-bezier(0.755,0.05,0.855,0.06);transform:translate3d(0,-15px,0) scaleY(1.05)}80%{transition-timing-function:cubic-bezier(0.215,0.61,0.355,1);transform:translate3d(0,0,0) scaleY(0.95)}90%{transform:translate3d(0,-4px,0) scaleY(1.02)}}.ht_ctc_an_bounce{animation-name:bounce;transform-origin:center bottom}',
	flash: '@keyframes flash{from,50%,to{opacity:1}25%,75%{opacity:0}}.ht_ctc_an_flash{animation-name:flash}',
	pulse: '@keyframes pulse{from{transform:scale3d(1,1,1)}50%{transform:scale3d(1.05,1.05,1.05)}to{transform:scale3d(1,1,1)}}.ht_ctc_an_pulse{animation-name:pulse;animation-timing-function:ease-in-out}',
	heartBeat: '@keyframes heartBeat{0%{transform:scale(1)}14%{transform:scale(1.3)}28%{transform:scale(1)}42%{transform:scale(1.3)}70%{transform:scale(1)}}.ht_ctc_an_heartBeat{animation-name:heartBeat;animation-duration:calc(1s * 1.3);animation-timing-function:ease-in-out}',
	flip: '@keyframes flip{from{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,0) rotate3d(0,1,0,-360deg);animation-timing-function:ease-out}40%{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,150px) rotate3d(0,1,0,-190deg);animation-timing-function:ease-out}50%{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,150px) rotate3d(0,1,0,-170deg);animation-timing-function:ease-in}80%{transform:perspective(400px) scale3d(.95,.95,.95) translate3d(0,0,0) rotate3d(0,1,0,0deg);animation-timing-function:ease-in}to{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,0) rotate3d(0,1,0,0deg);animation-timing-function:ease-in}}.ht_ctc_an_flip{backface-visibility:visible;animation-name:flip}',
};

// show_effect → keyframes + class rule. Corner isn't here: on the live site jQuery's show( 150 )
// runs with it (app.dev.js display()), which the preview can't match — its field note (notes.js) says so.
const ENTRY_EFFECTS = {
	center: '@keyframes center{from{transform:scale(0);}to{transform:scale(1);}}.ht_ctc_an_entry_center{animation: center .25s;}',
};

// One preview widget per page, so module state: the <style>, the widget's classes,
// the widget its end listener is on, the load timer.
let style = null;
let current = [];
let bound = null;
let timer = 0;

/**
 * Swaps the widget's animation classes. Re-adding a class it already had needs a
 * reflow in between to restart it (app.dev.js playAnimation); new ones start on their own.
 *
 * @param {HTMLElement} widget
 * @param {string[]}    classes
 */
const setClasses = ( widget, classes ) => {
	widget.classList.remove( ...current );
	if ( classes.some( ( name ) => current.includes( name ) ) ) {
		void widget.offsetWidth;
	}
	widget.classList.add( ...classes );
	current = classes;
};

/**
 * Which play a changed field triggers, or '' for any other field.
 *
 * @param {string} name e.g. 'ht_ctc_othersettings[an_delay]'
 * @returns {''|'animation'|'entry'}
 */
export const animationKindFor = ( name ) => {
	if ( name.startsWith( 'ht_ctc_othersettings[an_' ) ) { return 'animation'; }
	if ( name.startsWith( 'ht_ctc_othersettings[show_effect' ) ) { return 'entry'; }
	return '';
};

/**
 * Plays on the preview widget — its root, which holds greetings, badge and style.
 *
 * @param {HTMLElement} widget
 * @param {'load'|'animation'|'entry'} kind 'load' = both, in the live site's page-load order.
 * @param {{ get: (group: string, key: string) => * }} values FormValues instance
 * @param {boolean} onlyIfChanged Skip when the CSS is unchanged — a blur's 'change' after 'input'.
 */
export const animate = ( widget, kind, values, onlyIfChanged = false ) => {
	const read = ( key ) => String( values.get( 'ht_ctc_othersettings', key ) ?? '' );
	const effect = read( 'show_effect' );
	const type = read( 'an_type' );
	const entryRule = getSafeProperty( ENTRY_EFFECTS, effect );
	const animationRule = getSafeProperty( ANIMATIONS, type );

	// Same CSS as the live page, entry block first (HT_CTC_Animations::entry(), then ::animations()).
	// Written only when the settings change; a replay leaves the stylesheet alone.
	let css = '';
	if ( entryRule ) {
		css += '.ht_ctc_entry_animation{animation-duration:1s;animation-fill-mode:both;' +
			`animation-delay:0s;animation-iteration-count:1;}${entryRule}`;
	}
	if ( animationRule ) {
		const delay = Number( read( 'an_delay' ) );
		const iterations = Number( read( 'an_itr' ) );
		css += '.ht_ctc_animation{animation-duration:1s;animation-fill-mode:both;' +
			`animation-delay:${delay > 0 ? delay : 0}s;` +
			`animation-iteration-count:${iterations > 0 ? iterations : 1};}${animationRule}`;
	}
	if ( ! style ) { style = document.head.appendChild( document.createElement( 'style' ) ); }
	if ( style.textContent !== css ) {
		style.textContent = css;
	} else if ( onlyIfChanged ) {
		return;
	}

	// The classes come off when the animation ends — or is cut short by the preview being
	// hidden (save toast, toggle) — so showing the preview again doesn't replay it. A cancel
	// from a restart (remove → reflow → add) happens while it's rendered, so that one stays.
	if ( bound !== widget ) {
		const clear = ( event ) => {
			if ( event.target !== widget ) { return; }
			if ( event.type === 'animationcancel' ) {
				if ( widget.getClientRects().length ) { return; }
				clearTimeout( timer );
			}
			widget.classList.remove( ...current );
			current = [];
		};
		widget.addEventListener( 'animationend', clear );
		widget.addEventListener( 'animationcancel', clear );
		bound = widget;
	}

	const entryClasses = entryRule ? [ 'ht_ctc_entry_animation', `ht_ctc_an_entry_${effect}` ] : [];
	const animationClasses = animationRule ? [ 'ht_ctc_animation', `ht_ctc_an_${type}` ] : [];

	clearTimeout( timer );
	if ( kind === 'animation' ) {
		setClasses( widget, animationClasses );
		return;
	}

	setClasses( widget, entryClasses );
	if ( kind === 'load' && animationClasses.length ) {
		// app.dev.js ht_ctc_things(): added on top of the entry classes, 1200ms after any entry
		// effect (Corner too — the live widget still has ht_ctc_entry_animation), else 120ms.
		timer = setTimeout( () => {
			widget.classList.add( ...animationClasses );
			current = [ ...current, ...animationClasses ];
		}, [ '', 'no-show-effects' ].includes( effect ) ? 120 : 1200 );
	}
};

// (function ($) {
// 	'use strict';

/**
 * All of the code for your public-facing JavaScript source
 * should reside in this file.
 *
 * Note: It has been assumed you will write jQuery code here, so the
 * $ function reference has been prepared for usage within the scope
 * of this function.
 *
 * This enables you to define handlers, for when the DOM is ready:
 *
 * $(function() {
 *
 * });
 *
 * When the window is loaded:
 *
 * $( window ).load(function() {
 *
 * });
 *
 * ...and/or other possibilities.
 *
 * Ideally, it is not considered best practise to attach more than a
 * single DOM-ready or window-load handler for a particular page.
 * Although scripts in the WordPress core, Plugins and Themes may be
 * practising this, we should strive to set a better example in our own work.
 */

// })(jQuery);

console.log('hello from script')
tailwind.config = {
	prefix: 'bb-',
	corePlugins: {
		preflight: false,
	},
	theme: {
		spacing: {
			'4': '4px',
			'8': '8px',
			'10': '10px',
			'12': '12px',
			'16': '16px',
			'24': '24px',
			'30': '30px',
			'60': '60px',
		}
	}
}
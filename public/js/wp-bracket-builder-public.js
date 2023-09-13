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

// const plugin = require('tailwindcss/plugin')
// The Tailwind configuration
// Most of the values are overrides to narrow the styling options
// Add new values here
tailwind.config = {
	prefix: 'tw-',
	darkMode: 'class',
	corePlugins: {
		preflight: false,
	},
	theme: {
		extend: {
			opacity: {
				'15': '0.15',
			},
		},
		spacing: {
			'0': '0px',
			'1': '1px',
			'2': '2px',
			'4': '4px',
			'8': '8px',
			'10': '10px',
			'11': '11px',
			'12': '12px',
			'14': '14px',
			'15': '15px',
			'16': '16px',
			'20': '20px',
			'24': '24px',
			'30': '30px',
			'40': '40px',
			'50': '50px',
			'60': '60px',
			'80': '80px',
			'section': '1160px',
		},
		colors: {
			'transparent': 'transparent',
			'black': '#000',
			'off-black': '#000225',
			'white': '#fff',
			'green': '#05FF3C',
			'dark-green': '#00440F',
			'blue': '#2137ff',
			'dark-blue': '#0D1454',
			'dd-blue': '#010433',
			'yellow': '#F8E11A',
			'red': '#FF456D',
		},
		borderRadius: {
			'4': '4px',
			'8': '8px',
			'16': '16px',
		},
		fontSize: {
			'10': '10px',
			'12': '12px',
			'16': '16px',
			'20': '20px',
			'24': '24px',
			'30': '30px',
			'36': '36px',
			'32': '32px',
			'48': '48px',
			'60': '60px',
			'64': '64px',
			'80': '80px',
		},
		fontWeight: {
			'500': '500',
			'600': '600',
			'700': '700',
		},
		fontFamily: {
			'sans': ['Clash Display', 'sans-serif'],
		}
	},
	plugins: [
		function ({ addUtilities, addComponents, e, prefix, config }) {
			const individualBorderStyles = {
				'.border-t-none': {
					'border-top-style': 'none',
				},
				'.border-r-none': {
					'border-right-style': 'none',
				},
				'.border-b-none': {
					'border-bottom-style': 'none',
				},
				'.border-l-none': {
					'border-left-style': 'none',
				},
				'.border-x-none': {
					'border-left-style': 'none',
					'border-right-style': 'none',
				},
				'.border-y-none': {
					'border-top-style': 'none',
					'border-bottom-style': 'none',
				},
				'.border-b-solid': {
					'border-bottom-style': 'solid',
				},
			}
			addUtilities(individualBorderStyles)
		},
		function ({ addUtilities }) {
			const hideScrollbar = {
				'.no-scrollbar::-webkit-scrollbar': {
					display: 'none',
				},
				'.no-scrollbar': {
					'-ms-overflow-style': 'none',
					'scrollbar-width': 'none',
				}
			}
			addUtilities(hideScrollbar)
		}
	]
}
import React from 'react';
import App from "./App";
import { render } from '@wordpress/element';
import * as Sentry from '@sentry/react';
import { OverlayUrlThemeMap } from './preview/Gallery';
import { BracketRes } from './brackets/shared/api/types/bracket';
import { bracketBuilderStore } from './brackets/shared/app/store';
import { Provider } from 'react-redux';

interface WpbbAjaxObj {
	page: string;
	nonce: string;
	rest_url: string;
	tournament: BracketRes;
	bracket_url_theme_map: OverlayUrlThemeMap;
	css_url: string;
	bracket_product_archive_url: string;
	gallery_images: any;
	color_options: any;
	sentry_env: string;
	sentry_dsn: string;
	post: any;
}

declare var wpbb_ajax_obj: WpbbAjaxObj;
console.log('wpbb_ajax_obj', wpbb_ajax_obj)

/**
 * Import the stylesheet for the plugin.
 */
import './style/main.scss';
const sentryEnv = wpbb_ajax_obj.sentry_env
const sentryDsn = wpbb_ajax_obj.sentry_dsn

if (sentryDsn) {
	// Init Sentry
	Sentry.init({
		environment: sentryEnv || 'production',
		dsn: sentryDsn,
		integrations: [
			new Sentry.BrowserTracing({
				// Set `tracePropagationTargets` to control for which URLs distributed tracing should be enabled
				tracePropagationTargets: ["localhost", /^https:\/\/backmybracket\.com/],
			}),
			new Sentry.Replay(),
		],
		// Performance Monitoring
		tracesSampleRate: 1.0, // Capture 100% of the transactions, reduce in production!
		// Session Replay
		replaysSessionSampleRate: 0.1, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
		replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
	});
}

// Dynamically render components to avoid loading unused modules
const Settings = React.lazy(() => import('./brackets/AdminTemplateBuilder/Settings'))
const UserBracket = React.lazy(() => import('./brackets/UserBracketBuilder/UserBracket/UserBracket'))
const Gallery = React.lazy(() => import('./preview/Gallery'))
const Options = React.lazy(() => import('./brackets/UserTemplateBuilder/UserTemplateBuilder'))
const BracketManager = React.lazy(() => import('./brackets/BracketManager/BracketManager'))

// Get the wpbb_ajax_obj from the global scope

renderSettings(wpbb_ajax_obj)
renderPlayTournamentBuilder(wpbb_ajax_obj)
renderPreview(wpbb_ajax_obj)
renderOptionsTree()
bracketManager()

function renderSettings(wpbb_ajax_obj: WpbbAjaxObj) {
	const page = wpbb_ajax_obj.page

	if (page === 'settings') {
		// Render the App component into the DOM
		render(<App><Settings /></App >, document.getElementById('wpbb-admin-panel'));
	}
}

function renderOptionsTree() {
	const optionsBuilder = document.getElementById('wpbb-bracket-option-preview')
	if (optionsBuilder) {
		render(<App><Provider store={bracketBuilderStore}><Options /></Provider></App >, optionsBuilder);
	}
}

function bracketManager() {
	const bracketMangerBuilder = document.getElementById('wpbb-bracket-manager-preview')
	if (bracketMangerBuilder) {
		render(<App><BracketManager /></App >, bracketMangerBuilder);
	}
}

function renderPlayTournamentBuilder(wpbb_ajax_obj: WpbbAjaxObj) {
	const builderDiv = document.getElementById('wpbb-play-tournament-builder')
	const {
		tournament,
		bracket_product_archive_url,
		css_url,
	} = wpbb_ajax_obj

	if (builderDiv && tournament) {
		console.log('rendering play tournament builder')
		render(<App><Provider store={bracketBuilderStore}><UserBracket bracketStylesheetUrl={css_url} bracketRes={tournament} apparelUrl={bracket_product_archive_url} canPick /> </Provider></App>, builderDiv)
	}
}

function renderPreview(wpbb_ajax_obj: WpbbAjaxObj) {
	const previewDiv = document.getElementById('wpbb-bracket-preview-controller')

	if (previewDiv) {
		// ---------- Start Preview Page Logic ----------------
		// There must exist an element of class 'wpbb-bracket-preview-controller' to the 
		// product page for the component to be rendered.
		const {
			bracket_url_theme_map,
			gallery_images,
			color_options,
		} = wpbb_ajax_obj
		// Render the preview component into the DOM
		// Find the location to render the gallery component, and render the gallery component.
		render(<App><Gallery overlayThemeMap={bracket_url_theme_map} galleryImages={gallery_images} colorOptions={color_options} /> </App>, previewDiv);
	}
}





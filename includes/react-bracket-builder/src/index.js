import React from 'react';
import App from "./App";
import { render } from '@wordpress/element';
import * as Sentry from '@sentry/react';
// import 'bootstrap/dist/css/bootstrap.min.css';
// import Gallery from './preview/Gallery';

/**
 * Import the stylesheet for the plugin.
 */
import './style/main.scss';
const env = wpbb_ajax_obj.env


// Init Sentry
Sentry.init({
	environment: env,
	dsn: "https://2e4df9ae93914f279c4ab59721811edc@o4505256728330240.ingest.sentry.io/4505256731082752",
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

// Dynamically render components to avoid loading unused modules
const Settings = React.lazy(() => import('./settings/components/Settings'))
const UserBracket = React.lazy(() => import('./bracket_pick/components/UserBracket'))
const Gallery = React.lazy(() => import('./preview/Gallery'))

// Get the wpbb_ajax_obj from the global scope

renderSettings(wpbb_ajax_obj)
renderBracketBuilder(wpbb_ajax_obj)
renderPreview(wpbb_ajax_obj)

function renderSettings(wpbb_ajax_obj) {
	const page = wpbb_ajax_obj.page

	if (page === 'settings') {
		// Render the App component into the DOM
		render(<App><Settings /></App>, document.getElementById('wpbb-admin-panel'));
	}
}

function renderBracketBuilder(wpbb_ajax_obj) {
	const builderDiv = document.getElementById('wpbb-bracket-builder')
	const {
		bracket,
		bracket_product_archive_url,
	} = wpbb_ajax_obj
	if (builderDiv && bracket) {
		render(<App><UserBracket bracketRes={bracket} apparelUrl={bracket_product_archive_url} /></App>, builderDiv)
	}
}

function renderPreview(wpbb_ajax_obj) {
	const previewDiv = document.getElementById('wpbb-bracket-preview-controller')

	if (previewDiv) {
		// ---------- Start Preview Page Logic ----------------
		// There must exist an element of class 'wpbb-bracket-preview-controller' to the 
		// product page for the component to be rendered.
		const {
			bracket_url,
			default_product_color,
			gallery_images,
		} = wpbb_ajax_obj
		// Render the preview component into the DOM
		// Find the location to render the gallery component, and render the gallery component.
		render(<App><Gallery default_color={default_product_color} bracketImageUrl={bracket_url} galleryImages={gallery_images} /></App>, previewDiv);
	}
}





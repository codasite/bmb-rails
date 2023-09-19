import React from 'react';
import App from "./App";
import { render } from '@wordpress/element';
import * as Sentry from '@sentry/react';
import { OverlayUrlThemeMap } from './preview/Gallery';
// import { BracketRes } from './brackets/shared/api/types/bracket';
import { bracketBuilderStore } from './brackets/shared/app/store';
import { Provider } from 'react-redux';
import { camelCaseKeys } from './brackets/shared/api/bracketApi';
import withMatchTree from './brackets/shared/components/WithMatchTree';

interface WpbbAjaxObj {
	page: string;
	nonce: string;
	rest_url: string;
	tournament: any;
	template: any;
	play: any;
	bracket_url_theme_map: OverlayUrlThemeMap;
	css_url: string;
	bracket_product_archive_url: string;
	gallery_images: any;
	color_options: any;
	sentry_env: string;
	sentry_dsn: string;
	post: any;
	my_templates_url: string;
	my_tournaments_url: string;
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
const TournamentPlayBuilder = React.lazy(() => import('./brackets/TournamentPlayBuilder/TournamentPlayBuilder'))
const Gallery = React.lazy(() => import('./preview/Gallery'))
// const Options = React.lazy(() => import('./brackets/UserTemplateBuilder/UserTemplateBuilder'))
const TemplateBuilder = React.lazy(() => import('./brackets/TemplateBuilder/TemplateBuilder'))
const TournamentResultsBuilder = React.lazy(() => import('./brackets/TournamentResultsBuilder/TournamentResultsBuilder'))
// const WithMatchTree = React.lazy(() => import('./brackets/shared/components/WithMatchTree'))

// Get the wpbb_ajax_obj from the global scope

renderSettings(wpbb_ajax_obj)
renderPreview(wpbb_ajax_obj)
renderPlayTournamentBuilder(wpbb_ajax_obj)
renderTemplateBuilder(wpbb_ajax_obj)
renderPlayTemplate(wpbb_ajax_obj)
renderTournamentResultsBuilder(wpbb_ajax_obj)

/**
 * This renders the bracket builder admin page. DEPRECATED
 */
function renderSettings(wpbb_ajax_obj: WpbbAjaxObj) {
	const page = wpbb_ajax_obj.page

	if (page === 'settings') {
		// Render the App component into the DOM
		render(<App><Settings /></App >, document.getElementById('wpbb-admin-panel'));
	}
}

/**
 * This renders the create template builder page
 */
function renderTemplateBuilder(wpbb_ajax_obj: WpbbAjaxObj) {
	const templateBuilder = document.getElementById('wpbb-template-builder')
	const {
		my_templates_url,
		my_tournaments_url,
	} = wpbb_ajax_obj
	if (templateBuilder) {
		console.log('rendering template builder')
		const TemplateBuilderWithMatchTree = withMatchTree(TemplateBuilder)
		render(
			<App>
				<Provider store={bracketBuilderStore}>
					<TemplateBuilderWithMatchTree saveTemplateLink={my_templates_url} saveTournamentLink={my_tournaments_url} />
				</Provider>
			</App >, templateBuilder);
	}
}

/**
 * This renders the play template page
 */
function renderPlayTemplate(wpbb_ajax_obj: WpbbAjaxObj) {
	const builderDiv = document.getElementById('wpbb-play-template')
	const {
		template,
		bracket_product_archive_url,
		css_url,
	} = wpbb_ajax_obj

	const temp = camelCaseKeys(template)

	if (builderDiv && temp) {
		console.log('rendering play template')
		render(
			<App>
				<Provider store={bracketBuilderStore}>
					<TournamentPlayBuilder bracketStylesheetUrl={css_url} template={temp} apparelUrl={bracket_product_archive_url} />
				</Provider>
			</App>, builderDiv)
	}
}

/**
 * This renders the play tournament page
 */
function renderPlayTournamentBuilder(wpbb_ajax_obj: WpbbAjaxObj) {
	const builderDiv = document.getElementById('wpbb-play-tournament-builder')
	const {
		tournament,
		bracket_product_archive_url,
		css_url,
	} = wpbb_ajax_obj

	const tourney = camelCaseKeys(tournament)

	if (builderDiv && tournament) {
		console.log('rendering play tournament builder')
		render(
			<App>
				<Provider store={bracketBuilderStore}>
					<TournamentPlayBuilder bracketStylesheetUrl={css_url} tournament={tourney} apparelUrl={bracket_product_archive_url} />
				</Provider>
			</App>, builderDiv)
	}
}

/**
 * This renders the update tournament results page
 */
function renderTournamentResultsBuilder(wpbb_ajax_obj: WpbbAjaxObj) {
	const builderDiv = document.getElementById('wpbb-tournament-results-builder')
	const {
		tournament,
		my_tournaments_url,
	} = wpbb_ajax_obj

	const tourney = camelCaseKeys(tournament)

	if (builderDiv && tourney) {
		const TournamentResultsBuilderWithMatchTree = withMatchTree(TournamentResultsBuilder)
		render(
			<App>
				<Provider store={bracketBuilderStore}>
					<TournamentResultsBuilderWithMatchTree tournament={tourney} saveTournamentLink={my_tournaments_url} />
				</Provider>
			</App>, builderDiv)

	}
}

function renderViewBracketPlay(wpbb_ajax_obj: WpbbAjaxObj) {
	const builderDiv = document.getElementById('wpbb-view-play')
	const {
		play,
		bracket_product_archive_url,
		css_url,
	} = wpbb_ajax_obj

	const playObj = camelCaseKeys(play)

	if (builderDiv && playObj) {
		console.log('rendering view play')
		render(
			<App>
				<Provider store={bracketBuilderStore}>
					<TournamentPlayBuilder bracketStylesheetUrl={css_url} play={playObj} apparelUrl={bracket_product_archive_url} />
				</Provider>
			</App>, builderDiv)
	}

}


/**
 * This loads the apparel preview component for the bracket product page.
 */
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
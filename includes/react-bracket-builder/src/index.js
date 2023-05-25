import App from "./App";
import { render } from '@wordpress/element';
// import 'bootstrap/dist/css/bootstrap.min.css';
// import Gallery from './preview/Gallery';

/**
 * Import the stylesheet for the plugin.
 */
// import './style/main.scss';

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
	const bracket = wpbb_ajax_obj.bracket
	if (builderDiv && bracket) {
		render(<App><UserBracket bracketRes={bracket} /></App>, builderDiv)
	}
}

function renderPreview(wpbb_ajax_obj) {
	const previewDiv = document.getElementById('wpbb-bracket-preview-controller')

	if (previewDiv) {
		// ---------- Start Preview Page Logic ----------------
		// There must exist an element of class 'wpbb-bracket-preview-controller' to the 
		// product page for the component to be rendered.
		const {
			product_id,
			bracket_url,
			variation_gallery_mapping,
			default_product_color,
			gallery_images,
		} = wpbb_ajax_obj
		// Render the preview component into the DOM

		// Find the location to render the gallery component, and render the gallery component.
		render(<App><Gallery gallery_mapping={variation_gallery_mapping} default_color={default_product_color} bracketImageUrl={bracket_url} galleryImages={gallery_images} /></App>, previewDiv);
	}
}





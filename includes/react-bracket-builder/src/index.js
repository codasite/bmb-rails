import App from "./App";
import { render } from '@wordpress/element';
import 'bootstrap/dist/css/bootstrap.min.css';
import Gallery from './preview/components/Gallery';

/**
 * Import the stylesheet for the plugin.
 */
import './style/main.scss';

// Dynamically render components to avoid loading unused modules
const Settings = React.lazy(() => import('./settings/components/Settings'))
const UserBracket = React.lazy(() => import('./user_bracket/components/UserBracket'))

const page = wpbb_ajax_obj.page

if (page === 'settings') {
	// Render the App component into the DOM
	render(<App><Settings /></App>, document.getElementById('wpbb-admin-panel'));
}
const builderDiv = document.getElementById('wpbb-bracket-builder')
const bracket = wpbb_ajax_obj.bracket
if (builderDiv && bracket) {
	render(<App><UserBracket bracketRes={bracket} /></App>, builderDiv)
}

// ---------- Start Preview Page Logic ----------------
// There must exist an element of class 'wpbb-bracket-preview-controller' to the 
// product page for the component to be rendered.
const product_id = wpbb_ajax_obj.product_id;
const previewDiv = document.getElementById('wpbb-bracket-preview-controller')
const variation_gallery_mapping = wpbb_ajax_obj.variation_gallery_mapping;
const default_product_color = wpbb_ajax_obj.default_product_color;


if (previewDiv) {
	// Render the preview component into the DOM

	// Remove the default woocommerce product variation gallery element from the page
	var woo_variation_gallery_container = document.querySelector('.woo-variation-gallery-container');
	woo_variation_gallery_container.remove();

	// Find the location to render the gallery component, and render the gallery component.
	var gallery_location = document.querySelector(`#product-${product_id}`);
	var div = document.createElement("div");
	div.setAttribute("id", "wpbb-bracket-preview");
	gallery_location.insertBefore(div, gallery_location.firstChild);

	render(<App><Gallery gallery_mapping={variation_gallery_mapping} default_color={default_product_color}/></App>, div);
}



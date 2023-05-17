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

const previewDiv = document.getElementById('wpbb-bracket-preview-controller')
const variation_gallery_mapping = wpbb_ajax_obj.variation_gallery_mapping;
const default_color = wpbb_ajax_obj.default_color;
console.log(variation_gallery_mapping);


if (previewDiv) {

	// Remove (or hide) default gallery container
	var woo_variation_gallery_container = document.querySelector('.woo-variation-gallery-container');
	woo_variation_gallery_container.remove();
	//woo_variation_gallery_container.style.display = 'none';



	// Render gallery
	var node = document.querySelector("#product-40");
	var div = document.createElement("div");
	div.setAttribute("id", "wpbb-bracket-preview");
	// make div the first child of node
	node.insertBefore(div, node.firstChild);

	render(<App><Gallery gallery_mapping={variation_gallery_mapping} default_color={default_color}/></App>, div);
}



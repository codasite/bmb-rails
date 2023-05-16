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

const logoImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';
const logoPosition = [50, 100];
const logoSize = [32,32];

// Given the variation_gallery_mapping, get urls for images with bracket overlays.
const variation_gallary_mapping_with_overlay = {};
const image_urls = [];
Object.keys(variation_gallery_mapping).forEach((variation_id) => {
	const variation_image_urls = variation_gallery_mapping[variation_id];
	//var urls = [];
	variation_image_urls.forEach((image_url) => {
		// Overlay the bracket (url is appended to the array inside overlayLogo function))
		overlayLogo(image_url, logoImageUrl, logoPosition, logoSize, image_urls);
	});
});


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

	render(<App><Gallery imageUrls={image_urls} /></App>, div);
}



function overlayLogo(backgroundImageUrl, logoImageUrl, logoPosition, logoSize, urls) {
	// Create a new cross-origin image element for the background image
	const backgroundImage = new Image();
	backgroundImage.crossOrigin = "anonymous";
  
	// Set the source URL for the background image
	backgroundImage.src = backgroundImageUrl;
  
	// Create a new cross-origin image element for the logo image
	const logoImage = new Image();
	logoImage.crossOrigin = "anonymous";
  
	// Set the source URL for the logo image
	logoImage.src = logoImageUrl;
  
	// Wait for both images to load
	Promise.all([loadImage(backgroundImage), loadImage(logoImage)])
	  .then(() => {
		// Create a canvas element
		const canvas = document.createElement('canvas');
		const context = canvas.getContext('2d');
  
		// Set canvas dimensions to match the background image
		canvas.width = backgroundImage.width;
		canvas.height = backgroundImage.height;
  
		// Draw the background image on the canvas
		context.drawImage(backgroundImage, 0, 0);
  
		// Calculate the position to place the logo on the canvas
		const [x, y] = logoPosition;
  
		// Calculate the desired width and height of the logo
		const [logoWidth, logoHeight] = logoSize;
  
		// Draw the logo image on the canvas at the specified position and size
		context.drawImage(logoImage, x, y, logoWidth, logoHeight);
  
		// Convert the canvas image to a data URL
		const outputImageUrl = canvas.toDataURL();
  
		// Create a new image element to display the output image
		const outputImageElement = document.createElement('img');
		outputImageElement.setAttribute('src', outputImageUrl);
		urls.push(outputImageUrl);
	  })
	  .catch((error) => {
		console.error('An error occurred:', error);
	  });
  }
  

  function loadImage(imageElement) {
	return new Promise((resolve, reject) => {
	  imageElement.addEventListener('load', () => resolve(), false);
	  imageElement.addEventListener('error', (error) => reject(error), false);
	});
  }
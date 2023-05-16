import App from "./App";
import { render } from '@wordpress/element';
import 'bootstrap/dist/css/bootstrap.min.css';
import Gallery from './preview/components/Gallery';
import Preview from './preview/components/Preview';

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
if (previewDiv) {

	const logoImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';
	const logoPosition = [50, 100];
	const logoSize = [32,32];

	// Get the gallery container
	var woo_variation_gallery_container = document.querySelector('.woo-variation-gallery-container');

	// Get the urls of variation images
	var variation_images = document.querySelectorAll('.wp-post-image');
	var image_urls = [];
	variation_images.forEach((image) => {
		image_urls.push(image.getAttribute('src'));
	});

	// Hide children of the gallery container (dont remove because we need to get the
	// new image urls when the user selects a new variation)
	// while (woo_variation_gallery_container.firstChild) {
	// 	woo_variation_gallery_container.removeChild(woo_variation_gallery_container.firstChild);
	// }
	woo_variation_gallery_container.style.display = 'none';

	// Get new urls
	var new_image_urls = [];
	image_urls.forEach((image_url) => {
		// Create a new image element
		overlayLogo(image_url, logoImageUrl, logoPosition, logoSize, new_image_urls);
	});

	var node = document.querySelector("#product-40");
	var div = document.createElement("div");
	div.setAttribute("id", "wpbb-bracket-preview");
	// make div the first child of node
	node.insertBefore(div, node.firstChild);

	// render(<App><Gallery imageUrls={image_urls} /></App>, div);
	render(<App><Preview imageUrls={image_urls} /></App>, div);
}




function overlayLogo(backgroundImageUrl, logoImageUrl, logoPosition, logoSize, urls) {
	console.log('overlaying logo');
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
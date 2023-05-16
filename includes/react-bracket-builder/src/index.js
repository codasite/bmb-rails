import App from "./App";
import { render } from '@wordpress/element';
import 'bootstrap/dist/css/bootstrap.min.css';
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
	console.log("Preview");

	// Need to find the product image element in the document, and replace it
	// with the bracket preview.

	// The product image element is dynamically updated via the 
	// woocommerce/assets/js/frontend/add-to-cart-variation.min.js script when
	// the user selects a new product variation. This behaviour is not
	// customizable as far as I can tell.

	// To solve the issue, I find the product image in the dom, replace it with the
	// bracket preview, and listen for changes in that dom element, and update accordingly.

	// Find the product image element
	var imageNode = document.querySelector('.wp-post-image');
	// Update image node so it is hidden
	imageNode.setAttribute('style', 'display: none;');

	function getPreviewImage(src) {
		return src;
		var fire = 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Large_bonfire.jpg/640px-Large_bonfire.jpg'
		return fire;
	}

	// Append a new element to the parent of the image node
	var previewNode = document.createElement('img');
	previewNode.setAttribute('id', 'wpbb-bracket-preview');
	//previewNode.setAttribute('src', );

	imageNode.parentElement.appendChild(previewNode);



	var mutations = [imageNode];

	// Create an observer instance linked to the callback function
	var observer = new MutationObserver(function (mutations) {
		mutations.forEach(function (mutation) {

			// If the image src has changed, update the bracket preview
			var src = getPreviewImage(mutation.target.src);

			// Example usage
			const backgroundImageUrl = src;
			const logoImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';
			const logoPosition = [50, 100];
			const logoSize = [32,32];
  
			overlayLogo(backgroundImageUrl, logoImageUrl, logoPosition, logoSize, previewNode);
  
			if (src) {
				previewNode.setAttribute('src', src);
			}
		});
	});

	// Configuration options for the observer (e.g., observe childList, attributes, etc.)
	var config = { childList: true, attributes: true, subtree: true };

	// Start observing the target node and its descendants for mutations
	observer.observe(imageNode, config);

	render(<App><Preview /></App>)
}



function overlayLogo(backgroundImageUrl, logoImageUrl, logoPosition, logoSize, outputImageElement) {
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
		const outputImage = new Image();
		outputImageElement.setAttribute('src', outputImageUrl);
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

  function isPlaceholderImage(src) {
	return 'placeholder' in src;
  }
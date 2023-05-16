import React, { useEffect, useState } from 'react';
import Gallery from './Gallery';

const Preview = ({ imageUrls }) => {
    console.log('preview');
    const [urls, setUrls] = useState(imageUrls);


    useEffect(() => {
        const targetElement = document.querySelector('.woo-variation-product-gallery') || document.createElement('div');
        console.log(targetElement);
    
        const observerCallback = (mutationsList) => {
          for (const mutation of mutationsList) {
            const images = targetElement.querySelectorAll('.wp-post-image');
            const imageUrls = Array.from(images).map((image) => image.src);
            //setUrls(imageUrls);

            const logoImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';
            const logoPosition = [50, 100];
            const logoSize = [32,32];

            var new_urls = [];

            imageUrls.forEach((imageUrl) => {
                overlayLogo(imageUrl, logoImageUrl, logoPosition, logoSize, new_urls);
            });

            setUrls(new_urls);
          }
        };
    
        const observer = new MutationObserver(observerCallback);
        observer.observe(targetElement, { attributes: true, childList: true, subtree: true });
    
        return () => {
          observer.disconnect();
        };
      }, []);



    return (
        <Gallery imageUrls={urls} />
    )
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



export default Preview;
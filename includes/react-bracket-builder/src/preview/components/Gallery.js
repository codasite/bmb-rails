import React, { useState, useEffect } from 'react';

const arrowLeftStyle = {
  position: 'absolute',
  top: '50%',
  left: '0',
  transform: 'translateY(-50%)',
  backgroundColor: '#ffffff',
  border: 'none',
  color: '#333333',
  fontSize: '2rem',
  padding: '0.5rem',
  cursor: 'pointer',
  background: 'none'
};

const arrowRightStyle = {
  position: 'absolute',
  top: '50%',
  right: '0',
  transform: 'translateY(-50%)',
  backgroundColor: '#ffffff',
  border: 'none',
  color: '#333333',
  fontSize: '2rem',
  padding: '0.5rem',
  cursor: 'pointer',
  background: 'none'
};

const imageStyle = {
  maxWidth: '100%',
  height: 'auto',
};

const logoImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';
const logoPosition = [50, 100];
const logoSize = [32,32];


const Gallery = ({ gallery_mapping, default_color}) => {
  console.log("Gallery");
  const [currentIndex, setCurrentIndex] = useState(0);
  const [imageUrls, setImageUrls] = useState([]);
  const [currentColor, setCurrentColor] = useState(default_color);

  useEffect(() => {
    // Get the images with bracket overlays
    console.log("get the images with bracket overlays");
    setImageUrls([]);

    const addUrlToImageUrls = (url) => {
      setImageUrls(prevImageUrls => [...prevImageUrls, url]);
    }
    
    // Given the variation_gallery_mapping, get urls for images with bracket overlays.
    const variation_image_urls = gallery_mapping[currentColor];
    variation_image_urls.forEach((image_url) => {
      // Overlay the bracket (url is appended to the array inside overlayLogo function))
      overlayLogo(image_url, logoImageUrl, logoPosition, logoSize, addUrlToImageUrls);
    });
  }, [currentColor]);

  useEffect(() => {
    // attach an event listener to the select element
    const selectElement = document.querySelector('select#color');
    const optionElements = selectElement.querySelectorAll('option');

    selectElement.addEventListener('click', (event) => {
      if (event.target.value) {
        setCurrentColor(event.target.value);
        set
      };
      console.log('bruh');
      console.log(event.target.value);
      console.log('dodododo');
    });
  }, []);

  
  const handlePrevious = () => {
    setCurrentIndex((prevIndex) => (prevIndex === 0 ? imageUrls?.length - 1 : prevIndex - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prevIndex) => (prevIndex === imageUrls?.length - 1 ? 0 : prevIndex + 1));
  };

  const galleryStyle = {
    // display: 'flex',
    // alignItems: 'center',
    // justifyContent: 'center',
    position: 'relative',
  };


  return (
      <div className="woocommerce-product-gallery woocommerce-product-gallery--without-images woocommerce-product-gallery--columns-4 images">
        <div className="woocommerce-product-gallery__wrapper" style={galleryStyle} >
          <button style={arrowLeftStyle} onClick={handlePrevious}>
            &lt;
          </button>
          <img className="wp-post-image" style={imageStyle} src={imageUrls[currentIndex]} alt={`Image ${currentIndex + 1}`} />
          <button style={arrowRightStyle} onClick={handleNext}>
            &gt;
          </button>
        </div>
      </div>
  );
};










function overlayLogo(backgroundImageUrl, logoImageUrl, logoPosition, logoSize, callback) {
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
		context?.drawImage(backgroundImage, 0, 0);
  
		// Calculate the position to place the logo on the canvas
		const [x, y] = logoPosition;
  
		// Calculate the desired width and height of the logo
		const [logoWidth, logoHeight] = logoSize;
  
		// Draw the logo image on the canvas at the specified position and size
		context?.drawImage(logoImage, x, y, logoWidth, logoHeight);
  
		// Convert the canvas image to a data URL
		const outputImageUrl = canvas.toDataURL();
  
		// Create a new image element to display the output image
		const outputImageElement = document.createElement('img');
		outputImageElement.setAttribute('src', outputImageUrl);
    callback(outputImageUrl);
		//urls.push(outputImageUrl);
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






export default Gallery;





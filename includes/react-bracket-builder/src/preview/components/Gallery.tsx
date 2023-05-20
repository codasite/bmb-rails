import React, { useState, useEffect } from 'react';
import Thumbnails from './Thumbnails';

// TODO: use bracket image url
//const bracketImageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/SNice.svg/1200px-SNice.svg.png';

interface GalleryProps {
  gallery_mapping: { [key: string]: string[] };
  default_color: string;
  bracketImageUrl: string,
}

const Gallery: React.FC<GalleryProps> = ({ gallery_mapping, default_color, bracketImageUrl}) => {
  console.log('bracket image url: ', bracketImageUrl);

  // Index of current image to display in the gallery
  const [currentIndex, setCurrentIndex] = useState<number>(0);

  // URLs of images to display in the gallery. This is updated
  // when currentColor is updated. 
  const [imageUrls, setImageUrls] = useState<string[]>([]);

  // Color of current variation selection  
  const [currentColor, setCurrentColor] = useState<string>(default_color);



  // The product page is using the default woocommerce variation selector. We need to
  // listen to this selector in order to know when to update the gallery, so
  // attach a listener to the select element. Runs only once, when the component
  // is first rendered.
  useEffect(() => {
    // attach an event listener to the select element
    const selectElement = document.querySelector('select#color') as HTMLSelectElement;
    const optionElements = selectElement.querySelectorAll('option');

    selectElement.addEventListener('click', (event) => {
      const target = event.target as HTMLSelectElement;
      if (target.value) {
        setCurrentColor(target.value);
    };
    });
  }, []);



  // Update imageUrls.
  // Runs every time the currentColor is updated.
  useEffect(() => {
    setImageUrls([]);

    // Callback to append url to imageUrls. This is passed as the last arugment
    // to overlayBracket function.
    const appendUrl = (url: string) => {
      setImageUrls(prevImageUrls => [...prevImageUrls, url]);
    }
    
    // Get URLs of images rendered with bracket overlay.
    const variation_image_urls = gallery_mapping[currentColor];
    variation_image_urls?.forEach((image_url) => {
      // Overlay the bracket on the image, and append the url to imageUrls.
      if (image_url) { // null check

        // Only overlay the bracket on the back of the shirt.
        if (image_url.toLowerCase().includes('back')) {
          overlayBracket(image_url, bracketImageUrl, appendUrl);
        } else {
          appendUrl(image_url);
        }
      }
    });
  }, [currentColor]);



  // Controls to navigate the gallery
  const handlePrevious = () => {
    setCurrentIndex((prevIndex) => (prevIndex === 0 ? (imageUrls?.length ?? 0)- 1 : prevIndex - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prevIndex) => (prevIndex === (imageUrls?.length ?? 0) - 1 ? 0 : prevIndex + 1));
  };


  // The outer two div classNames are copied from the original WooCommerce product page to
  // ensure that the gallery is styled correctly.
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
        <Thumbnails imageUrls={imageUrls} currentIndex={currentIndex} setCurrentIndex={setCurrentIndex} />
      </div>
  );
};

function extractFilenameFromUrl(url) {
  const slashIndex = url.lastIndexOf('/');
  if (slashIndex !== -1) {
    const filename = url.substring(slashIndex + 1);
    console.log('filename: ', filename);
    return filename;
  }
  throw new Error("Error extracting filename from background image url");
}

function extractImageValues(imageUrl: string): { width: number, xOffset: number, yOffset: number } {
  // Extract the width, x, and y values from the image URL
  const filename = extractFilenameFromUrl(imageUrl);
  console.log('filename: ', filename);

  const matches = filename.match(/_(\d+)_(\d+)_(\d+)\.\w+$/);

  if (matches && matches.length === 4) {
    const width = parseInt(matches[1], 10);
    const xOffset = parseInt(matches[2], 10);
    const yOffset = parseInt(matches[3], 10);

    return { width, xOffset, yOffset };
  }
  throw new Error("Error extracting image values");
}

// This is the big function that overlays the bracket on the image.
function overlayBracket(backgroundImageUrl: string, bracketImageUrl: string, callback: (url: string) => void) {

  // Extract the bracket overlay width, x, and y offsets from the background image filename in the background image URL. 
  const {width, xOffset, yOffset} = extractImageValues(backgroundImageUrl);
  const bracketWidth = width;
  const bracketPosition = [xOffset, yOffset];

	// Create a new image element for the background image
  // The background image comes from the product so no worries
  // about cross-origin properties.
	const backgroundImage = new Image();
	//backgroundImage.crossOrigin = "anonymous";  
  
	// Set the source URL for the background image
	backgroundImage.src = backgroundImageUrl;
  
	// Create a new image element for the bracket image
  // Because the bracket image is hosted on a different domain (while I'm developing),
  // must set the crossOrigin attribute to anonymous. This will likely be unecessary in
  // production.
	const bracketImage = new Image();
	bracketImage.crossOrigin = "anonymous";
  
	// Set the source URL for the logo image
	bracketImage.src = bracketImageUrl;
  
	// Wait for both images to load
	Promise.all([loadImage(backgroundImage), loadImage(bracketImage)])
	  .then(() => {

      // Scale the bracket image to the correct size
      const aspectRatio = bracketImage.width / bracketImage.height;
      const bracketHeight = bracketWidth / aspectRatio;

		// Create a canvas element
		const canvas = document.createElement('canvas');
		const context = canvas.getContext('2d');

    // Set canvas dimensions to match the background image
		canvas.width = backgroundImage.width;
		canvas.height = backgroundImage.height;
  
		// Draw the background image on the canvas
		context?.drawImage(backgroundImage, 0, 0);
  
		// Calculate the position to place the logo on the canvas
		//const [x, y] = logoPosition;
    const [x,y] = bracketPosition;
    
		// Draw the logo image on the canvas at the specified position and size
		context?.drawImage(bracketImage, x, y, bracketWidth, bracketHeight);
  
		// Convert the canvas image to a data URL
		const outputImageUrl = canvas.toDataURL();
  
		// Create a new image element to display the output image
		const outputImageElement = document.createElement('img');
		outputImageElement.setAttribute('src', outputImageUrl);
    callback(outputImageUrl);
	  })
	  .catch((error) => {
		console.error('An error occurred:', error);
	  });
  }
  

  function loadImage(imageElement: HTMLImageElement): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      imageElement.addEventListener('load', () => resolve(), false);
      imageElement.addEventListener('error', (error) => reject(error), false);
    });
  }


export default Gallery;



// Gallery styles
const galleryStyle: React.CSSProperties = {
  position: 'relative',
};

const arrowLeftStyle: React.CSSProperties = {
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

const arrowRightStyle: React.CSSProperties = {
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

const imageStyle: React.CSSProperties = {
  maxWidth: '100%',
  height: 'auto',
};


import React, { useState, useEffect } from 'react';
import Thumbnails from './Thumbnails';
import ImageGallery from 'react-image-gallery';

interface GalleryProps {
  gallery_mapping: { [key: string]: string[] };
  default_color: string;
  bracketImageUrl: string,
  galleryImages: string[];
}

const Gallery: React.FC<GalleryProps> = ({ gallery_mapping, default_color, bracketImageUrl, galleryImages }) => {

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
        let color = target.value;
        console.log('current color: ', color.toLowerCase().trim());
        setCurrentColor(color.toLowerCase().trim());
      };
    });
  }, []);



  // Update imageUrls.
  // Runs every time the currentColor is updated.
  useEffect(() => {
    console.log('gallery images')
    console.log(galleryImages)
    console.log('bracket url')
    console.log(bracketImageUrl)
    // setImageUrls(galleryImages);

    // Callback to append url to imageUrls. This is passed as the last arugment
    // to overlayBracket function.
    const appendUrl = (url: string) => {
      setImageUrls(prevImageUrls => [...prevImageUrls, url]);
    }

    // Get URLs of images rendered with bracket overlay.
    // const variation_image_urls = gallery_mapping[currentColor];
    // setImageUrls(variation_image_urls ?? []);
    // galleryImages?.forEach((image_url) => {
    // const overlayedUrls = galleryImages?.map((imageUrl) => {
    galleryImages?.forEach((imageUrl) => {
      // Overlay the bracket on the image, and append the url to imageUrls.
      if (imageUrl) { // null check
        let filename = extractFilenameFromUrl(imageUrl);
        // Only overlay the bracket on the back of the shirt.
        if (filename.toLowerCase().includes('back')) {
          console.log('overlaying bracket')
          overlayBracket(imageUrl, bracketImageUrl, appendUrl);
        } else {
          appendUrl(imageUrl);
        }
      }
    });
  }, [currentColor]);



  // Controls to navigate the gallery
  const handlePrevious = () => {
    setCurrentIndex((prevIndex) => (prevIndex === 0 ? (imageUrls?.length ?? 0) - 1 : prevIndex - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prevIndex) => (prevIndex === (imageUrls?.length ?? 0) - 1 ? 0 : prevIndex + 1));
  };


  // The outer two div classNames are copied from the original WooCommerce product page to
  // ensure that the gallery is styled correctly.
  // return (
  //   <div className="woocommerce-product-gallery woocommerce-product-gallery--without-images woocommerce-product-gallery--columns-4 images">
  //     <div style={galleryWrapperStyle}>
  //       <div className="woocommerce-product-gallery__wrapper" style={galleryStyle} >
  //         <button style={arrowLeftStyle} onClick={handlePrevious}>
  //           &lt;
  //         </button>
  //         <img className="wp-post-image" style={imageStyle} src={imageUrls[currentIndex]} alt={`Image ${currentIndex + 1}`} />
  //         <button style={arrowRightStyle} onClick={handleNext}>
  //           &gt;
  //         </button>
  //       </div>
  //     </div>
  //     <Thumbnails imageUrls={imageUrls} currentIndex={currentIndex} setCurrentIndex={setCurrentIndex} />
  //   </div>
  // );
  const images = imageUrls.map((image) => {
    return {
      original: image,
      thumbnail: image
    }
  })

  return (
    //@ts-ignore
    <ImageGallery items={images} showPlayButton={false} />
  )
};

function extractFilenameFromUrl(url) {
  console.log('Image url:', url);
  const slashIndex = url.lastIndexOf('/');
  if (slashIndex !== -1) {
    const filename = url.substring(slashIndex + 1);
    return filename;
  }
  throw new Error("Error extracting filename from background image url");
}

function extractImageValues(imageUrl: string): { width: number, xCenter: number, yCenter: number } {
  /*
  Extract the width, and desired coordinates of the center of the bracket from the image URL.
  
  @param {string} imageUrl - URL of the bracket imgae to extract values from.
  @returns {object} - Object containing the desired image width, plus x and y values of the center of the bracket.
  */
  // Extract the width, x, and y values from the image URL
  const filename = extractFilenameFromUrl(imageUrl);
  console.log('Filename:', filename);

  const matches = filename.match(/-(\d+)-(\d+)-(\d+)\.\w+$/);

  if (matches && matches.length === 4) {
    const width = parseInt(matches[1], 10);
    const xCenter = parseInt(matches[2], 10);
    const yCenter = parseInt(matches[3], 10);

    return { width, xCenter, yCenter };
  }
  throw new Error(`Error extracting image values. ${filename} does not match expected format: product_color_back_{width}_{x_offset}_{y_offset}.png`);
}

// This is the big function that overlays the bracket on the image.
function overlayBracket(backgroundImageUrl: string, bracketImageUrl: string, callback: (url: string) => void) {

  // Extract the bracket overlay width, x, and y offsets from the background image filename in the background image URL. 
  // const { width, xCenter, yCenter } = extractImageValues(backgroundImageUrl);
  const width = 200
  const xCenter = 415
  const yCenter = 215

  const bracketWidth = width;
  const bracketCenter = [xCenter, yCenter];

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
      var [x, y] = bracketCenter;
      x -= bracketWidth / 2;
      y += bracketHeight / 2;

      // Draw the logo image on the canvas at the specified position and size
      context?.drawImage(bracketImage, x, y, bracketWidth, bracketHeight);

      // Convert the canvas image to a data URL
      const outputImageUrl = canvas.toDataURL();

      // Create a new image element to display the output image
      // const outputImageElement = document.createElement('img');
      // outputImageElement.setAttribute('src', outputImageUrl);
      // console.log('output url')
      // console.log(outputImageUrl)


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
const galleryWrapperStyle: React.CSSProperties = {
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
};

const galleryStyle: React.CSSProperties = {
  position: 'relative',
  maxWidth: '450px',
  width: '100%',
  height: 'auto',
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


import React, { useState, useEffect } from 'react';
import Thumbnails from './Thumbnails';
import ImageGallery from 'react-image-gallery';

interface GalleryProps {
  defaultColor: string;
  bracketImageUrl: string,
  galleryImages: string[];
}

// An enum called ProductImageOrientation 
enum ProductImageOrientation {
  FRONT = 'front',
  BACK = 'back',
}

interface ProductImageParams {
  productSlug: string;
  variationColor: string;
  orientation: string;
  overlayParams: ImageOverlayParams;
}

interface ImageOverlayParams {
  width: number;
  xCenter: number;
  yCenter: number;
}

const Gallery: React.FC<GalleryProps> = ({ defaultColor, bracketImageUrl, galleryImages }) => {
  // URLs of images to display in the gallery. This is updated
  // when currentColor is updated. 
  const [imageUrls, setImageUrls] = useState<string[]>([]);

  // Color of current variation selection  
  const [currentColor, setCurrentColor] = useState<string>(defaultColor);

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
  useEffect(() => {
    addOverlays()
  }, []);

  const addOverlays = async () => {
    const promises = galleryImages.map((imageUrl) => {

      const params = parseImageParams(imageUrl);

      if (params.orientation === ProductImageOrientation.BACK) {
        const dataUrl = overlayImage(imageUrl, bracketImageUrl, 200, 415, 215);
        return dataUrl
      }
      return imageUrl

    })

    // Get URLs of images rendered with bracket overlay.
    const dataUrls = await Promise.allSettled(promises).then(res => {
      // get data urls where status is fulfilled
      const fulfilledDataUrls = res.filter((promise) => {
        return promise.status === 'fulfilled'
      }).map((promise) => {
        return (promise as PromiseFulfilledResult<string>).value;
      })
      return fulfilledDataUrls
    }).catch(error => {
      alert('Error loading images');
      console.error(error);
    });
    if (dataUrls) {
      setImageUrls(dataUrls)
    }
  }

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

const parseImageParams = (imageUrl: string): ProductImageParams => {
  const filename = extractFilenameFromUrl(imageUrl);
  if (!filename) {
    throw new Error('Unable to extract filename from image URL.');
  }
  const params = filename.split('_');

  if (params.length < 6) {
    throw new Error('Received too few parameters in image URL.');
  }
  // unpack params
  const [productSlug, variationColor, orientation, width, xCenter, yCenter, ...rest] = params;

  // validate that orientation is either 'front' or 'back'
  if (orientation !== ProductImageOrientation.FRONT && orientation !== ProductImageOrientation.BACK) {
    throw new Error(`Unexpected orientation value in image URL: ${orientation}`);
  }

  // validate that width is a number prefixed with 'w'
  const widthRegex = /^w\d+$/;
  if (!widthRegex.test(width)) {
    throw new Error(`Unexpected width value in image URL: ${width}`);
  }

  // validate that xCenter and yCenter are numbers prefixed with 'xc' and 'yc'
  const xCenterRegex = /^xc\d+$/;
  if (!xCenterRegex.test(xCenter)) {
    throw new Error(`Unexpected xCenter value in image URL: ${xCenter}`);
  }

  const yCenterRegex = /^yc\d+$/;
  if (!yCenterRegex.test(yCenter)) {
    throw new Error(`Unexpected yCenter value in image URL: ${yCenter}`);
  }

  // extract the numeric values from the strings
  const widthValue = parseInt(width.substring(1), 10);
  const xCenterValue = parseInt(xCenter.substring(2), 10);
  const yCenterValue = parseInt(yCenter.substring(2), 10);

  // return the parsed values
  return {
    productSlug,
    variationColor,
    orientation,
    overlayParams: {
      width: widthValue,
      xCenter: xCenterValue,
      yCenter: yCenterValue,
    },
  };
}

function extractFilenameFromUrl(url): string | null {
  // Extract the filename from the URL and strip the file extension.
  let filename = url.split('/').pop()
  // strip file extension from filename
  const dotIndex = filename.lastIndexOf('.');
  if (dotIndex !== -1) {
    filename = filename.substring(0, dotIndex);
  }
  return filename;
}

// This is the big function that overlays the bracket on the image.
async function overlayImage(backgroundUrl: string, overlayUrl: string, width: number, xCenter: number, yCenter: number,) {

  const bracketWidth = width;
  const bracketCenter = [xCenter, yCenter];

  // Create a new image element for the background image
  // The background image comes from the product so no worries
  // about cross-origin properties.
  const backgroundImage = new Image();
  //backgroundImage.crossOrigin = "anonymous";  

  // Set the source URL for the background image
  backgroundImage.src = backgroundUrl;

  // Create a new image element for the bracket image
  // Because the bracket image is hosted on a different domain (while I'm developing),
  // must set the crossOrigin attribute to anonymous. This will likely be unecessary in
  // production.
  const bracketImage = new Image();
  bracketImage.crossOrigin = "anonymous";

  // Set the source URL for the logo image
  bracketImage.src = overlayUrl;
  await loadImage(bracketImage).catch((error) => {
    console.error(error);
    throw new Error("Error loading bracket image");
  });

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
  return outputImageUrl;
}


function loadImage(imageElement: HTMLImageElement): Promise<void> {
  return new Promise<void>((resolve, reject) => {
    imageElement.addEventListener('load', () => resolve(), false);
    imageElement.addEventListener('error', (error) => reject(error), false);
  });
}

export default Gallery;
import React, { useState, useEffect, useRef } from 'react';
import Thumbnails from './Thumbnails';
import ImageGallery from 'react-image-gallery';
import * as Sentry from '@sentry/react';

// maps the theme name to the url of the overlay image
export interface OverlayUrlThemeMap {
  [key: string]: string;
}

interface GalleryImage {
  src: string;
  title: string;
}

interface GalleryProps {
  overlayThemeMap: OverlayUrlThemeMap;
  galleryImages: GalleryImage[];
  colorOptions: string[];
}

// An enum called ProductImageOrientation 
enum ProductImageOrientation {
  FRONT = 'front',
  BACK = 'back',
}

enum ProductImageThemeMode {
  DARK = 'dark',
  LIGHT = 'light',
}

interface ProductImageConfig {
  url: string;
  variationColor?: string;
}

interface ProductImageParams {
  variationColor?: string;
  orientation?: ProductImageOrientation;
  overlayParams?: ImageOverlayParams;
  themeMode?: ProductImageThemeMode;
}

interface ImageOverlayParams {
  width: number;
  xCenter: number;
  yCenter: number;
}

const Gallery: React.FC<GalleryProps> = ({ overlayThemeMap, galleryImages, colorOptions }) => {
  // URLs of images to display in the gallery. This is updated
  // when the select listener is triggered.
  const [imageUrls, setImageUrls] = useState<string[]>([]);
  const [imageConfigs, setImageConfigs] = useState<ProductImageConfig[]>([]);

  const imageConfigsRef = useRef<ProductImageConfig[]>(imageConfigs);

  useEffect(() => {
    // Store image configs in a ref so we can access them in the colorSelectChangeHandler
    imageConfigsRef.current = imageConfigs;
  }, [imageConfigs]);


  useEffect(() => {
    const imageConfigsPromise = buildImageConfigs();
    const domContentLoadedPromise = createDomContentLoadedPromise();

    // Wait for both the image configs and the DOM content to be ready before we attach the select listener.
    Promise.all([imageConfigsPromise, domContentLoadedPromise])
      .then(([imageConfigs]) => {
        setImageConfigs(imageConfigs);
        initImages(imageConfigs);
      })
      .catch(error => {
        console.error('An error occurred:', error);
        Sentry.captureException(error);
      });
  }, []);

  // This function is called when the image configs are ready and the DOM content is loaded.
  // It sets the initial image URLs and attaches the select listener.
  const initImages = (imageConfigs: ProductImageConfig[]) => {
    const selectElement = document.querySelector('select#color') as HTMLSelectElement | null;

    if (selectElement?.value) {
      let color = selectElement.value;
      const newUrls = getImageUrlsForColor(imageConfigs, color);
      setImageUrls(newUrls);
      selectElement.addEventListener('change', colorSelectChangeHandler);
    } else {
      setImageUrls(imageConfigs.map((config) => config.url));
    }
  }

  const colorSelectChangeHandler = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const imageConfigs = imageConfigsRef.current;
    if (target?.value) {
      let color = target.value;
      // Use the image configs stored in the ref to get the image URLs for the selected color.
      // Needed because this handler is attached to the select element, which is not part of react.
      const newUrls = getImageUrlsForColor(imageConfigs, color);
      if (newUrls.length > 0) {
        return setImageUrls(newUrls);
      }
    }
    const urls = imageConfigs.map(config => {
      return config.url
    })
    setImageUrls(urls);
  };

  const buildImageConfigs = async (): Promise<ProductImageConfig[]> => {
    const promises = galleryImages.map((image) => {
      return buildProductImageConfig(image, overlayThemeMap);
    });

    const configs = await Promise.allSettled(promises).then(res => {
      // get data urls where status is fulfilled
      const fulfilledConfigs = res.filter((promise) => {
        return promise.status === 'fulfilled'
      }).map((promise) => {
        return (promise as PromiseFulfilledResult<ProductImageConfig>).value;
      })
      // get promisses that failed
      const rejectedPromises = res.filter((promise) => {
        return promise.status === 'rejected'
      })
      // log rejected promises
      rejectedPromises.forEach((promise) => {
        console.error(promise);
      })
      return fulfilledConfigs
    }).catch(error => {
      console.error(error);
    });
    return configs ? configs : [];
  }

  const buildProductImageConfig = async (image: GalleryImage, overlayMap: OverlayUrlThemeMap): Promise<ProductImageConfig> => {
    const {
      src: backgroundImageUrl,
      title: backgroundImageTitle,
    } = image;

    const {
      variationColor,
      orientation,
      overlayParams,
      themeMode,
    } = parseImageParams(backgroundImageTitle, colorOptions);

    const overlayUrl = overlayMap[themeMode || ProductImageThemeMode.DARK];

    const url = orientation === ProductImageOrientation.BACK && overlayParams && overlayUrl
      ? await addOverlay(backgroundImageUrl, overlayUrl, overlayParams)
      : backgroundImageUrl;

    const config: ProductImageConfig = {
      url,
      variationColor,
    };

    return config;
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

const getImageUrlsForColor = (configs: ProductImageConfig[], color: string): string[] => {
  return configs.filter((config) => {
    return config.variationColor && compareProductAttributes(config.variationColor, color);
  }).map((config) => {
    return config.url;
  });
}

const compareProductAttributes = (str1: string, str2: string): boolean => {
  // Do a case-insensitive comparison of two strings, ignoring whitespace, underscores, and hyphens.
  const formattedStr1 = normalizeString(str1);
  const formattedStr2 = normalizeString(str2);

  return formattedStr1 === formattedStr2;
}

const normalizeString = (str: string): string => {
  // Normalize a string by removing whitespace, underscores, and hyphens, and converting to lowercase.
  return str.trim().toLowerCase().replace(/[-_\s]/g, '');
}

const parseImageParams = (imageTitle: string, colorOptions: string[]): ProductImageParams => {
  // extract a matching value in the colorOptions array from the image title
  const normalizedTitle = normalizeString(imageTitle);
  const normalizedColors = colorOptions.map((color) => {
    return normalizeString(color);
  });

  // get the colorOption where the corresponding normalizedColor is included in the normalizedTitle
  const variationColor = colorOptions.find((colorOption, index) => {
    return normalizedTitle.includes(normalizedColors[index]);
  });

  let orientation: ProductImageOrientation | undefined;
  if (normalizedTitle.includes('back')) {
    orientation = ProductImageOrientation.BACK;
  } else if (normalizedTitle.includes('front')) {
    orientation = ProductImageOrientation.FRONT;
  }

  let themeMode: ProductImageThemeMode | undefined;
  if (normalizedTitle.includes('dark')) {
    themeMode = ProductImageThemeMode.DARK;
  } else if (normalizedTitle.includes('light')) {
    themeMode = ProductImageThemeMode.LIGHT;
  }

  const widthRegex = /w\d+/;
  const widthMatch = normalizedTitle.match(widthRegex);
  const width = widthMatch ? widthMatch[0] : null;

  const xCenterRegex = /xc\d+/;
  const xCenterMatch = normalizedTitle.match(xCenterRegex);
  const xCenter = xCenterMatch ? xCenterMatch[0] : null;

  const yCenterRegex = /yc\d+/;
  const yCenterMatch = normalizedTitle.match(yCenterRegex);
  const yCenter = yCenterMatch ? yCenterMatch[0] : null;

  const imageParams: ProductImageParams = {
    variationColor,
    orientation,
    themeMode,
  }
  if (width && xCenter && yCenter) {
    imageParams.overlayParams = {
      width: parseInt(width.substring(1), 10),
      xCenter: parseInt(xCenter.substring(2), 10),
      yCenter: parseInt(yCenter.substring(2), 10),
    }
  }
  return imageParams;
}

// This is the big function that overlays the bracket on the image.
async function addOverlay(backgroundUrl: string, overlayUrl: string, overlayParams: ImageOverlayParams) {
  const {
    width,
    xCenter,
    yCenter,
  } = overlayParams;

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
  y -= bracketHeight / 2;

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

function createDomContentLoadedPromise(): Promise<void> {
  return new Promise<void>((resolve) => {
    const handleDOMContentLoaded = () => {
      resolve();
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded);
    };

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      resolve();
    } else {
      document.addEventListener('DOMContentLoaded', handleDOMContentLoaded);
    }
  });
}

export default Gallery;
import React, { useState, useEffect, useRef } from 'react'
import ImageGallery from 'react-image-gallery'
import * as Sentry from '@sentry/react'
import 'react-image-gallery/styles/css/image-gallery.css'
import { Spinner } from './Spinner'

const RESFACTOR = 3

// maps the theme name to the url of the overlay image
export interface OverlayUrlThemeMap {
  // [key: string]: string;
  light: string
  dark: string
}

interface GalleryImage {
  src: string
  title: string
}

interface GalleryProps {
  overlayThemeMap: OverlayUrlThemeMap
  galleryImages: GalleryImage[]
  colorOptions: string[]
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
  url: string
  variationColor?: string
  variationTheme?: ProductImageThemeMode
}

interface ProductImageParams {
  variationColor?: string
  orientation?: ProductImageOrientation
  overlayParams?: ImageOverlayParams
  themeMode?: ProductImageThemeMode
}

interface ImageOverlayParams {
  width: number
  xCenter: number
  yCenter: number
}

const Gallery: React.FC<GalleryProps> = ({
  overlayThemeMap,
  galleryImages,
  colorOptions,
}) => {
  // URLs of images to display in the gallery. This is updated
  // when the select listener is triggered.
  const [currentColor, setCurrentColor] = useState<string>('')
  const [currentTheme, setCurrentTheme] = useState<string>('')
  const [imageUrls, setImageUrls] = useState<string[]>([])
  const [imageConfigs, setImageConfigs] = useState<ProductImageConfig[]>([])
  const [loadingImages, setLoadingImages] = useState<boolean>(true)

  useEffect(() => {
    const imageConfigsPromise = buildImageConfigs()
    const domContentLoadedPromise = createDomContentLoadedPromise()
    console.time('buildImageConfigs')

    // Wait for both the image configs and the DOM content to be ready before we attach the select listener.
    Promise.all([imageConfigsPromise, domContentLoadedPromise])
      .then(([imageConfigs]) => {
        //setImageConfigs(imageConfigs)
        initChangeHandlers()
        setLoadingImages(false)
        console.timeEnd('buildImageConfigs')
      })
      .catch((error) => {
        console.error('An error occurred:', error)
        Sentry.captureException(error)
      })
  }, [])

  // useEffect hook that runs whenever currentColor or currentTheme changes
  useEffect(() => {
    const filtered = filterImageConfigs(
      imageConfigs,
      currentColor,
      currentTheme
    )
    if (filtered.length > 0) {
      setImageUrls(
        filtered.map((config) => {
          return config.url
        })
      )
    } else {
      setImageUrls(
        imageConfigs.map((config) => {
          return config.url
        })
      )
    }
  }, [currentColor, currentTheme, imageConfigs])

  const filterImageConfigs = (
    imageConfigs: ProductImageConfig[],
    color: string,
    theme: string
  ): ProductImageConfig[] => {
    return imageConfigs.filter((config) => {
      // if (config.variationColor === color && config.variationTheme === theme) {
      if (config.variationColor === color) {
        return true
      }
      return false
    })
  }

  const initChangeHandlers = () => {
    const colorSelectChangeHandler = selectChangeHandler(setCurrentColor)
    const themeSelectChangeHandler = selectChangeHandler(setCurrentTheme)
    initSelectHandler('color', setCurrentColor, colorSelectChangeHandler)
    initSelectHandler(
      'pa_bracket-theme',
      setCurrentTheme,
      themeSelectChangeHandler
    )
  }

  const initSelectHandler = (
    selector: string,
    setFunction: (value: string) => void,
    handler: (event: Event) => void
  ) => {
    const selectElement = document.querySelector(
      `select#${selector}`
    ) as HTMLSelectElement | null

    if (!selectElement) {
      return
    }

    const value = selectElement.value
    if (value) {
      setFunction(value)
    }
    selectElement.addEventListener('change', handler)
  }

  const selectChangeHandler = (setFunction: (value: string) => void) => {
    return (event: Event) => {
      const target = event.target as HTMLSelectElement
      if (target?.value) {
        let value = target.value
        setFunction(value)
      } else {
        setFunction('')
      }
    }
  }

  const buildImageConfigs = async (): Promise<ProductImageConfig[]> => {
    const promises = galleryImages.map((image) => {
      return buildProductImageConfig(image, overlayThemeMap)
    })

    const configs = await Promise.allSettled(promises)
      .then((res) => {
        // get data urls where status is fulfilled
        const fulfilledConfigs = res
          .filter((promise) => {
            return promise.status === 'fulfilled'
          })
          .map((promise) => {
            return (promise as PromiseFulfilledResult<ProductImageConfig>).value
          })
        // get promisses that failed
        const rejectedPromises = res.filter((promise) => {
          return promise.status === 'rejected'
        })
        // log rejected promises
        rejectedPromises.forEach((promise) => {
          console.error(promise)
        })
        return fulfilledConfigs
      })
      .catch((error) => {
        console.error(error)
      })
    return configs ? configs : []
  }

  const buildProductImageConfig = async (
    image: GalleryImage,
    overlayMap: OverlayUrlThemeMap
  ): Promise<ProductImageConfig> => {
    const { src: backgroundImageUrl, title: backgroundImageTitle } = image

    const { variationColor, orientation, overlayParams, themeMode } =
      parseImageParams(backgroundImageTitle, colorOptions)

    const overlayUrl = getOverlayUrl(overlayMap, themeMode)

    const url =
      orientation === ProductImageOrientation.BACK &&
      overlayParams &&
      overlayUrl
        ? await addOverlay(backgroundImageUrl, overlayUrl, overlayParams)
        : backgroundImageUrl

    const config: ProductImageConfig = {
      url,
      variationColor,
    }

    setImageConfigs((prev) => [...prev, config])
    //if (loadingImages) {
    //  setLoadingImages(false)
    //}
    return config
  }

  const images = imageUrls.map((image) => {
    return {
      original: image,
      thumbnail: image,
    }
  })

  return (
    //@ts-ignore
    <>
      {loadingImages ? (
        <div className="tw-flex tw-h-[400px] tw-items-center tw-justify-center">
          <Spinner />
        </div>
      ) : (
        <div className="wpbb-gallery-container">
          <ImageGallery items={images} showPlayButton={false} />
        </div>
      )}
    </>
  )
}

const getOverlayUrl = (
  overlayMap: OverlayUrlThemeMap,
  theme?: string
): string => {
  if (theme === ProductImageThemeMode.DARK) {
    return overlayMap.dark
  } else if (theme === ProductImageThemeMode.LIGHT) {
    return overlayMap.light
  }
  return ''
}

const normalizeString = (str: string): string => {
  // Normalize a string by removing whitespace, underscores, and hyphens, and converting to lowercase.
  return str
    .trim()
    .toLowerCase()
    .replace(/[-_\s]/g, '')
}

const parseImageParams = (
  imageTitle: string,
  colorOptions: string[]
): ProductImageParams => {
  // extract a matching value in the colorOptions array from the image title
  const normalizedTitle = normalizeString(imageTitle)
  const normalizedColors = colorOptions.map((color) => {
    return normalizeString(color)
  })

  // get the colorOption where the corresponding normalizedColor is included in the normalizedTitle
  const variationColor = colorOptions.find((colorOption, index) => {
    return normalizedTitle.includes(normalizedColors[index])
  })

  let orientation: ProductImageOrientation | undefined
  if (normalizedTitle.includes('back')) {
    orientation = ProductImageOrientation.BACK
  } else if (normalizedTitle.includes('front')) {
    orientation = ProductImageOrientation.FRONT
  }

  let themeMode: ProductImageThemeMode | undefined
  if (normalizedTitle.includes('darktheme')) {
    themeMode = ProductImageThemeMode.DARK
  } else if (normalizedTitle.includes('lighttheme')) {
    themeMode = ProductImageThemeMode.LIGHT
  }

  const widthRegex = /w\d+/
  const widthMatch = normalizedTitle.match(widthRegex)
  const width = widthMatch ? widthMatch[0] : null

  const xCenterRegex = /x\d+/
  const xCenterMatch = normalizedTitle.match(xCenterRegex)
  const xCenter = xCenterMatch ? xCenterMatch[0] : null

  const yCenterRegex = /y\d+/
  const yCenterMatch = normalizedTitle.match(yCenterRegex)
  const yCenter = yCenterMatch ? yCenterMatch[0] : null

  const imageParams: ProductImageParams = {
    variationColor,
    orientation,
    themeMode,
  }
  if (width && xCenter && yCenter) {
    imageParams.overlayParams = {
      width: parseInt(width.substring(1), 10),
      xCenter: parseInt(xCenter.substring(1), 10),
      yCenter: parseInt(yCenter.substring(1), 10),
    }
  }
  return imageParams
}

// This is the big function that overlays the bracket on the image.
async function addOverlay(
  backgroundUrl: string,
  overlayUrl: string,
  overlayParams: ImageOverlayParams
) {
  const { width, xCenter, yCenter } = overlayParams

  const bracketWidth = width
  const bracketCenter = [xCenter, yCenter]

  // Create a new image element for the background image
  // The background image comes from the product so no worries
  // about cross-origin properties.
  const backgroundImage = new Image()
  //backgroundImage.crossOrigin = "anonymous";

  // Set the source URL for the background image
  backgroundImage.src = backgroundUrl

  // Create a new image element for the bracket image
  // Because the bracket image is hosted on a different domain (while I'm developing),
  // must set the crossOrigin attribute to anonymous. This will likely be unecessary in
  // production.
  const bracketImage = new Image()
  bracketImage.crossOrigin = 'anonymous'

  // Set the source URL for the logo image
  bracketImage.src = overlayUrl
  // await loadImage(bracketImage).catch((error) => {
  //   console.error(error);
  // });
  console.time('loadImage ' + bracketImage.src)
  await Promise.all([
    loadImage(backgroundImage),
    loadImage(bracketImage),
  ]).catch((error) => {
    console.error(error)
  })
  console.timeEnd('loadImage ' + bracketImage.src)

  // Scale the bracket image to the correct size
  const aspectRatio = bracketImage.width / bracketImage.height
  const bracketHeight = bracketWidth / aspectRatio

  // Create a canvas element
  const canvas = document.createElement('canvas')
  const context = canvas.getContext('2d')

  // Scale the canvas
  // context.scale(RESFACTOR, RESFACTOR)

  // Set canvas dimensions to match the background image
  canvas.width = backgroundImage.width * RESFACTOR
  canvas.height = backgroundImage.height * RESFACTOR

  // Draw the background image on the canvas
  context?.drawImage(
    backgroundImage,
    0,
    0,
    backgroundImage.width * RESFACTOR,
    backgroundImage.height * RESFACTOR
  )

  // context.scale(1/RESFACTOR, 1/RESFACTOR)

  // Calculate the position to place the logo on the canvas
  //const [x, y] = logoPosition;
  var [x, y] = bracketCenter

  x -= bracketWidth / 2
  y -= bracketHeight / 2

  x = x * RESFACTOR
  y = y * RESFACTOR

  // Draw the logo image on the canvas at the specified position and size
  context?.drawImage(
    bracketImage,
    x,
    y,
    bracketWidth * RESFACTOR,
    bracketHeight * RESFACTOR
  )

  // Convert the canvas image to a data URL
  const outputImageUrl = canvas.toDataURL()
  return outputImageUrl
}

function loadImage(imageElement: HTMLImageElement): Promise<void> {
  return new Promise<void>((resolve, reject) => {
    imageElement.addEventListener('load', () => resolve(), false)
    imageElement.addEventListener('error', (error) => reject(error), false)
  })
}

function createDomContentLoadedPromise(): Promise<void> {
  return new Promise<void>((resolve) => {
    const handleDOMContentLoaded = () => {
      resolve()
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }

    if (
      document.readyState === 'complete' ||
      document.readyState === 'interactive'
    ) {
      resolve()
    } else {
      document.addEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }
  })
}

export default Gallery

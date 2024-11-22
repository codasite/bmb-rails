// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState, useEffect } from 'react'

interface SizeDimensions {
  width: number
  height: number
}

function getWindowDimensions(): SizeDimensions {
  // outerwidth is the width of the browser window.
  // Using the value prevents a resize event from firing when the user pinches to zoom on a mobile device.
  const { outerWidth: width, innerHeight: height } = window
  return {
    width,
    height,
  }
}

export function useWindowDimensions(): SizeDimensions {
  const [windowDimensions, setWindowDimensions] = useState<SizeDimensions>(
    getWindowDimensions()
  )

  useEffect(() => {
    function handleResize(): void {
      setWindowDimensions(getWindowDimensions())
    }

    window.addEventListener('resize', handleResize)
    return (): void => window.removeEventListener('resize', handleResize)
  }, [])

  return windowDimensions
}

export function useResizeObserver(
  ref: React.RefObject<Element>,
  callback: (dimensions: SizeDimensions) => void
): void {
  useEffect(() => {
    const element = ref.current
    if (!element) return

    const resizeObserver = new ResizeObserver((entries) =>
      // wrap in setTimeout to avoid `ResizeObserver loop completed with undelivered notifications` error
      // https://github.com/juggle/resize-observer/issues/103
      setTimeout(() => {
        for (let entry of entries) {
          const { inlineSize: width, blockSize: height } =
            entry.borderBoxSize[0]
          callback({ width, height })
        }
      }, 0)
    )
    resizeObserver.observe(element, { box: 'border-box' })

    return () => {
      resizeObserver.disconnect()
    }
  }, [ref, callback])
}

export function useDomContentLoaded() {
  const [domContentLoaded, setDomContentLoaded] = useState(
    document.readyState === 'complete' || document.readyState === 'interactive'
  )

  useEffect(() => {
    if (domContentLoaded) return

    const handleDOMContentLoaded = () => {
      setDomContentLoaded(true)
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }

    document.addEventListener('DOMContentLoaded', handleDOMContentLoaded)

    // Cleanup function
    return () => {
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }
  }, [domContentLoaded]) // Depend on domContentLoaded state

  return domContentLoaded
}

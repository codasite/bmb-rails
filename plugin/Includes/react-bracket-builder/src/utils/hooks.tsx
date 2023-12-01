import React, { useState, useEffect } from 'react'

interface SizeDimensions {
  width: number
  height: number
}

function getWindowDimensions(): SizeDimensions {
  const { innerWidth: width, innerHeight: height } = window
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

    // const resizeObserver = new ResizeObserver((entries) => {
    //   if (!Array.isArray(entries) || !entries.length) return

    //   const entry = entries[0]
    //   const { width, height } = entry.contentRect
    //   callback({ width, height })
    // })
    // resizeObserver.observe(element)
    const resizeObserver = new ResizeObserver((entries) => {
      for (let entry of entries) {
        const { inlineSize: width, blockSize: height } = entry.borderBoxSize[0]
        callback({ width, height })
      }
    })
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

import React, { useCallback, useRef, useState, useEffect } from 'react'
import { useResizeObserver } from '../../../../utils/hooks'

export interface ScaledSpanProps {
  targetWidth: number
  hideToResize?: boolean
}

export const ScaledSpan = (props: any) => {
  const { targetWidth, style, children, ...rest } = props
  const textRef = useRef(null)
  const [textScale, setTextScale] = useState(0.5)
  const [showText, setShowText] = useState(true)
  useEffect(() => {
    const element = textRef.current
    if (element) {
      console.log('Element offsetWidth: ', element.offsetWidth) // Should be > 0 if in DOM and visible
      // Rest of your code...
    }
  }, [])

  const resizeCallback = useCallback(
    ({ width: currentWidth }) => {
      console.log('currentWidth', currentWidth)
      console.log('targetWidth', targetWidth)
      console.log('targetWidth', targetWidth)
      let scaleFactor = 0.5
      if (currentWidth > targetWidth) {
        scaleFactor = targetWidth / currentWidth
      }
      setTextScale(scaleFactor)
      setShowText(true)
    },
    [children]
  )

  useResizeObserver(textRef, resizeCallback)
  const newStyle = {
    ...style,
    transform: `scale(${textScale})`,
    visibility: showText ? 'visible' : 'hidden',
  }

  return (
    <span ref={textRef} style={newStyle} {...rest}>
      {children}
    </span>
  )
}

import React, { useCallback, useRef, useState, useEffect } from 'react'
import { useResizeObserver } from '../../../../utils/hooks'

export interface ScaledSpanProps {
  targetWidth: number
  hideToResize?: boolean
}

export const ScaledSpan = (props: any) => {
  const { targetWidth, style, children, ...rest } = props
  const textRef = useRef(null)
  const [textScale, setTextScale] = useState(1)
  const [showText, setShowText] = useState(false)

  const resizeCallback = useCallback(
    ({ width: currentWidth }) => {
      let scaleFactor = 1
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

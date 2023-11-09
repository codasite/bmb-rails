import { useCallback, useRef, useState } from 'react'
import { useResizeObserver } from '../../../../utils/hooks'

export interface ScaledSpanProps {
  targetWidth: number
}

export const ScaledSpan = (props: any) => {
  const { targetWidth, style, children, ...rest } = props
  const textRef = useRef(null)
  const [textScale, setTextScale] = useState(1)

  const resizeCallback = useCallback(
    ({ width: currentWidth }) => {
      if (currentWidth <= targetWidth) {
        setTextScale(1)
      }
      const scaleFactor = targetWidth / currentWidth
      setTextScale(scaleFactor)
    },
    [children]
  )

  useResizeObserver(textRef, resizeCallback)
  const newStyle = {
    ...style,
    transform: `scale(${textScale})`,
  }

  return (
    <span ref={textRef} style={newStyle} {...rest}>
      {children}
    </span>
  )
}

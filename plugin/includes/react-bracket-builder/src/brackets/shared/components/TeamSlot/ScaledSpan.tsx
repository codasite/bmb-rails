import { useCallback, useRef, useState } from 'react'
import { useResizeObserver } from '../../../../utils/hooks'

export interface ScaledSpanProps {
  targetWidth: number
}

export const ScaledSpan = (props: any) => {
  const { targetWidth, style, ...rest } = props
  const textRef = useRef(null)
  const [textScale, setTextScale] = useState(1)

  const resizeCallback = useCallback(({ width: currentWidth }) => {
    if (currentWidth <= targetWidth) return
    const scaleFactor = targetWidth / currentWidth
    setTextScale(scaleFactor)
  }, [])

  useResizeObserver(textRef, resizeCallback)
  const newStyle = {
    ...style,
    transform: `scale(${textScale})`,
  }

  return <span ref={textRef} style={newStyle} {...rest} />
}

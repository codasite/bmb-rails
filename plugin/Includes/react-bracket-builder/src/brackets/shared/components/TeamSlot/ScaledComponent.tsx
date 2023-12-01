import { useCallback, useRef, useState, useEffect, createElement } from 'react'
import { useResizeObserver } from '../../../../utils/hooks'

export interface ScaledComponentProps {
  targetWidth: number
  hideToResize?: boolean
  children: React.ReactNode
  ComponentToRender?: React.ComponentType<any>
  [key: string]: any
}

export const ScaledComponent = (props: ScaledComponentProps) => {
  const {
    targetWidth,
    style,
    children,
    ComponentToRender = 'div',
    ...rest
  } = props
  const componentRef = useRef(null)
  const [textScale, setTextScale] = useState(1)
  const [isVisible, setIsVisible] = useState(false)

  const resizeCallback = useCallback(
    ({ width: currentWidth }) => {
      let scaleFactor = 1
      if (currentWidth > targetWidth) {
        scaleFactor = targetWidth / currentWidth
      }
      setTextScale(scaleFactor)
      setIsVisible(true)
    },
    [children]
  )

  useResizeObserver(componentRef, resizeCallback)
  const newStyle = {
    ...style,
    transform: `scale(${textScale})`,
    visibility: isVisible ? 'visible' : 'hidden',
  }

  return createElement(
    ComponentToRender,
    {
      ref: componentRef,
      style: newStyle,
      ...rest,
    },
    children
  )
}

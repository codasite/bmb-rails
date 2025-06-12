import { useEffect, useRef } from 'react'
import { BracketClickEventHandlerProps } from './types'

export const BracketClickEventDelegator = (
  props: BracketClickEventHandlerProps
) => {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const container = containerRef.current
    if (!container) return

    const handleClick = async (event: MouseEvent) => {
      const target = event.target as HTMLElement
      const button = target.closest('button')
      console.log('button', button)
      console.log('target', target)
      if (!button) return

      // Find the first matching handler for this button's classes
      const matchingClass = Object.keys(props.handlers).find((className) =>
        button.classList.contains(className)
      )
      if (!matchingClass) return

      const handler = props.handlers[matchingClass]
      try {
        await handler(button)
      } catch (error) {
        console.error('Error handling click:', error)
      }
    }

    container.addEventListener('click', handleClick)
    return () => container.removeEventListener('click', handleClick)
  }, [props.handlers])

  return (
    <div ref={containerRef} className={props.className}>
      {props.children}
    </div>
  )
}

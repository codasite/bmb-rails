import { useEffect } from 'react'

export default function addClickHandlers(props: {
  buttonClassName: string
  onButtonClick: (button: HTMLButtonElement) => void
}) {
  const handleButtonClick = (e: any) => {
    props.onButtonClick(e.currentTarget)
  }
  useEffect(() => {
    const buttons = document.getElementsByClassName(props.buttonClassName)
    for (const button of buttons) {
      button.addEventListener('click', handleButtonClick)
    }
    return () => {
      for (const button of buttons) {
        button.removeEventListener('click', handleButtonClick)
      }
    }
  })
}

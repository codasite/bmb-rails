import { useContext } from 'react'
import { EllipseIcon } from '../shared'
import { DarkModeContext } from '../shared/context/context'

export const BracketHeaderTag = (props: {
  text: string
  color: 'green' | 'yellow'
}) => {
  const { darkMode } = useContext(DarkModeContext)
  let colorClass = ''

  switch (props.color) {
    case 'yellow':
      colorClass = darkMode
        ? 'tw-text-yellow tw-border-yellow tw-bg-yellow/20'
        : 'tw-text-dd-blue tw-border-yellow tw-bg-yellow'
      break
    case 'green':
    default:
      colorClass = darkMode
        ? 'tw-text-green tw-border-green tw-bg-green/20'
        : 'tw-text-dd-blue tw-border-green tw-bg-green'
    // You can add more colors here if needed
  }
  return (
    <div
      className={`tw-flex tw-items-center tw-gap-4 tw-px-8 tw-py-4 tw-border tw-border-solid tw-rounded-8 ${colorClass}`}
    >
      <EllipseIcon />
      <span className="tw-text-12 tw-font-500">{props.text}</span>
    </div>
  )
}

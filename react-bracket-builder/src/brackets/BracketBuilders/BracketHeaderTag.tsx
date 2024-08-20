import { EllipseIcon } from '../shared'

export const BracketHeaderTag = (props: {
  text: string
  color: 'green' | 'yellow'
}) => {
  const colorClass =
    props.color === 'yellow'
      ? 'tw-text-yellow tw-border-yellow tw-bg-yellow/20'
      : 'tw-text-green tw-border-green tw-bg-green/20'
  return (
    <div
      className={`tw-flex tw-items-center tw-gap-4 tw-px-8 tw-py-4 tw-border tw-border-solid tw-rounded-8 ${colorClass}`}
    >
      <EllipseIcon />
      <span className="tw-text-12 tw-font-500">{props.text}</span>
    </div>
  )
}

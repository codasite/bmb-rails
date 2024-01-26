import { EllipseIcon } from '../shared'

export const GreenTag = (props: { text: string }) => {
  return (
    <div className="tw-flex tw-items-center tw-gap-4 tw-px-8 tw-py-4 tw-text-green tw-border tw-border-solid tw-border-green tw-bg-green/20 tw-rounded-8">
      <EllipseIcon />
      <span className="tw-text-12 tw-font-500">{props.text}</span>
    </div>
  )
}

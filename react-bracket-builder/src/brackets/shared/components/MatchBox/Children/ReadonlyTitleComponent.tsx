import { defaultBracketConstants } from '../../../constants'
import { TitleComponentProps } from '../../types'

export const ReadonlyTitleComponent = (props: TitleComponentProps) => {
  return (
    <span
      className={`tw-text-48 sm:tw-text-${props.fontSize} tw-text-${props.color} dark:tw-text-${props.colorDark} tw-font-700 tw-text-center tw-leading-none`}
      style={{
        width: props.width,
      }}
    >
      {props.title}
    </span>
  )
}

import { defaultBracketConstants } from '../../../constants'
import { TitleComponentProps } from '../../types'

export const ReadonlyTitleComponent = (props: TitleComponentProps) => {
  const {
    title,
    fontSize = 48,
    color = 'white',
    colorDark = 'white',
    width,
  } = props
  return (
    <span
      className={`tw-text-48 sm:tw-text-${fontSize} tw-text-${color} dark:tw-text-${colorDark} tw-font-700 tw-text-center tw-leading-none`}
      style={{
        width,
      }}
    >
      {title}
    </span>
  )
}

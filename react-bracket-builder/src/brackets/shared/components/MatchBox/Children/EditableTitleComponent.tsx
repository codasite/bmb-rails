import { BufferedTextInput } from '../../BufferedTextInput'
import { TitleComponentProps } from '../../types'

export const EditableTitleComponent = (props: TitleComponentProps) => {
  console.log('editable title component', props)
  return (
    <BufferedTextInput
      value={props.title}
      onChange={(e) => props.setTitle(e.target.value)}
      initialValue={props.title}
      className="tw-text-48 sm:tw-text-64 tw-border tw-border-white tw-border-solid tw-rounded-8 tw-outline-none tw-text-white tw-bg-transparent tw-font-sans tw-uppercase tw-font-700 tw-text-center tw-p-0"
      style={{
        width: props.width,
      }}
    />
  )
}

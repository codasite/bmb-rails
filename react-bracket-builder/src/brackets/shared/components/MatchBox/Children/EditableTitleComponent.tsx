import { BufferedTextArea } from '../../BufferedTextArea'
import { TitleComponentProps } from '../../types'
import { useContext, useState } from 'react'
import { BracketMetaContext } from '../../../context/context'
export const EditableTitleComponent = (props: TitleComponentProps) => {
  const { fontSize = 48, width, style } = props
  const [editing, setEditing] = useState(false)
  return (
    <BufferedTextArea
      onChange={(e) => props.setTitle(e.target.value)}
      onStartEditing={() => setEditing(true)}
      onDoneEditing={() => setEditing(false)}
      initialValue={props.title}
      className={`${
        editing
          ? 'tw-border-white tw-text-white'
          : 'tw-border-white/50 tw-text-white/50'
      } tw-border-solid tw-rounded-8 tw-outline-none tw-bg-transparent tw-font-sans tw-uppercase tw-font-700 tw-text-center tw-p-0`}
      style={{
        fontSize,
        width,
        ...style,
      }}
    />
  )
}

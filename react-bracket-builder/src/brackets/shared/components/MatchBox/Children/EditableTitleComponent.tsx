import { BufferedTextArea } from '../../BufferedTextArea'
import { TitleComponentProps } from '../../types'
import { useContext, useState } from 'react'
import { BracketMetaContext } from '../../../context/context'
export const EditableTitleComponent = (props: TitleComponentProps) => {
  const [editing, setEditing] = useState(false)
  return (
    <BufferedTextArea
      onChange={(e) => props.setTitle(e.target.value)}
      onStartEditing={() => setEditing(true)}
      onDoneEditing={() => setEditing(false)}
      initialValue={props.title}
      className={`tw-text-48 ${
        editing ? 'tw-border' : 'tw-border-none'
      } tw-border-white tw-border-solid tw-rounded-8 tw-outline-none tw-text-white tw-bg-transparent tw-font-sans tw-uppercase tw-font-700 tw-text-center tw-p-0`}
      style={{
        width: props.width,
      }}
    />
  )
}

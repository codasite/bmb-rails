import { BracketRes } from '../shared/api/types/bracket'
import { GreenTag } from './GreenTag'

export const BracketStatusTag = (props: { bracket?: BracketRes }) => {
  return (
    <GreenTag
      text={
        props.bracket?.status === 'complete'
          ? 'Final Results'
          : 'Partial Results'
      }
    />
  )
}

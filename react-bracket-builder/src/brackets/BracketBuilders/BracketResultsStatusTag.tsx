import { BracketRes } from '../shared/api/types/bracket'
import { BracketHeaderTag } from './BracketHeaderTag'

export const BracketResultsStatusTag = (props: { bracket?: BracketRes }) => {
  return (
    <BracketHeaderTag
      text={
        props.bracket?.status === 'complete'
          ? 'Final Results'
          : 'Partial Results'
      }
      color="green"
    />
  )
}

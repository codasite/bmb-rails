import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { ScoredPlayTeamSlot } from '../TeamSlot/ScoredPlayTeamSlot'

export const ScoredPlayBracket = (props: BracketProps) => {
  const {
    BracketComponent = DefaultBracket,
    TeamSlotComponent = ScoredPlayTeamSlot,
  } = props

  return <BracketComponent TeamSlotComponent={TeamSlotComponent} {...props} />
}

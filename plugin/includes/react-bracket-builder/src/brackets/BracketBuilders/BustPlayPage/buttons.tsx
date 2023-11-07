import {
  ActionButton,
  ActionButtonProps,
} from '../../shared/components/ActionButtons'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play.svg'

export const JoinTournamentButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="big-green" {...props}>
      <PlayIcon />
      Join Tournament
    </ActionButton>
  )
}

export const BustBracketButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="red" size="big" {...props}>
      <LightningIcon />
      Bust Bracket
    </ActionButton>
  )
}

export const AddApparelButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="green" size="big" {...props}>
      Add to Apparel
    </ActionButton>
  )
}

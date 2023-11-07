import {
  ActionButton,
  ActionButtonProps,
} from '../../shared/components/ActionButtons'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play.svg'

export const JoinTournamentButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="green" {...props}>
      <PlayIcon />
      Join Tournament
    </ActionButton>
  )
}

export const BustBracketButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="red" {...props}>
      <LightningIcon />
      Bust Bracket
    </ActionButton>
  )
}

export const AddApparelButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="green" {...props}>
      Add to Apparel
    </ActionButton>
  )
}

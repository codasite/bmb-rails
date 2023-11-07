import {
  ActionButton,
  ActionButtonProps,
} from '../../shared/components/ActionButtons'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play_32.svg'

export const JoinTournamentButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="blue" borderWidth={4} fontWeight={700} {...props}>
      <PlayIcon />
      Play
    </ActionButton>
  )
}

export const BustBracketButton = (props: ActionButtonProps) => {
  return (
    <ActionButton variant="red" {...props}>
      <LightningIcon />
      Bust
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

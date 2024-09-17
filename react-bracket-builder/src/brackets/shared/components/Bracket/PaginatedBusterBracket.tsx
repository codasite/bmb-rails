import { PaginatedBracketProps, PaginatedNavButtonsProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { BusterBracket } from './BusterBracket'
import { DefaultFullBracketButton } from './BracketActionButtons'
import { DefaultFinalButton } from './BracketActionButtons'
import { DefaultNavButtons } from './BracketActionButtons'
import { ActionButtonProps } from '../ActionButtons'

export const BusterNavButtons = (props: PaginatedNavButtonsProps) => {
  const {
    FullBracketBtnComponent = BusterFullBracketButton,
    FinalButtonComponent = BusterFinalButton,
  } = props

  return (
    <DefaultNavButtons
      FullBracketBtnComponent={FullBracketBtnComponent}
      FinalButtonComponent={FinalButtonComponent}
      {...props}
    />
  )
}

export const BusterFullBracketButton = (props: ActionButtonProps) => {
  return <DefaultFullBracketButton variant="red" {...props} />
}

export const BusterFinalButton = (props: ActionButtonProps) => {
  return <DefaultFinalButton variant="red" {...props} />
}

export const PaginatedBusterBracket = (props: PaginatedBracketProps) => {
  return (
    <BusterBracket
      BracketComponent={PaginatedDefaultBracket}
      NavButtonsComponent={BusterNavButtons}
      {...props}
    />
  )
}

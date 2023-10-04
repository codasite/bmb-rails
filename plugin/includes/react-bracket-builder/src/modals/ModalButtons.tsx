import { ActionButton } from '../brackets/shared/components/ActionButtons'
import * as React from 'react'

export const ConfirmButton = (props: {
  disabled: boolean
  onClick: () => void
  children?: React.ReactNode
}) => {
  return (
    <ActionButton
      variant="green"
      paddingY={12}
      paddingX={16}
      fontSize={16}
      fontWeight={700}
      disabled={props.disabled}
      onClick={props.onClick}
      className={
        (props.disabled ? '' : 'hover:tw-text-white/75') + ' tw-w-full'
      }
    >
      {props.children}
    </ActionButton>
  )
}
export const CancelButton = (props: { onClick: () => void }) => {
  return (
    <button
      onClick={props.onClick}
      className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer"
    >
      Cancel
    </button>
  )
}

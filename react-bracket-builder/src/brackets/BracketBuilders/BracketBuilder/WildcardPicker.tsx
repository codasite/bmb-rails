import React from 'react'
import { WildcardPlacement } from '../../shared/models/WildcardPlacement'

interface WildcardPickerButtonProps {
  active: boolean
  onPressed: () => void
  label: string
}

const WildcardPickerButton = (props: WildcardPickerButtonProps) => {
  const { onPressed, label, active } = props

  const baseStyles = [
    'tw-flex-grow',
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-flex-grow',
    'tw-border-solid',
    'tw-border',
    'tw-rounded-8',
    'tw-text-20',
    'tw-font-500',
    'tw-font-sans',
    'tw-uppercase',
    'tw-h-[56px]',
    'tw-bg-transparent',
    'tw-cursor-pointer',
  ]

  const activeStyles = ['tw-border-yellow/50', 'tw-text-yellow']

  const inactiveStyles = ['tw-border-white/50', 'tw-text-white']

  const styles = baseStyles
    .concat(active ? activeStyles : inactiveStyles)
    .join(' ')
  return (
    <button className={styles} onClick={onPressed}>
      {label}
    </button>
  )
}

const wildcardPlacementOptions: Record<string, WildcardPlacement> = {
  Top: WildcardPlacement.Top,
  Bottom: WildcardPlacement.Bottom,
  Center: WildcardPlacement.Center,
  Split: WildcardPlacement.Split,
}

interface WildcardPickerProps {
  wildcardPlacement: WildcardPlacement
  onWildcardPlacementChanged: (placement: WildcardPlacement) => void
}

export const WildcardPicker = (props: WildcardPickerProps) => {
  const { wildcardPlacement, onWildcardPlacementChanged } = props
  return (
    <div className="tw-flex tw-flex-col tw-gap-10">
      <span className="tw-text-16 sm:tw-text-24 tw-text-center tw-font-500 tw-text-white/50">
        Wildcard Display
      </span>
      <div className="tw-flex tw-flex-col sm:tw-flex-row tw-gap-16">
        {Object.entries(wildcardPlacementOptions).map(([key, value]) => {
          return (
            <WildcardPickerButton
              label={key}
              active={value === wildcardPlacement}
              onPressed={() => onWildcardPlacementChanged(value)}
            />
          )
        })}
      </div>
    </div>
  )
}

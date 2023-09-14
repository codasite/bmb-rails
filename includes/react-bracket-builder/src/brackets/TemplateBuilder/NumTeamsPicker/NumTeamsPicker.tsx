import React, { } from 'react'

interface NumTeamsIncrementButtonProps {
  active: boolean
  onPressed: () => void
  children: React.ReactNode
}

const NumTeamsIncrementButton = (props: NumTeamsIncrementButtonProps) => {
  const {
    onPressed,
    children,
    active,
  } = props
  const baseStyles = [
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-flex-grow',
    'tw-border-solid',
    'tw-border',
    'tw-rounded-4',
    'tw-text-36',
    'tw-font-500',
    'tw-font-sans',
    'tw-h-30',
    'tw-bg-transparent',
  ]
  const activeStyles = [
    'tw-border-white',
    'tw-text-white',
    'tw-cursor-pointer',
  ]
  const inactiveStyles = [
    'tw-border-white/50',
    'tw-text-white/50',
  ]

  const styles = baseStyles.concat(active ? activeStyles : inactiveStyles).join(' ')

  return (
    <button className={styles} onClick={onPressed} disabled={!active}>
      {children}
    </button>
  )
}

interface NumTeamsPickerProps {
  currentValue: number
  defaultValue: number
  min: number
  max: number
  selected: boolean
  setSelected: () => void
  increment: () => void
  decrement: () => void
  setCurrentValue: (value: number) => void
  selectNextPicker?: () => void
  selectPrevPicker?: () => void
}

/**
 * A component that allows the user to select the number of teams in a bracket
 * If the user increments or decrements the number of teams beyone the min-max range, 
 * the next or previous picker is selected (if it exists)
 * 
 * If the user clicks a box that is already selected, the value is reset to the default value
 */
export const NumTeamsPicker = (props: NumTeamsPickerProps) => {
  const {
    currentValue,
    defaultValue,
    min,
    max,
    selected,
    setSelected,
    increment,
    decrement,
    setCurrentValue,

    selectNextPicker,
    selectPrevPicker
  } = props

  const handleBoxClick = () => {
    if (selected) {
      // if the box is already selected, set the value to the default
      setCurrentValue(defaultValue)
    } else {
      setSelected()
    }
  }

  const handleIncrement = () => {
    if (currentValue < max) {
      increment()
    }
    else if (selectNextPicker) {
      selectNextPicker()
    }
  }

  const handleDecrement = () => {
    if (currentValue > min) {
      decrement()
    }
    else if (selectPrevPicker) {
      selectPrevPicker()
    }
  }

  const incrementDisabled = currentValue >= max && !selectNextPicker
  const decrementDisabled = currentValue <= min && !selectPrevPicker

  const baseStyles = [
    'tw-flex',
    'tw-justify-center',
    // 'tw-py-[38px]',
    'tw-h-[136px]',
    'tw-items-center',
    'tw-border-solid',
    'tw-rounded-8',
    'tw-relative',
    'tw-cursor-pointer',
  ]
  const inactiveStyles = [
    'tw-border',
    'tw-border-white/50',
  ]
  const activeStyles = [
    'tw-bg-green/15',
    'tw-border-4',
    'tw-border-green',
  ]

  const styles = baseStyles.concat(selected ? activeStyles : inactiveStyles).join(' ')

  return (
    <div className={'tw-flex tw-flex-col tw-gap-24 tw-grow'}>
      <div
        className={styles}
        onClick={handleBoxClick}
      >
        <span className='tw-font-500 tw-text-48 tw-text-white'>{currentValue}</span>
        {selected && currentValue === defaultValue && <span className='tw-absolute tw-bottom-8 tw-left-14 tw-text-green tw-font-500 tw-text-12'>Default</span>}
      </div>
      {
        selected &&
        <div className='tw-flex tw-justify-center tw-gap-12'>
          <NumTeamsIncrementButton active={!decrementDisabled} onPressed={handleDecrement}>-</NumTeamsIncrementButton>
          <NumTeamsIncrementButton active={!incrementDisabled} onPressed={handleIncrement}>+</NumTeamsIncrementButton>
        </div>

      }

      {/* <div style={{ display: selected ? 'block' : 'none' }}>
        <div aria-label="Basic example" className='button-container'>
          <button className='btn-secondary no-highlight-button step-down-button' disabled={decrementDisabled} onClick={handleDecrement}>-</button>
          <button className='btn-secondary no-highlight-button step-up-button' disabled={incrementDisabled} onClick={handleIncrement}>+</button>
        </div>
      </div> */}
    </div>
  )
}
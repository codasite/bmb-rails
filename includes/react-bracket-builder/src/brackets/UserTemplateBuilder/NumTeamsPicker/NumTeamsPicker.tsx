import React, { } from 'react'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
// import './user-template-builder.scss'

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

    return (
        <div>
            <div
                // key={num}
                className={`team-chooser-box ${selected ? 'highlight' : ''}`}
                onClick={handleBoxClick}
            >
                {currentValue}
                {selected && currentValue === defaultValue && <span className='corner-text'>Default</span>}

            </div>
                <div style={{ visibility: selected ? 'visible' : 'hidden' }}>
                    <div>
                        <ButtonGroup aria-label="Basic example" className='button-container'>
                            <Button className='btn-secondary no-highlight-button step-down-button' disabled={decrementDisabled} variant='secondary' onClick={handleDecrement}>-</Button>
                            <Button className='btn-secondary no-highlight-button step-up-button' disabled={incrementDisabled} variant='secondary' onClick={handleIncrement}>+</Button>
                        </ButtonGroup>
                    </div>
                </div>
        </div>
    )
}
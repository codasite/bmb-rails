import React, { useState, useEffect } from 'react'
import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import './user-template-builder.scss'
import { set } from 'immer/dist/internal'
import { NumTeamsPicker } from './NumTeamsPicker/NumTeamsPicker'
import { MatchTree } from '../shared/models/MatchTree'
import { Bracket } from '../shared/components/Bracket'
import { bracketApi } from '../shared/api/bracketApi';

const defaultBracketName = "MY BRACKET NAME"
const WildCardPlacements = ['TOP', 'BOTTOM', 'CENTER', 'SPLIT']

const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [2, 18, 34]
const teamPickerMax = [30, 62, 64]


interface NumTeamsPickerState {
    currentValue: number
    selected: boolean
}

const BracketTitle = (props) => {
    const {
        title,
        setTitle
    } = props

    const [editing, setEditing] = useState(false)
    const [textBuffer, setTextBuffer] = useState(title)

    const startEditing = () => {
        setEditing(true)
        setTextBuffer(title)
    }
    const doneEditing = (event: any) => {
        setTitle(textBuffer)
        setEditing(false)
    }

    return (
        <div className='wpbb-bracket-title' onClick={startEditing}>
            {editing ?
                <input
                    className='wpbb-bracket-title-input'
                    autoFocus
                    onFocus={(e) => e.target.select()}
                    type='text'
                    value={textBuffer}
                    onChange={(e) => setTextBuffer(e.target.value)}
                    onBlur={doneEditing}
                    onKeyUp={(e) => {
                        if (e.key === 'Enter') {
                            doneEditing(e)
                        }
                    }}
                />
                :
                <div className='wpbb-bracket-title-name'>{title}</div>
            }
        </div>
    )
}

const evaluateNumRoundAndWildCard = (numTeams: number) => {
    let numRounds = 1
    let wildCardGame = 0
    while (numTeams > Math.pow(2, numRounds)) {
        numRounds++;
    }

    wildCardGame = numTeams - Math.pow(2, numRounds - 1);
    if (wildCardGame === Math.pow(2, numRounds - 1)) {
        wildCardGame = 0
    }
    // console.log('Round for team ' + inputNumber + ' is ' + numRounds)
    // console.log('Wild Card for ' + inputNumber + ' is ' + wildCardGame)
    return [
        numRounds,
        wildCardGame
    ];
}

const UserTemplateBuilder = () => {

    const initialPickerIndex = 1
    const [numTeams, setNumTeams] = useState(teamPickerDefaults[initialPickerIndex])
    const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
        teamPickerDefaults.map((val, i) => ({
            currentValue: val,
            selected: i === initialPickerIndex
        }))
    )
    const [bracketTitle, setBracketTitle] = useState(defaultBracketName);
    const [wildCardPos, setWildCardPos] = useState(initialPickerIndex)
    const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(4, 0, initialPickerIndex));
    const [totalRounds,setTotalRounds] = useState(Number);
    const [totalWildCardGames,setTotalWildCardGames] = useState(Number);

    // Update the global `numTeams` variable whenever picker state changes
    useEffect(() => {
        const picker = teamPickerState.find(picker => picker.selected)
        if (picker) {
            setNumTeams(picker.currentValue)
            const [totalRounds, totalWildCardGames] = evaluateNumRoundAndWildCard(picker.currentValue)
            setTotalRounds(totalRounds);
            setTotalWildCardGames(totalWildCardGames);
            setMatchTree(MatchTree.fromOptions(totalRounds, totalWildCardGames, wildCardPos))
        }
    }, [teamPickerState, wildCardPos])

    const handleWildCardPlacement = (index) => {
        setWildCardPos(index)
    }

    const CreateWildCardPlacementButtons = (props) => {
        const selected = props.wildCard();
        return (
            <div>
                <ButtonGroup aria-label="Basic example">
                    <Button className={`btn-secondary no-highlight-button pos-btn ${selected ? 'selected-btn' : ''}`}  variant='secondary' onClick={() => handleWildCardPlacement(props.positionIndex)}>{props.position}</Button>
                </ButtonGroup>
            </div>
        )
    }

    const updateTeamPicker = (index: number, newPicker: NumTeamsPickerState) => {
        const newPickers = teamPickerState.map((picker, i) => {
            if (i === index) {
                return newPicker
            }
            return picker
        })
        setTeamPickerState(newPickers)
    }

    const updateTeamPickerValueBy = (index: number, value: number) => {
        const picker = teamPickerState[index]
        const newPicker = {
            ...picker,
            currentValue: picker.currentValue + value
        }
        updateTeamPicker(index, newPicker)
    }

    const incrementTeamPicker = (index: number) => {
        updateTeamPickerValueBy(index, 2)
    }

    const decrementTeamPicker = (index: number) => {
        updateTeamPickerValueBy(index, -2)
    }

    const setTeamPickerValue = (index: number, value: number) => {
        const picker = teamPickerState[index]
        const newPicker = {
            ...picker,
            currentValue: value
        }
        updateTeamPicker(index, newPicker)
    }

    const setTeamPickerSelected = (index: number, resetValue: boolean = false) => {
        const newPickers = teamPickerState.map((picker, i) => {
            if (i === index) {
                return {
                    ...picker,
                    currentValue: resetValue ? teamPickerDefaults[index] : picker.currentValue,
                    selected: true
                }
            }
            return {
                ...picker,
                selected: false
            }
        })
        setTeamPickerState(newPickers)
    }

    const setWildCardSelected = (index) => {
        if(wildCardPos === index){
            return true;
        }
    }

    /**
     * Get the function to select the next team picker
     * When the next team picker is selected, its value is reset to the default value
     */
    const getSelectNextTeamPicker = (index: number) => {
        if (index < teamPickerState.length - 1) {
            return () => setTeamPickerSelected(index + 1, true)
        }
        return undefined
    }

    /**
     * Get the function to select the previous team picker
     * When the previous team picker is selected, its value is reset to the default value
     */
    const getSelectPrevTeamPicker = (index: number) => {
        if (index > 0) {
            return () => setTeamPickerSelected(index - 1, true)
        }
        return undefined
    }

    /**
     * Bitwise operation to check if a number is a power of 2
     */
    const isPowerOfTwo = (num: number) => {
        return (num & (num - 1)) === 0
    }

    // Show wild card options if numTeams is a power of 2
    const showWildCardOptions = !isPowerOfTwo(numTeams)

    const handleShuffle = ()=>{
        const shuffleData = matchTree;
        const req = matchTree.toRequest(bracketTitle, true, totalRounds, totalWildCardGames, wildCardPos)
        bracketApi.shuffleBracket(req)
        .then((bracket) => {
            setMatchTree(MatchTree.fromRounds(bracket.rounds))          
        })
        
    }


    return (
        <div className='wpbb-template-builder-root'>
            <BracketTitle title={bracketTitle} setTitle={setBracketTitle} />
            <div className='wpbb-default'>
                <Bracket matchTree={matchTree} setMatchTree={setMatchTree} canEdit />
            </div>
            <div>
                <Button className='randomize-teams no-highlight-button' onClick={handleShuffle}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.2929 2.79289C17.6834 2.40237 18.3166 2.40237 18.7071 2.79289L21.7071 5.79289C22.0976 6.18342 22.0976 6.81658 21.7071 7.20711L18.7071 10.2071C18.3166 10.5976 17.6834 10.5976 17.2929 10.2071C16.9024 9.81658 16.9024 9.18342 17.2929 8.79289L18.5858 7.5H18.5689C17.5674 7.5 17.275 7.51019 17.0244 7.5863C16.7728 7.6627 16.5388 7.78796 16.3356 7.95491C16.1333 8.12123 15.9626 8.35886 15.4071 9.19214L10.257 16.9173C10.2323 16.9543 10.2079 16.9909 10.1839 17.027C9.73463 17.7018 9.39557 18.2111 8.93428 18.5902C8.52804 18.9241 8.05995 19.1746 7.55679 19.3274C6.98546 19.5009 6.37366 19.5005 5.56299 19.5001C5.51961 19.5 5.47566 19.5 5.43112 19.5H3C2.44772 19.5 2 19.0523 2 18.5C2 17.9477 2.44772 17.5 3 17.5H5.43112C6.4326 17.5 6.72499 17.4898 6.97562 17.4137C7.2272 17.3373 7.46125 17.212 7.66437 17.0451C7.86672 16.8788 8.03739 16.6411 8.59291 15.8079L13.743 8.08274C13.7677 8.04568 13.792 8.0091 13.8161 7.973C14.2654 7.29821 14.6044 6.78895 15.0657 6.40982C15.472 6.07592 15.9401 5.82541 16.4432 5.6726C17.0145 5.4991 17.6263 5.49946 18.437 5.49995C18.4804 5.49997 18.5243 5.5 18.5689 5.5H18.5858L17.2929 4.20711C16.9024 3.81658 16.9024 3.18342 17.2929 2.79289ZM6.97562 7.5863C6.72499 7.51019 6.4326 7.5 5.43112 7.5H3C2.44772 7.5 2 7.05228 2 6.5C2 5.94772 2.44772 5.5 3 5.5H5.43112C5.47566 5.5 5.51961 5.49997 5.56298 5.49995C6.37365 5.49946 6.98546 5.4991 7.55679 5.6726C8.05995 5.82541 8.52804 6.07592 8.93429 6.40982C9.39557 6.78894 9.73463 7.2982 10.1839 7.97299C10.2079 8.0091 10.2323 8.04568 10.257 8.08274L10.4987 8.4453C10.8051 8.90483 10.6809 9.5257 10.2214 9.83205C9.76184 10.1384 9.14097 10.0142 8.83462 9.5547L8.59291 9.19214C8.03739 8.35886 7.86672 8.12123 7.66437 7.95491C7.46125 7.78796 7.2272 7.6627 6.97562 7.5863ZM17.2929 14.7929C17.6834 14.4024 18.3166 14.4024 18.7071 14.7929L21.7071 17.7929C22.0976 18.1834 22.0976 18.8166 21.7071 19.2071L18.7071 22.2071C18.3166 22.5976 17.6834 22.5976 17.2929 22.2071C16.9024 21.8166 16.9024 21.1834 17.2929 20.7929L18.5858 19.5H18.5689C18.5243 19.5 18.4804 19.5 18.437 19.5001C17.6263 19.5005 17.0145 19.5009 16.4432 19.3274C15.9401 19.1746 15.472 18.9241 15.0657 18.5902C14.6044 18.2111 14.2654 17.7018 13.8161 17.027C13.7921 16.9909 13.7677 16.9543 13.743 16.9173L13.5013 16.5547C13.1949 16.0952 13.3191 15.4743 13.7786 15.1679C14.2382 14.8616 14.859 14.9858 15.1654 15.4453L15.4071 15.8079C15.9626 16.6411 16.1333 16.8788 16.3356 17.0451C16.5388 17.212 16.7728 17.3373 17.0244 17.4137C17.275 17.4898 17.5674 17.5 18.5689 17.5H18.5858L17.2929 16.2071C16.9024 15.8166 16.9024 15.1834 17.2929 14.7929Z" fill="white" />
                    </svg><span className={'randomize-teams-text'}>scramble team order</span>
                </Button>
            </div>
            <div>
                <div className='options-bracket-display-text'>
                    Bracket style
                </div>
                <div className='tree-group'>
                    <div className='tree-type'>
                        Dual Tree
                    </div>
                    <div className='tree-type'>
                        Single Tree
                    </div>
                </div>
            </div>
            <div>
                <div className='bracket-text-info'>
                    How Many total teams in Your Bracket
                </div>
                <div className='team-picker'>
                    {teamPickerState.map((pickerState, i) => {
                        return (
                            <NumTeamsPicker
                                currentValue={pickerState.currentValue}
                                defaultValue={teamPickerDefaults[i]}
                                min={teamPickerMin[i]}
                                max={teamPickerMax[i]}
                                selected={pickerState.selected}
                                setSelected={() => setTeamPickerSelected(i)}
                                increment={() => incrementTeamPicker(i)}
                                decrement={() => decrementTeamPicker(i)}
                                setCurrentValue={(value) => setTeamPickerValue(i, value)}
                                selectNextPicker={getSelectNextTeamPicker(i)}
                                selectPrevPicker={getSelectPrevTeamPicker(i)}
                            />
                        )
                    })}
                </div>
            </div>
            <div className='wild-btn' style={{ visibility: showWildCardOptions ? 'visible' : 'hidden' }}>
                <div className='bracket-text-info  wild-card-display-text'>
                    WILDCARD DISPLAY
                </div>
                <div className='wild-card-group'>
                    {WildCardPlacements.map((pos, index) => (
                        <div className='wild-card-btn' key={index}>
                            <CreateWildCardPlacementButtons position={pos} positionIndex={index} wildCard={() => setWildCardSelected(index)}/>
                        </div>
                    ))}
                </div>
            </div>
            <div className='bracket-button'>
                <ButtonGroup className='save-bracket'>
                    <Button className='btn-save-bracket' variant='secondary' >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <path d="M7 3.5V6.9C7 7.46005 7 7.74008 7.10899 7.95399C7.20487 8.14215 7.35785 8.29513 7.54601 8.39101C7.75992 8.5 8.03995 8.5 8.6 8.5H15.4C15.9601 8.5 16.2401 8.5 16.454 8.39101C16.6422 8.29513 16.7951 8.14215 16.891 7.95399C17 7.74008 17 7.46005 17 6.9V4.5M17 21.5V15.1C17 14.5399 17 14.2599 16.891 14.046C16.7951 13.8578 16.6422 13.7049 16.454 13.609C16.2401 13.5 15.9601 13.5 15.4 13.5H8.6C8.03995 13.5 7.75992 13.5 7.54601 13.609C7.35785 13.7049 7.20487 13.8578 7.10899 14.046C7 14.2599 7 14.5399 7 15.1V21.5M21 9.82548V16.7C21 18.3802 21 19.2202 20.673 19.862C20.3854 20.4265 19.9265 20.8854 19.362 21.173C18.7202 21.5 17.8802 21.5 16.2 21.5H7.8C6.11984 21.5 5.27976 21.5 4.63803 21.173C4.07354 20.8854 3.6146 20.4265 3.32698 19.862C3 19.2202 3 18.3802 3 16.7V8.3C3 6.61984 3 5.77976 3.32698 5.13803C3.6146 4.57354 4.07354 4.1146 4.63803 3.82698C5.27976 3.5 6.11984 3.5 7.8 3.5H14.6745C15.1637 3.5 15.4083 3.5 15.6385 3.55526C15.8425 3.60425 16.0376 3.68506 16.2166 3.79472C16.4184 3.9184 16.5914 4.09135 16.9373 4.43726L20.0627 7.56274C20.4086 7.90865 20.5816 8.0816 20.7053 8.28343C20.8149 8.46237 20.8957 8.65746 20.9447 8.86154C21 9.09171 21 9.3363 21 9.82548Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span className='save-bracket-text'>Save Bracket</span>
                    </Button>
                </ButtonGroup>
                <ButtonGroup className='play-bracket'>
                    <Button className='btn-play-bracket' variant='secondary' >
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                            <path d="M5.5 5.48951C5.5 4.51835 5.5 4.03277 5.70249 3.7651C5.87889 3.53191 6.14852 3.38761 6.4404 3.37018C6.77544 3.35017 7.17946 3.61953 7.98752 4.15823L18.5031 11.1686C19.1708 11.6137 19.5046 11.8363 19.6209 12.1168C19.7227 12.3621 19.7227 12.6377 19.6209 12.883C19.5046 13.1635 19.1708 13.386 18.5031 13.8312L7.98752 20.8415C7.17946 21.3802 6.77544 21.6496 6.4404 21.6296C6.14852 21.6122 5.87889 21.4679 5.70249 21.2347C5.5 20.967 5.5 20.4814 5.5 19.5103V5.48951Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span className='play-bracket-text'>Play Bracket</span>
                    </Button>
                </ButtonGroup>
            </div>
        </div>
    )
}

export default UserTemplateBuilder



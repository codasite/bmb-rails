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
import { ShuffleTeam } from './ShuffleTeam'
import { bracketConstants } from '../shared/constants'
import UserBracket from '../UserBracketBuilder/UserBracket/UserBracket'
import { BracketRes } from '../shared/api/types/bracket'

const defaultBracketName = "MY BRACKET NAME"
const WildCardPlacements = ['TOP', 'BOTTOM', 'CENTER', 'SPLIT']

const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [2, 18, 34]
const teamPickerMax = [16, 32, 64]


interface NumTeamsPickerState {
    currentValue: number
    selected: boolean
}

export interface BracketFields {
    bracketTitle: string,
    totalRounds: number,
    totalWildCardGames: number,
    wildCardPos: number
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
    
    return [
        numRounds,
        wildCardGame
    ];
}
interface UserBracketProps {
    bracketId?: number;
    apparelUrl: string;
    bracketStylesheetUrl: string;
}

const UserTemplateBuilder = (props: UserBracketProps) => {

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
    const [totalRounds, setTotalRounds] = useState(Number);
    const [totalWildCardGames, setTotalWildCardGames] = useState(Number);
    const [showComponent, setShowComponent] = useState(false);
    const [bracketFields, setBracketFields] = useState<BracketFields>({
        bracketTitle: bracketTitle,
        totalRounds: 4,
        totalWildCardGames: 0,
        wildCardPos: initialPickerIndex
    }
    )
    let bracketRes: any;
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
                    <Button className={`btn-secondary no-highlight-button pos-btn ${selected ? 'selected-btn' : ''}`} variant='secondary' onClick={() => handleWildCardPlacement(props.positionIndex)}>{props.position}</Button>
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
        if (wildCardPos === index) {
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

    const handleTeams = () => {
        setBracketFields({
            bracketTitle: bracketTitle,
            totalRounds: totalRounds,
            totalWildCardGames: totalWildCardGames,
            wildCardPos: wildCardPos
        }
        )
        bracketRes = matchTree;
        setShowComponent(true);
    }


    return (
        <div>
            {showComponent ? <UserBracket bracketStylesheetUrl={props.bracketStylesheetUrl} bracketRes={bracketRes} apparelUrl={props.apparelUrl} bracketFields={bracketFields} canEdit /> :
                <div className='wpbb-template-builder-root'>
                    <div className="bracket-container">
                        <BracketTitle title={bracketTitle} setTitle={setBracketTitle} />
                        <div className='pt-5 wpbb-default'>
                            <Bracket matchTree={matchTree} setMatchTree={setMatchTree} canEdit matchHeight={2 * bracketConstants.teamHeight} minWidth={bracketConstants.roundWidth} userBracketWindow />
                        </div>
                        <div className='team-picker-container'>
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
                        <div className='wild-btn' style={{ display: showWildCardOptions ? 'block' : 'none' }}>
                            <div className='bracket-text-info  wild-card-display-text'>
                                WILDCARD DISPLAY
                            </div>
                            <div className='wild-card-group'>
                                {WildCardPlacements.map((pos, index) => (
                                    <div className='wild-card-btn' key={index}>
                                        <CreateWildCardPlacementButtons position={pos} positionIndex={index} wildCard={() => setWildCardSelected(index)} />
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className='bracket-button'>
                            <ButtonGroup className='play-bracket'>
                                <Button className='btn-play-bracket' variant='secondary' onClick={handleTeams}>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                        <path d="M5.5 5.48951C5.5 4.51835 5.5 4.03277 5.70249 3.7651C5.87889 3.53191 6.14852 3.38761 6.4404 3.37018C6.77544 3.35017 7.17946 3.61953 7.98752 4.15823L18.5031 11.1686C19.1708 11.6137 19.5046 11.8363 19.6209 12.1168C19.7227 12.3621 19.7227 12.6377 19.6209 12.883C19.5046 13.1635 19.1708 13.386 18.5031 13.8312L7.98752 20.8415C7.17946 21.3802 6.77544 21.6496 6.4404 21.6296C6.14852 21.6122 5.87889 21.4679 5.70249 21.2347C5.5 20.967 5.5 20.4814 5.5 19.5103V5.48951Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span className='play-bracket-text'>Add Your Teams</span>
                                </Button>
                            </ButtonGroup>
                        </div>
                    </div>
                </div>
            }
        </div>
    )
}

export default UserTemplateBuilder


